<?php

namespace Workdo\FiscalBR\Library;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Workdo\FiscalBR\Entities\NFe;
use Workdo\FiscalBR\Entities\NFeEvent;
use Workdo\FiscalBR\Entities\SefazLog;

class NFeEventService
{
    protected $workspaceId;
    protected $tools;
    protected $config;

    public function __construct(int $workspaceId)
    {
        $this->workspaceId = $workspaceId;
        $this->config = SefazConfigService::getConfig($workspaceId);
        $this->initializeTools();
    }

    /**
     * Initialize NFePHP Tools
     */
    private function initializeTools()
    {
        try {
            $certificateData = SefazConfigService::getCertificate($this->workspaceId);
            $certificate = Certificate::readPfx($certificateData['content'], $certificateData['password']);
            
            $this->tools = new Tools(json_encode($this->config), $certificate);
            $this->tools->model('55');
        } catch (\Exception $e) {
            throw new \Exception('Erro ao inicializar ferramentas SEFAZ: ' . $e->getMessage());
        }
    }

    /**
     * Cancel NF-e
     *
     * @param NFe $nfe
     * @param string $justificativa
     * @return array
     */
    public function cancelar(NFe $nfe, string $justificativa): array
    {
        try {
            // Validate
            if (!$nfe->canBeCancelled()) {
                throw new \Exception('NF-e não pode ser cancelada. Verifique se está autorizada e dentro do prazo de 24 horas.');
            }

            if (strlen($justificativa) < 15) {
                throw new \Exception('A justificativa deve ter no mínimo 15 caracteres.');
            }

            // Create event record
            $event = NFeEvent::create([
                'workspace_id' => $this->workspaceId,
                'nfe_id' => $nfe->id,
                'tipo' => 'cancelamento',
                'sequencia' => 1,
                'justificativa' => $justificativa,
                'status' => 'processando',
            ]);

            // Generate cancellation event XML
            $chave = $nfe->chave_acesso;
            $protocolo = $nfe->protocolo;
            $xJust = $justificativa;

            $response = $this->tools->sefazCancela($chave, $xJust, $protocolo);

            // Log the request
            $this->logSefazRequest('cancelamento', $chave, $response, $nfe->id);

            // Parse response
            $xml = simplexml_load_string($response);
            $cStat = (string)$xml->retEvento->infEvento->cStat;

            if ($cStat === '135' || $cStat === '101') {
                // Event registered successfully
                $event->update([
                    'status' => 'registrado',
                    'protocolo' => (string)$xml->retEvento->infEvento->nProt,
                    'codigo_status' => $cStat,
                    'mensagem' => (string)$xml->retEvento->infEvento->xMotivo,
                    'xml_retorno' => $response,
                    'data_evento' => now(),
                ]);

                // Update NFe status
                $nfe->update([
                    'status' => 'cancelada',
                    'data_cancelamento' => now(),
                    'xml_cancelamento' => $response,
                ]);

                return [
                    'success' => true,
                    'message' => 'NF-e cancelada com sucesso!',
                    'protocolo' => (string)$xml->retEvento->infEvento->nProt,
                ];
            } else {
                // Event rejected
                $event->update([
                    'status' => 'rejeitado',
                    'codigo_status' => $cStat,
                    'mensagem' => (string)$xml->retEvento->infEvento->xMotivo,
                    'xml_retorno' => $response,
                ]);

                throw new \Exception('Cancelamento rejeitado: ' . (string)$xml->retEvento->infEvento->xMotivo);
            }
        } catch (\Exception $e) {
            if (isset($event)) {
                $event->update([
                    'status' => 'rejeitado',
                    'mensagem' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Create Carta de Correção Eletrônica (CC-e)
     *
     * @param NFe $nfe
     * @param string $correcao
     * @return array
     */
    public function cartaCorrecao(NFe $nfe, string $correcao): array
    {
        try {
            // Validate
            if (!$nfe->isAutorizada()) {
                throw new \Exception('Apenas NF-e autorizadas podem ter Carta de Correção.');
            }

            if (strlen($correcao) < 15) {
                throw new \Exception('A correção deve ter no mínimo 15 caracteres.');
            }

            // Get next sequence
            $sequencia = NFeEvent::getNextSequence($nfe->id, 'carta_correcao');

            if ($sequencia > 20) {
                throw new \Exception('Limite de 20 Cartas de Correção atingido para esta NF-e.');
            }

            // Create event record
            $event = NFeEvent::create([
                'workspace_id' => $this->workspaceId,
                'nfe_id' => $nfe->id,
                'tipo' => 'carta_correcao',
                'sequencia' => $sequencia,
                'correcao' => $correcao,
                'status' => 'processando',
            ]);

            // Generate CC-e event XML
            $chave = $nfe->chave_acesso;
            $xCorrecao = $correcao;

            $response = $this->tools->sefazCCe($chave, $xCorrecao, $sequencia);

            // Log the request
            $this->logSefazRequest('carta_correcao', $chave, $response, $nfe->id);

            // Parse response
            $xml = simplexml_load_string($response);
            $cStat = (string)$xml->retEvento->infEvento->cStat;

            if ($cStat === '135' || $cStat === '101') {
                // Event registered successfully
                $event->update([
                    'status' => 'registrado',
                    'protocolo' => (string)$xml->retEvento->infEvento->nProt,
                    'codigo_status' => $cStat,
                    'mensagem' => (string)$xml->retEvento->infEvento->xMotivo,
                    'xml_retorno' => $response,
                    'data_evento' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Carta de Correção registrada com sucesso!',
                    'protocolo' => (string)$xml->retEvento->infEvento->nProt,
                    'sequencia' => $sequencia,
                ];
            } else {
                // Event rejected
                $event->update([
                    'status' => 'rejeitado',
                    'codigo_status' => $cStat,
                    'mensagem' => (string)$xml->retEvento->infEvento->xMotivo,
                    'xml_retorno' => $response,
                ]);

                throw new \Exception('CC-e rejeitada: ' . (string)$xml->retEvento->infEvento->xMotivo);
            }
        } catch (\Exception $e) {
            if (isset($event)) {
                $event->update([
                    'status' => 'rejeitado',
                    'mensagem' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Inutilizar numeração de NF-e
     *
     * @param string $serie
     * @param int $numeroInicial
     * @param int $numeroFinal
     * @param string $justificativa
     * @return array
     */
    public function inutilizar(string $serie, int $numeroInicial, int $numeroFinal, string $justificativa): array
    {
        try {
            if (strlen($justificativa) < 15) {
                throw new \Exception('A justificativa deve ter no mínimo 15 caracteres.');
            }

            if ($numeroFinal < $numeroInicial) {
                throw new \Exception('O número final deve ser maior ou igual ao número inicial.');
            }

            $ano = date('y');
            $mes = date('m');

            $response = $this->tools->sefazInutiliza(
                $serie,
                $numeroInicial,
                $numeroFinal,
                $justificativa,
                $ano
            );

            // Log the request
            $this->logSefazRequest('inutilizacao', "$serie-$numeroInicial-$numeroFinal", $response);

            // Parse response
            $xml = simplexml_load_string($response);
            $cStat = (string)$xml->infInut->cStat;

            if ($cStat === '102') {
                return [
                    'success' => true,
                    'message' => 'Numeração inutilizada com sucesso!',
                    'protocolo' => (string)$xml->infInut->nProt,
                ];
            } else {
                throw new \Exception('Inutilização rejeitada: ' . (string)$xml->infInut->xMotivo);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Consultar situação da NF-e
     *
     * @param string $chave
     * @return array
     */
    public function consultar(string $chave): array
    {
        try {
            $response = $this->tools->sefazConsultaChave($chave);

            // Log the request
            $this->logSefazRequest('consulta', $chave, $response);

            // Parse response
            $xml = simplexml_load_string($response);
            $cStat = (string)$xml->protNFe->infProt->cStat ?? (string)$xml->cStat;

            return [
                'success' => true,
                'codigo' => $cStat,
                'mensagem' => (string)($xml->protNFe->infProt->xMotivo ?? $xml->xMotivo),
                'protocolo' => (string)($xml->protNFe->infProt->nProt ?? ''),
                'xml' => $response,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao consultar NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Consultar status do serviço SEFAZ
     *
     * @return array
     */
    public function consultarStatus(): array
    {
        try {
            $response = $this->tools->sefazStatus();

            // Parse response
            $xml = simplexml_load_string($response);
            $cStat = (string)$xml->cStat;

            return [
                'success' => true,
                'codigo' => $cStat,
                'mensagem' => (string)$xml->xMotivo,
                'online' => $cStat === '107',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'online' => false,
                'mensagem' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log SEFAZ request
     */
    private function logSefazRequest(string $operation, string $request, string $response, ?int $nfeId = null)
    {
        SefazLog::create([
            'workspace_id' => $this->workspaceId,
            'nfe_id' => $nfeId,
            'operacao' => $operation,
            'uf' => $this->config['siglaUF'],
            'ambiente' => $this->config['tpAmb'] == 1 ? 'producao' : 'homologacao',
            'request' => $request,
            'response' => $response,
            'status_code' => null,
            'mensagem' => null,
            'tempo_resposta' => null
        ]);
    }
}

