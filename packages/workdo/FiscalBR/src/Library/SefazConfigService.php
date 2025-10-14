<?php

namespace Workdo\FiscalBR\Library;

use Workdo\FiscalBR\Entities\FiscalConfig;
use Workdo\FiscalBR\Entities\Certificate;

class SefazConfigService
{
    /**
     * Get SEFAZ configuration for NFePHP
     *
     * @param int $workspaceId
     * @return array
     */
    public static function getConfig(int $workspaceId): array
    {
        $fiscalConfig = FiscalConfig::where('workspace_id', $workspaceId)->first();
        $certificate = Certificate::where('workspace_id', $workspaceId)->where('ativo', true)->first();

        if (!$fiscalConfig || !$certificate) {
            throw new \Exception('Configuração fiscal ou certificado não encontrado.');
        }

        return [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => $fiscalConfig->ambiente === 'producao' ? 1 : 2,
            'razaosocial' => $fiscalConfig->razao_social,
            'siglaUF' => self::getUFFromCNPJ($fiscalConfig->cnpj),
            'cnpj' => preg_replace('/[^0-9]/', '', $fiscalConfig->cnpj),
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => '',
            'CSC' => '', // Código de Segurança do Contribuinte (para NFC-e)
            'CSCid' => '', // ID do CSC
            'aProxyConf' => [
                'proxyIp' => '',
                'proxyPort' => '',
                'proxyUser' => '',
                'proxyPass' => ''
            ]
        ];
    }

    /**
     * Get certificate content
     *
     * @param int $workspaceId
     * @return array
     */
    public static function getCertificate(int $workspaceId): array
    {
        $certificate = Certificate::where('workspace_id', $workspaceId)
            ->where('ativo', true)
            ->first();

        if (!$certificate || !$certificate->isValid()) {
            throw new \Exception('Certificado digital inválido ou expirado.');
        }

        return [
            'content' => $certificate->certificado,
            'password' => $certificate->senha
        ];
    }

    /**
     * Get UF from CNPJ
     *
     * @param string $cnpj
     * @return string
     */
    private static function getUFFromCNPJ(string $cnpj): string
    {
        // Mapeamento dos códigos UF do CNPJ
        $ufMap = [
            '11' => 'RO', '12' => 'AC', '13' => 'AM', '14' => 'RR',
            '15' => 'PA', '16' => 'AP', '17' => 'TO', '21' => 'MA',
            '22' => 'PI', '23' => 'CE', '24' => 'RN', '25' => 'PB',
            '26' => 'PE', '27' => 'AL', '28' => 'SE', '29' => 'BA',
            '31' => 'MG', '32' => 'ES', '33' => 'RJ', '35' => 'SP',
            '41' => 'PR', '42' => 'SC', '43' => 'RS', '50' => 'MS',
            '51' => 'MT', '52' => 'GO', '53' => 'DF'
        ];

        $cnpjClean = preg_replace('/[^0-9]/', '', $cnpj);
        $codigo = substr($cnpjClean, 0, 2);

        return $ufMap[$codigo] ?? 'SP';
    }

    /**
     * Get all UF codes
     *
     * @return array
     */
    public static function getAllUFs(): array
    {
        return [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins'
        ];
    }
}

