<?php

namespace Workdo\TaxOptimizer\Services;

use Illuminate\Support\Facades\DB;
use Workdo\TaxOptimizer\Models\TaxAnalysis;

class TaxOptimizerService
{
    /**
     * Simula a aplicação do Mecanismo de Regras de Otimização Tributária.
     * Na versão final, esta lógica seria um modelo de Machine Learning.
     *
     * @param array $data Dados de entrada (CNAE, Receita, Custos, XMLs, etc.)
     * @return array Resultados da análise
     */
    public function runAnalysis(array $data): array
    {
        // 1. Ingestão de Dados (Simulada)
        $cnae = $data['cnae'] ?? 'N/A';
        $revenue = $data['revenue'] ?? 0;
        $costs = $data['costs'] ?? 0;
        $payroll = $data['payroll'] ?? 0;

        // 2. Simulação de Regimes Tributários
        $regimes = $this->simulateTaxRegimes($revenue, $costs, $payroll);

        // 3. Aplicação de Regras Fiscais (Simulada)
        $creditsFound = $this->applyTaxRules($cnae, $regimes);

        // 4. Cálculo de Economia e Créditos
        $bestRegime = $regimes['best_regime'];
        $potentialEconomy = $regimes['economy_potential'];
        $recoveryAmount = array_sum(array_column($creditsFound, 'amount'));

        return [
            'cnae' => $cnae,
            'best_regime' => $bestRegime,
            'economy_potential' => $potentialEconomy,
            'recovery_amount' => $recoveryAmount,
            'credits_found' => $creditsFound,
            'report_summary' => "Análise concluída. Regime ideal: {$bestRegime}. Economia potencial: R$ {$potentialEconomy}. Créditos identificados: R$ {$recoveryAmount}.",
        ];
    }

    /**
     * Simula a simulação de Lucro Real, Presumido e Simples Nacional.
     */
    private function simulateTaxRegimes($revenue, $costs, $payroll): array
    {
        // Lógica de simulação simplificada
        $taxReal = $revenue * 0.15 - ($costs * 0.0925); // Exemplo simplificado
        $taxPresumed = $revenue * 0.08 * 0.15; // Exemplo simplificado
        $taxSimple = $revenue * 0.045; // Exemplo simplificado (Anexo I)

        $taxes = [
            'Lucro Real' => $taxReal,
            'Lucro Presumido' => $taxPresumed,
            'Simples Nacional' => $taxSimple,
        ];

        $bestRegime = array_keys($taxes, min($taxes))[0];
        $maxTax = max($taxes);
        $economyPotential = $maxTax - min($taxes);

        return [
            'taxes' => $taxes,
            'best_regime' => $bestRegime,
            'economy_potential' => round($economyPotential, 2),
        ];
    }

    /**
     * Simula a aplicação de regras de recuperação de crédito.
     */
    private function applyTaxRules($cnae, $regimes): array
    {
        // Simulação de regras baseadas em CNAE e Regime
        $credits = [];

        if ($regimes['best_regime'] === 'Lucro Real' && str_starts_with($cnae, '47')) { // Comércio Varejista
            $credits[] = [
                'name' => 'Crédito de PIS/COFINS sobre Insumos',
                'amount' => 50000.00,
                'legal_base' => 'Lei 10.833/03, Art. 3º',
            ];
        }

        if ($regimes['best_regime'] === 'Lucro Presumido') {
            $credits[] = [
                'name' => 'Recuperação de ICMS-ST (Substituição Tributária)',
                'amount' => 25000.00,
                'legal_base' => 'Decisão Judicial Recente',
            ];
        }

        return $credits;
    }
}
