# Módulo Fiscal Brasileiro (FiscalBR)

## Descrição

Módulo addon para o Gestor Easy v3 (WorkDo Dash SaaS) que implementa funcionalidades de compliance fiscal brasileiro, incluindo:

- ✅ Emissão de NF-e (Nota Fiscal Eletrônica)
- ✅ Emissão de NFC-e (Nota Fiscal de Consumidor Eletrônica)
- 🚧 Emissão de NFS-e (Nota Fiscal de Serviços Eletrônica) - Em desenvolvimento
- 🚧 Geração de SPED Fiscal (EFD ICMS/IPI) - Em desenvolvimento

## Versão

**1.2.0** - Fase 3: Eventos de NF-e (Cancelamento, CC-e, Consultas)

## Requisitos

- PHP >= 8.1
- Laravel 10+
- MySQL 8.0+
- Certificado Digital A1 (formato .pfx)

## Instalação

1. Faça upload do arquivo ZIP do módulo através do **Add-On Manager** no painel Super Admin
2. Ative o módulo após o upload
3. Configure o módulo em **Subscription Settings** para disponibilizá-lo aos clientes
4. Execute as migrations do banco de dados (automático na instalação)

## Configuração

### 1. Dados da Empresa

Acesse **Fiscal Brasileiro > Configurações** e preencha:
- CNPJ
- Razão Social
- Nome Fantasia
- Inscrição Estadual
- Inscrição Municipal
- CNAE
- Regime Tributário
- Ambiente (Homologação/Produção)

### 2. Certificado Digital

Faça upload do certificado digital A1 (.pfx) e informe a senha.

**Importante:** O certificado e a senha são armazenados de forma criptografada no banco de dados.

### 3. Numeração

O sistema gerencia automaticamente a numeração sequencial de NF-e e NFC-e.

## Funcionalidades Implementadas

### Dashboard Fiscal
- Visão geral de notas emitidas
- Estatísticas de valores
- Ações rápidas

### Configurações
- Cadastro de dados da empresa
- Gerenciamento de certificado digital
- Controle de numeração

### NF-e
- ✅ Criação de NF-e
- ✅ Geração de XML (layout 4.00)
- ✅ Assinatura digital
- ✅ Transmissão para SEFAZ
- ✅ Geração de DANFE (PDF)
- ✅ Download de XML autorizado
- ✅ Cancelamento de NF-e
- ✅ Carta de Correção Eletrônica (CC-e)
- ✅ Consulta de situação na SEFAZ
- ✅ Logs de auditoria

### NFC-e
- 🚧 Em desenvolvimento

## Roadmap

### Fase 1 ✅ Concluída
- [x] Estrutura básica do módulo
- [x] Migrations e Models
- [x] Service Providers
- [x] Views básicas
- [x] Controllers

### Fase 2 ✅ Concluída
- [x] Implementação completa de emissão de NF-e
- [x] Integração com biblioteca NFePHP
- [x] Comunicação com SEFAZ
- [x] Geração de DANFE
- [x] Assinatura digital
- [x] Processamento assíncrono

### Fase 3 ✅ Concluída
- [x] Cancelamento de NF-e
- [x] Carta de Correção Eletrônica (CC-e)
- [x] Consulta de situação na SEFAZ
- [x] Inutilização de numeração
- [x] Eventos de NF-e

### Fase 4 (Próxima)
- [ ] Implementação de NFC-e
- [ ] Integração com módulo POS
- [ ] QR Code para NFC-e

### Fase 5
- [ ] Geração de SPED Fiscal (EFD ICMS/IPI)
- [ ] Exportação de arquivos SPED

### Fase 6
- [ ] Emissão de NFS-e (padrão ABRASF)
- [ ] Integração com prefeituras

## Estrutura do Banco de Dados

- `fiscalbr_configs` - Configurações fiscais por workspace
- `fiscalbr_certificates` - Certificados digitais
- `fiscalbr_nfe` - Notas fiscais (NF-e e NFC-e)
- `fiscalbr_nfe_items` - Itens das notas fiscais
- `fiscalbr_nfe_events` - Eventos de NF-e (cancelamento, CC-e)
- `fiscalbr_sefaz_logs` - Logs de comunicação com SEFAZ

## Segurança

- Certificados digitais armazenados com criptografia AES-256
- Senhas criptografadas
- Logs de auditoria de todas as operações
- Controle de acesso via middleware `PlanModuleCheck`

## Suporte

Para suporte técnico, entre em contato:
- Email: joelson@jracing.dev.br
- Sistema: https://workdo.jracing.dev.br

## Licença

MIT License

## Desenvolvido por

JRacing Dev - Gestor Easy v3

