<?php

namespace Workdo\TaxOptimizer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\TaxOptimizer\Services\TaxOptimizerService;
use Workdo\TaxOptimizer\Models\TaxAnalysis; // Assumindo que o Model será criado na próxima fase

class TaxOptimizerController extends Controller
{
    protected $taxOptimizerService;

    public function __construct(TaxOptimizerService $taxOptimizerService)
    {
        $this->taxOptimizerService = $taxOptimizerService;
    }

    public function index()
    {
        $analyses = TaxAnalysis::all(); // Buscar análises existentes
        return view('taxoptimizer::index', compact('analyses'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'cnae' => 'required|string|max:10',
            // Adicionar validação de outros campos de entrada
        ]);

        // Simulação de dados de entrada (que seriam extraídos dos XMLs)
        $inputData = [
            'cnae' => $request->cnae,
            'revenue' => 500000.00, // Simulação
            'costs' => 200000.00, // Simulação
            'payroll' => 50000.00, // Simulação
        ];

        // 1. Iniciar a análise
        $analysisResult = $this->taxOptimizerService->runAnalysis($inputData);

        // 2. Salvar o registro da análise
        $analysis = TaxAnalysis::create([
            'company_id' => auth()->user()->currentCompany()->id, // Assumindo método de obtenção da empresa
            'cnae' => $request->cnae,
            'status' => 'completed',
            'input_data' => $inputData,
            'analysis_result' => $analysisResult,
            'report_summary' => $analysisResult['report_summary'],
        ]);

        return redirect()->route('taxoptimizer.analyze', $analysis->id)->with('success', 'Análise concluída com sucesso.');
    }

    public function analyze(TaxAnalysis $analysis)
    {
        return view('taxoptimizer::analysis_report', compact('analysis'));
    }

    // Métodos para gerenciamento de regras (Simulação)
    public function rulesIndex()
    {
        return view('taxoptimizer::rules.index');
    }

    public function rulesStore(Request $request)
    {
        // Lógica para salvar a regra (simulada)
        return redirect()->route('taxoptimizer.rules.index')->with('success', 'Regra fiscal simulada salva com sucesso.');
    }
}
