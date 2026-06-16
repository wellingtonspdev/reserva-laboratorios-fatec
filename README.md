# Reserva de Laboratórios FATEC

![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3-EE4323.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2F8.4-blue.svg)
![Docker](https://img.shields.io/badge/Docker-ready-2496ED.svg)
![License](https://img.shields.io/badge/License-AGPL--3.0--or--later-green.svg)

Sistema web para reserva e gestão de laboratórios, salas, períodos e usuários, adaptado a partir do projeto open-source [ClassroomBookings](https://github.com/classroombookings/classroombookings).

Este repositório público é um snapshot sanitizado do fork usado para a FATEC. Ele não contém dados reais de agenda, professores, usuários, credenciais, CSVs privados ou dumps de banco. A operação em produção deve usar credenciais, domínio, banco, usuários e dados fornecidos pela equipe responsável pelo deploy.

## Sumário

- [Sobre o Projeto](#sobre-o-projeto)
- [Motivação](#motivação)
- [Funcionalidades](#funcionalidades)
- [Stack Técnica](#stack-técnica)
- [Arquitetura](#arquitetura)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [Execução com Docker](#execução-com-docker)
- [Execução Manual](#execução-manual)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Seed e Dados de Demonstração](#seed-e-dados-de-demonstração)
- [Comandos de Desenvolvimento](#comandos-de-desenvolvimento)
- [Testes e Validação](#testes-e-validação)
- [Deploy](#deploy)
- [Segurança e Dados Sensíveis](#segurança-e-dados-sensíveis)
- [Mudanças em Relação ao Original](#mudanças-em-relação-ao-original)
- [Licença](#licença)

## Sobre o Projeto

O Reserva de Laboratórios FATEC centraliza a visualização e a administração de reservas de ambientes acadêmicos. A aplicação permite consultar salas disponíveis, visualizar reservas atuais e próximas, administrar cadastros e configurar dados operacionais do sistema.

O projeto mantém a base do ClassroomBookings, mas recebeu adaptações de interface, idioma, responsividade, Docker, bootstrap de banco e documentação para facilitar uso em ambiente acadêmico brasileiro.

## Motivação

A motivação principal é oferecer uma solução simples, auditável e implantável em container para organizar reservas de laboratórios e salas, reduzindo dependência de planilhas, controles manuais e informações dispersas.

Os objetivos deste fork são:

- padronizar a interface para o contexto FATEC/CPS;
- usar português brasileiro como idioma padrão;
- manter alternância de idioma com dicionários revisados;
- facilitar deploy por terceiros com Docker;
- separar código público de dados reais;
- preservar a licença e a base open-source do ClassroomBookings;
- documentar claramente o que foi alterado em relação ao software original.

## Funcionalidades

- Autenticação de usuários.
- Visualização de reservas por dia, sala, período e semestre.
- Indicação de reserva atual e próxima reserva.
- Gestão de salas e grupos de salas.
- Cadastro de recursos, equipamentos e detalhes de ambientes.
- Administração de usuários, departamentos e perfis de acesso.
- Configurações gerais do sistema.
- Suporte a português brasileiro e inglês.
- Tela de setup reorganizada para navegação administrativa.
- Layout responsivo para desktop e mobile.
- Bootstrap automatizado da estrutura do banco em ambiente Docker.
- Seed opcional para homologação controlada, sem dados reais versionados.

## Stack Técnica

| Camada | Tecnologia |
| --- | --- |
| Backend | PHP 8.2+ |
| Framework | CodeIgniter 3 |
| Banco | MySQL 8.0/8.4 ou MariaDB compatível |
| Frontend | Views PHP, CSS, JavaScript, Tailwind CSS para CSS customizado |
| Build CSS | Node.js LTS, npm, Tailwind CSS |
| Container | Docker, Docker Compose |
| Deploy | Dockerfile e `railway.json` para deploy baseado em container |
| Testes | Script PHP executado via npm |
| Licença | AGPL-3.0-or-later |

## Arquitetura

A aplicação segue a arquitetura tradicional do CodeIgniter 3:

- `index.php` é o ponto de entrada HTTP.
- `crbs-core/application/controllers` concentra os controladores.
- `crbs-core/application/models` concentra acesso a dados e regras de domínio.
- `crbs-core/application/views` contém as telas renderizadas no servidor.
- `crbs-core/application/language` contém os dicionários de idioma.
- `crbs-core/application/migrations` mantém a evolução estrutural do banco.
- `assets` contém CSS, JavaScript, imagens e identidade visual.

No Docker, o código é copiado para dentro da imagem. Por isso, mudanças em PHP, views, assets, scripts Docker ou configurações da aplicação exigem novo build da imagem.

Fluxo resumido de execução:

1. O navegador acessa o container `app`.
2. O servidor PHP embutido atende a aplicação pela porta interna `8000`.
3. A aplicação lê configurações geradas em `local/config.php`.
4. O banco MySQL é acessado pelo host configurado em `DB_HOST`.
5. Volumes Docker preservam arquivos locais, uploads e dados do banco.

## Estrutura de Pastas

```text
.
├── assets/                    # CSS, JS, imagens, ícones e marca visual
├── crbs-core/                 # Núcleo do ClassroomBookings/CodeIgniter
│   ├── application/           # Controllers, models, views, migrations e idiomas
│   ├── system/                # CodeIgniter 3
│   └── vendor/                # Dependências PHP versionadas do projeto original
├── docker/                    # Scripts de entrypoint e bootstrap do container
├── docs/                      # Documentação complementar de deploy e mudanças do fork
├── seed/                      # Scripts para geração/importação de seed privado
├── tests/                     # Testes automatizados disponíveis no fork
├── uploads/                   # Diretório de uploads controlado pela aplicação
├── Dockerfile                 # Imagem da aplicação PHP
├── docker-compose.yml         # Ambiente local com app, MySQL e Adminer opcional
├── railway.json               # Configuração base para deploy Railway
├── package.json               # Scripts Node/Tailwind/testes
└── README.md                  # Documentação principal do projeto
```

## Execução com Docker

Requisitos:

- Docker;
- Docker Compose;
- portas locais livres para app e banco, por padrão `8000` e `3307`.

1. Crie o arquivo de ambiente:

```powershell
Copy-Item .env.example .env
```

2. Edite `.env` e defina senhas próprias:

```env
DB_PASSWORD=troque-esta-senha
DB_ROOT_PASSWORD=troque-esta-senha-root
```

3. Suba o ambiente:

```powershell
docker compose up -d --build
```

4. Verifique os containers:

```powershell
docker compose ps
docker compose logs -f app
```

5. Acesse:

[http://127.0.0.1:8000/](http://127.0.0.1:8000/)

Por padrão, o Docker importa apenas a estrutura do banco. Dados de seed não são importados automaticamente neste repositório público.

### Adminer Opcional

Para subir o Adminer em ambiente local:

```powershell
docker compose --profile tools up -d adminer
```

Acesse `http://127.0.0.1:8080/` ou a porta configurada em `ADMINER_PORT`.

## Execução Manual

A execução manual é útil apenas para desenvolvimento avançado ou ambientes sem Docker.

Requisitos mínimos:

- PHP 8.2+ com extensões compatíveis com MySQL;
- MySQL 8.0/8.4 ou MariaDB compatível;
- Node.js LTS e npm para build de CSS;
- servidor web apontando para `index.php`.

Passos gerais:

1. Configure o banco MySQL.
2. Crie `local/config.php` com credenciais do banco ou use o fluxo de instalação do sistema.
3. Importe a estrutura base disponível em `crbs-core/application/modules/install/resources/structure.sql`.
4. Execute as migrations necessárias.
5. Instale dependências Node e gere CSS:

```powershell
npm ci
npm run build:css
```

Para a maioria dos operadores, a execução via Docker é a opção recomendada.

## Variáveis de Ambiente

| Variável | Padrão local | Descrição |
| --- | --- | --- |
| `APP_ENV` | `development` | Ambiente da aplicação. Use `production` em produção real. |
| `APP_BASE_URL` | `http://127.0.0.1:8000/` | URL pública/base do sistema. |
| `APP_PORT` | `8000` | Porta local publicada para o container da aplicação. |
| `TZ` | `America/Sao_Paulo` | Fuso horário da aplicação e do banco. |
| `DB_HOST` | `db` no Compose | Host do MySQL. |
| `DB_PORT` | `3306` interno / `3307` local | Porta do MySQL. |
| `DB_NAME` | `classroombookings` | Nome do banco. |
| `DB_USER` | `classroombookings` | Usuário do banco. |
| `DB_PASSWORD` | sem valor seguro | Senha do usuário do banco. |
| `DB_ROOT_PASSWORD` | sem valor seguro | Senha root do MySQL local. |
| `AUTO_BOOTSTRAP_DB` | `1` | Quando ativo, cria estrutura inicial se o banco estiver vazio. |
| `INCLUDE_SEED` | `0` | Quando ativo, tenta gerar/importar seed privado. |
| `SEED_CSV_PATH` | vazio | Caminho para CSV privado de seed. |
| `SEED_USER_PASSWORD` | vazio | Senha temporária para usuários gerados por seed. |
| `SEED_ADMIN_USERS` | `aux_coord,aux_doc` | Usuários que devem receber perfil administrativo no seed. |
| `EXTEND_SEED_UNTIL` | vazio | Data limite para extensão de reservas recorrentes em seed. |
| `APP_FAKE_NOW` | vazio | Data/hora simulada para testes controlados. |
| `ADMINER_PORT` | `8080` | Porta local do Adminer quando o profile `tools` é usado. |

## Seed e Dados de Demonstração

Este repositório público não versiona dados reais. Isso inclui:

- CSVs de exportação;
- nomes reais de professores ou usuários;
- agendas reais;
- dumps SQL locais;
- arquivos `.env`;
- senhas, chaves ou certificados.

Para homologação privada, o operador pode habilitar seed explicitamente:

```env
INCLUDE_SEED=1
SEED_CSV_PATH=/caminho/privado/exportacao.csv
SEED_USER_PASSWORD=senha-temporaria-segura
EXTEND_SEED_UNTIL=2026-07-31
```

O CSV privado deve seguir o formato esperado por `seed/generate_seed.php`. Não publique CSVs, dumps SQL ou arquivos `.env`.

Em produção real, mantenha:

```env
INCLUDE_SEED=0
```

## Comandos de Desenvolvimento

Instalar dependências Node:

```powershell
npm ci
```

Gerar CSS minificado:

```powershell
npm run build:css
```

Gerar CSS em modo watch:

```powershell
npm run dev:css
```

Rodar teste PHP disponível:

```powershell
npm test
```

Rebuild do container após alterações de aplicação:

```powershell
docker compose build app
docker compose up -d --no-deps app
```

Se a mudança não aparecer:

```powershell
docker compose up -d --force-recreate --no-deps app
```

## Testes e Validação

Validação local recomendada antes de entregar alterações:

```powershell
npm test
docker compose config
docker compose build app
docker compose up -d
docker compose ps
```

Depois de subir, valide pelo menos:

- `/`;
- `/bookings`;
- `/setup`;
- `/profile/edit`;
- login;
- troca de idioma;
- criação/cancelamento de reserva em ambiente controlado.

No Docker:

```powershell
docker compose exec app npm test
```

## Deploy

O deploy de produção deve seguir [docs/SUPORTE_DEPLOY.md](docs/SUPORTE_DEPLOY.md).

Pontos obrigatórios para o operador:

- configurar `APP_ENV=production`;
- definir domínio público em `APP_BASE_URL`;
- usar banco MySQL provisionado para o ambiente;
- usar credenciais próprias e seguras;
- manter backups do banco;
- validar logs e rotas principais após o deploy;
- manter `INCLUDE_SEED=0` em produção, salvo migração planejada;
- não executar comandos que removam volumes de produção sem backup validado.

### Railway

O arquivo `railway.json` usa o Dockerfile do projeto. Em Railway, configure um serviço para a aplicação e um serviço MySQL, depois defina as variáveis de ambiente necessárias.

Se Railway disponibilizar variáveis como `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER` e `MYSQLPASSWORD`, o entrypoint consegue usá-las. Também é possível configurar explicitamente `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`.

## Segurança e Dados Sensíveis

Este repositório foi preparado para ser público, mas a segurança operacional depende do ambiente final.

Boas práticas obrigatórias:

- não versionar `.env`;
- não versionar CSVs privados;
- não versionar dumps SQL;
- não versionar certificados, chaves privadas ou tokens;
- trocar senhas temporárias antes de produção;
- restringir acesso administrativo;
- manter backup periódico;
- monitorar logs da aplicação e do banco.

## Cuidados Para Uso Público

Este repositório público foi criado a partir de um snapshot sanitizado, sem reaproveitar o histórico do repositório interno.

Ao usar, publicar forks ou preparar deploys:

1. Confirme que não há CSVs, dumps SQL, `.env`, chaves ou certificados versionados.
2. Use dados reais apenas em ambiente privado e controlado.
3. Mantenha `INCLUDE_SEED=0` em produção, salvo em migração planejada.
4. Configure credenciais, domínio, backups e monitoramento fora do repositório.

## Mudanças em Relação ao Original

Resumo das principais mudanças deste fork:

- identidade visual adaptada para FATEC/CPS;
- português brasileiro como idioma padrão;
- dicionário inglês preservado e revisado;
- telas de reservas, setup, perfil e detalhes de salas ajustadas;
- responsividade revisada para desktop e mobile;
- botões de voltar e navegação administrativa padronizados;
- Dockerfile, Docker Compose e bootstrap de banco adicionados;
- seed privado opcional para homologação;
- correções de warnings relacionados a idioma/headers;
- documentação de deploy e entrega pública.

Detalhes completos: [docs/MUDANCAS_DO_ORIGINAL.md](docs/MUDANCAS_DO_ORIGINAL.md).

## Documentação Complementar

- [docs/SUPORTE_DEPLOY.md](docs/SUPORTE_DEPLOY.md): guia para equipe responsável por produção.
- [docs/MUDANCAS_DO_ORIGINAL.md](docs/MUDANCAS_DO_ORIGINAL.md): mudanças do fork em relação ao ClassroomBookings original.

## Origem do Projeto

Este sistema é derivado do ClassroomBookings, software open-source para gerenciamento de reservas de salas. Este fork mantém a licença original e documenta as adaptações feitas para o contexto FATEC.

Projeto original: [https://github.com/classroombookings/classroombookings](https://github.com/classroombookings/classroombookings)

## Licença

Este projeto mantém a licença do ClassroomBookings: GNU Affero General Public License v3.0 ou posterior. Consulte [LICENSE.txt](LICENSE.txt).
