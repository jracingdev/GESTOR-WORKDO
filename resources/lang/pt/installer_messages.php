<?php

return [

    /*
     *
     * Traduções compartilhadas.
     *
     */
    'title' => 'Instalador Laravel',
    'next' => 'Próxima Etapa',
    'back' => 'Anterior',
    'finish' => 'Instalar',
    'forms' => [
        'errorTitle' => 'Os seguintes erros ocorreram:',
    ],

    /*
     *
     * Traduções da página inicial.
     *
     */
    'welcome' => [
        'templateTitle' => 'Bem-vindo',
        'title'   => 'Instalador Laravel',
        'message' => 'Assistente de Instalação e Configuração Fácil.',
        'next'    => 'Verificar Requisitos',
    ],

    /*
     *
     * Traduções da página de requisitos.
     *
     */
    'requirements' => [
        'templateTitle' => 'Passo 1 | Requisitos do Servidor',
        'title' => 'Requisitos do Servidor',
        'next'    => 'Verificar Permissões',
    ],

    /*
     *
     * Traduções da página de permissões.
     *
     */
    'permissions' => [
        'templateTitle' => 'Passo 2 | Permissões',
        'title' => 'Permissões',
        'next' => 'Configurar Ambiente',
    ],

    /*
     *
     * Traduções da página de ambiente.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Passo 3 | Configurações de Ambiente',
            'title' => 'Configurações de Ambiente',
            'desc' => 'Por favor, selecione como você deseja configurar o arquivo <code>.env</code> da aplicação.',
            'wizard-button' => 'Configuração do Assistente de Formulário',
            'classic-button' => 'Editor de Texto Clássico',
        ],
        'wizard' => [
            'templateTitle' => 'Passo 3 | Configurações de Ambiente | Assistente Guiado',
            'title' => 'Assistente Guiado <code>.env</code>',
            'tabs' => [
                'environment' => 'Ambiente',
                'database' => 'Banco de Dados',
                'application' => 'Aplicação',
            ],
            'form' => [
                'name_required' => 'Um nome de ambiente é obrigatório.',
                'app_name_label' => 'Nome da Aplicação',
                'app_name_placeholder' => 'Nome da Aplicação',
                'app_environment_label' => 'Ambiente da Aplicação',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Desenvolvimento',
                'app_environment_label_qa' => 'QA',
                'app_environment_label_production' => 'Produção',
                'app_environment_label_other' => 'Outro',
                'app_environment_placeholder_other' => 'Digite seu ambiente...',
                'app_debug_label' => 'Debug da Aplicação',
                'app_debug_label_true' => 'Verdadeiro',
                'app_debug_label_false' => 'Falso',
                'app_log_level_label' => 'Nível de Log da Aplicação',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL da Aplicação',
                'app_url_placeholder' => 'URL da Aplicação',
                'db_connection_failed' => 'Não foi possível conectar ao banco de dados.',
                'db_connection_label' => 'Conexão do Banco de Dados',
                'db_connection_label_mysql' => 'MySQL',
                'db_connection_label_sqlite' => 'SQLite',
                'db_connection_label_pgsql' => 'PostgreSQL',
                'db_connection_label_sqlsrv' => 'SQL Server',
                'db_host_label' => 'Host do Banco de Dados',
                'db_host_placeholder' => 'Host do Banco de Dados',
                'db_port_label' => 'Porta do Banco de Dados',
                'db_port_placeholder' => 'Porta do Banco de Dados',
                'db_name_label' => 'Nome do Banco de Dados',
                'db_name_placeholder' => 'Nome do Banco de Dados',
                'db_username_label' => 'Nome de Usuário do Banco de Dados',
                'db_username_placeholder' => 'Nome de Usuário do Banco de Dados',
                'db_password_label' => 'Senha do Banco de Dados',
                'db_password_placeholder' => 'Senha do Banco de Dados',

                'app_tabs' => [
                    'more_info' => 'Mais Informações',
                    'broadcasting_title' => 'Broadcasting, Cache, Sessão e Fila',
                    'broadcasting_label' => 'Driver de Broadcast',
                    'broadcasting_placeholder' => 'Driver de Broadcast',
                    'cache_label' => 'Driver de Cache',
                    'cache_placeholder' => 'Driver de Cache',
                    'session_label' => 'Driver de Sessão',
                    'session_placeholder' => 'Driver de Sessão',
                    'queue_label' => 'Driver de Fila',
                    'queue_placeholder' => 'Driver de Fila',
                    'redis_label' => 'Driver Redis',
                    'redis_host' => 'Host Redis',
                    'redis_password' => 'Senha Redis',
                    'redis_port' => 'Porta Redis',

                    'mail_label' => 'E-mail',
                    'mail_driver_label' => 'Driver de E-mail',
                    'mail_driver_placeholder' => 'Driver de E-mail',
                    'mail_host_label' => 'Host de E-mail',
                    'mail_host_placeholder' => 'Host de E-mail',
                    'mail_port_label' => 'Porta de E-mail',
                    'mail_port_placeholder' => 'Porta de E-mail',
                    'mail_username_label' => 'Nome de Usuário de E-mail',
                    'mail_username_placeholder' => 'Nome de Usuário de E-mail',
                    'mail_password_label' => 'Senha de E-mail',
                    'mail_password_placeholder' => 'Senha de E-mail',
                    'mail_encryption_label' => 'Criptografia de E-mail',
                    'mail_encryption_placeholder' => 'Criptografia de E-mail',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'ID do App Pusher',
                    'pusher_app_id_palceholder' => 'ID do App Pusher',
                    'pusher_app_key_label' => 'Chave do App Pusher',
                    'pusher_app_key_palceholder' => 'Chave do App Pusher',
                    'pusher_app_secret_label' => 'Segredo do App Pusher',
                    'pusher_app_secret_palceholder' => 'Segredo do App Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'Configurar Banco de Dados',
                    'setup_application' => 'Configurar Aplicação',
                    'install' => 'Instalar',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Passo 3 | Configurações de Ambiente | Editor Clássico',
            'title' => 'Editor de Ambiente Clássico',
            'save' => 'Salvar .env',
            'back' => 'Usar Assistente de Formulário',
            'install' => 'Salvar e Instalar',
        ],
        'success' => 'As configurações do seu arquivo .env foram salvas.',
        'errors' => 'Não foi possível salvar o arquivo .env. Por favor, crie-o manualmente.',
    ],

    'install' => 'Instalar',

    /*
     *
     * Traduções de Log de Instalação.
     *
     */
    'installed' => [
        'success_log_message' => 'Instalador Laravel instalado com sucesso em ',
    ],

    /*
     *
     * Traduções da página final.
     *
     */
    'final' => [
        'title' => 'Instalação Concluída',
        'templateTitle' => 'Instalação Concluída',
        'finished' => 'A aplicação foi instalada com sucesso.',
        'migration' => 'Saída do Console de Migração e Seed:',
        'console' => 'Saída do Console da Aplicação:',
        'log' => 'Entrada de Log de Instalação:',
        'env' => 'Arquivo .env Final:',
        'exit' => 'Clique aqui para sair',
    ],

    /*
     *
     * Traduções específicas de atualização
     *
     */
    'updater' => [
        /*
         *
         * Traduções compartilhadas.
         *
         */
        'title' => 'Atualizador Laravel',

        /*
         *
         * Traduções da página de boas-vindas para o recurso de atualização.
         *
         */
        'welcome' => [
            'title'   => 'Bem-vindo ao Atualizador',
            'message' => 'Bem-vindo ao assistente de atualização.',
        ],

        /*
         *
         * Traduções da página de visão geral para o recurso de atualização.
         *
         */
        'overview' => [
            'title'   => 'Visão Geral',
            'message' => 'Existe 1 atualização.|Existem :number atualizações.',
            'install_updates' => 'Instalar Atualizações',
        ],

        /*
         *
         * Traduções da página final.
         *
         */
        'final' => [
            'title' => 'Concluído',
            'finished' => 'O banco de dados da aplicação foi atualizado com sucesso.',
            'exit' => 'Clique aqui para sair',
        ],

        'log' => [
            'success_log_message' => 'Instalador Laravel atualizado com sucesso em ',
        ],
    ],
];
