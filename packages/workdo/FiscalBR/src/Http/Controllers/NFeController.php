<?php

namespace Workdo\FiscalBR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NFeController extends Controller
{
    /**
     * Display a listing of NF-e.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('fiscalbr::nfe.index');
    }

    /**
     * Show the form for creating a new NF-e.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('fiscalbr::nfe.create');
    }

    /**
     * Store a newly created NF-e.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // TODO: Implementar criação de NF-e
        return redirect()->route('fiscalbr.nfe.index')->with('success', 'NF-e criada com sucesso!');
    }

    /**
     * Display the specified NF-e.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('fiscalbr::nfe.show', compact('id'));
    }

    /**
     * Transmit NF-e to SEFAZ.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function transmitir($id)
    {
        // TODO: Implementar transmissão para SEFAZ
        return response()->json([
            'success' => true,
            'message' => 'NF-e transmitida com sucesso!',
            'protocolo' => '123456789012345'
        ]);
    }

    /**
     * Cancel NF-e.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelar($id)
    {
        // TODO: Implementar cancelamento
        return response()->json([
            'success' => true,
            'message' => 'NF-e cancelada com sucesso!'
        ]);
    }

    /**
     * Generate DANFE PDF.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function danfe($id)
    {
        // TODO: Implementar geração de DANFE
        return response()->download(storage_path('app/fiscalbr/danfe_exemplo.pdf'));
    }

    /**
     * Download XML.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadXml($id)
    {
        // TODO: Implementar download de XML
        return response()->download(storage_path('app/fiscalbr/nfe_exemplo.xml'));
    }
}

