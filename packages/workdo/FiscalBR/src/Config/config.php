<?php

return [
    'name' => 'FiscalBR',
    'version' => '1.0.0',
    
    // Ambientes SEFAZ
    'ambiente' => env('FISCALBR_AMBIENTE', 'homologacao'), // producao ou homologacao
    
    // Configurações de timeout para comunicação com SEFAZ
    'timeout' => 30,
    
    // Diretório para armazenamento de XMLs
    'xml_storage_path' => storage_path('app/fiscalbr/xml'),
    
    // Diretório para armazenamento de certificados
    'certificate_storage_path' => storage_path('app/fiscalbr/certificates'),
    
    // Configurações de contingência
    'contingencia' => [
        'tipo' => 'SVC-AN', // SVC-AN ou SVC-RS
        'motivo' => 'Problemas técnicos',
    ],
];

