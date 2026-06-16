# Mudanças em Relação ao ClassroomBookings Original

Este fork mantém a base do ClassroomBookings, mas foi adaptado para a operação de reservas de laboratórios da FATEC.

## Mantido do Original

- Arquitetura PHP/CodeIgniter 3.
- Fluxos principais de autenticação, reservas, salas, usuários, permissões e setup.
- Estrutura base de migrations e instalação.
- Licença AGPL-3.0.

## Alterado Neste Fork

- Interface visual adaptada para identidade FATEC/CPS.
- Navegação principal revisada.
- Tela de reservas reorganizada para desktop e mobile.
- Legendas, cards de reserva, filtros e controles responsivos ajustados.
- Páginas de setup e subpáginas revisadas com botões de voltar e padronização visual.
- Tela de perfil redesenhada para seguir o padrão visual do sistema.
- Detalhes de salas unificados em página sem scroll interno duplicado.
- Correções em arquivos de idioma e fallback de linguagem.
- Português brasileiro adotado como idioma padrão.
- Dicionário inglês preservado para alternância de idioma.
- Correções de warnings relacionados a arquivos de idioma e headers.
- Dockerfile, Docker Compose e scripts de bootstrap adicionados para execução em container.

## Específico Para FATEC

- Marca e textos voltados ao contexto FATEC.
- Organização de salas, laboratórios e grupos conforme a implantação local.
- Campos auxiliares de detalhes de salas para recursos, equipamentos e manutenção.

## Produção e Deploy

- O deploy real deve usar `APP_ENV=production`.
- Dados reais não acompanham o repositório público.
- O operador de produção deve criar usuários, senhas, domínio, banco e backups.
- Seed privado só deve ser usado em homologação controlada.

## Dados Removidos da Entrega Pública

- CSVs de exportação com reservas reais.
- Dumps SQL gerados localmente.
- Relatórios internos de reconstrução/análise.
- Arquivos de automação de agente e documentação operacional interna.

## Impacto Para Quem Receber o Software

Quem receber o projeto deve tratar este repositório como um fork pronto para implantação, mas não como uma cópia de produção com dados. A configuração produtiva depende do ambiente final, banco real, domínio e política de acesso definida pela equipe responsável.
