# Reserva de Laboratórios FATEC

![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3-EE4323.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2F8.4-blue.svg)
![License](https://img.shields.io/badge/License-AGPL--3.0-green.svg)

Sistema de reserva de laboratórios adaptado a partir do projeto open-source [ClassroomBookings](https://github.com/classroombookings/classroombookings).

Este fork foi preparado para entrega pública sem dados reais de agenda, professores ou usuários da FATEC. A operação em produção deve ser feita com credenciais, banco e dados fornecidos pelo responsável pelo deploy.

## Principais Mudanças do Fork

- Interface adaptada para identidade visual FATEC/CPS.
- Idioma padrão em português brasileiro, com dicionários revisados em português e inglês.
- Ajustes de responsividade e usabilidade nas telas de reservas, setup, perfil e detalhes de salas.
- Dockerfile e Docker Compose para execução local e deploy baseado em container.
- Bootstrap automatizado de estrutura do banco para ambientes Docker.
- Documentação pública de suporte, deploy e diferenças em relação ao software original.

Veja detalhes em [docs/MUDANCAS_DO_ORIGINAL.md](docs/MUDANCAS_DO_ORIGINAL.md).

## Stack

- PHP 8.2+
- CodeIgniter 3
- MySQL 8.0/8.4 ou MariaDB compatível
- Node.js LTS apenas para build de CSS
- Docker e Docker Compose para execução recomendada

## Execução Recomendada com Docker

1. Crie o arquivo de ambiente:

```powershell
Copy-Item .env.example .env
```

2. Edite `.env` e preencha senhas próprias:

```env
DB_PASSWORD=troque-esta-senha
DB_ROOT_PASSWORD=troque-esta-senha-root
```

3. Suba os containers:

```powershell
docker compose up -d --build
```

4. Verifique o estado:

```powershell
docker compose ps
docker compose logs -f app
```

5. Acesse:

[http://127.0.0.1:8000/](http://127.0.0.1:8000/)

Por padrão, o Docker importa apenas a estrutura do banco. Dados de seed não são importados automaticamente nesta versão pública.

## Seed e Dados de Demonstração

Dados reais de agenda, usuários e professores não são versionados neste repositório.

Para gerar seed em ambiente privado, forneça explicitamente:

```env
INCLUDE_SEED=1
SEED_CSV_PATH=/caminho/privado/exportacao.csv
SEED_USER_PASSWORD=senha-temporaria-segura
EXTEND_SEED_UNTIL=2026-07-31
```

O CSV privado deve seguir o formato esperado por `seed/generate_seed.php`. Não publique CSVs, dumps SQL ou arquivos `.env`.

## Variáveis Importantes

| Variável | Uso |
| --- | --- |
| `APP_ENV` | Use `production` em produção real. |
| `APP_BASE_URL` | URL pública do sistema. |
| `APP_PORT` | Porta local exposta pelo Docker Compose. |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Conexão com MySQL. |
| `AUTO_BOOTSTRAP_DB` | Quando `1`, cria estrutura inicial se o banco estiver vazio. |
| `INCLUDE_SEED` | Quando `1`, tenta gerar/importar seed privado. Default público: `0`. |
| `EXTEND_SEED_UNTIL` | Estende reservas recorrentes apenas quando seed está habilitado. |

## Testes e Build

Instale dependências Node se necessário:

```powershell
npm ci
```

Build do CSS:

```powershell
npm run build:css
```

Teste PHP disponível no projeto:

```powershell
npm test
```

No Docker:

```powershell
docker compose exec app npm test
```

## Deploy e Suporte

O deploy de produção deve seguir [docs/SUPORTE_DEPLOY.md](docs/SUPORTE_DEPLOY.md).

Pontos obrigatórios para o operador:

- configurar `APP_ENV=production`;
- definir domínio e `APP_BASE_URL`;
- usar credenciais próprias;
- manter backups do banco;
- validar logs e rotas principais após o deploy;
- não habilitar seed em produção real sem necessidade controlada.

## Cuidados Antes de Tornar Público

Antes de mudar a visibilidade do repositório:

1. Confirme que não há CSVs, dumps SQL, `.env`, chaves ou certificados versionados.
2. Confirme que branches antigas com dados reais foram removidas ou que o repositório público foi recriado a partir de snapshot limpo.
3. Confirme que `main` e `develop` apontam para a versão sanitizada.

Checklist detalhado: [docs/LIMPEZA_BRANCHES_PUBLICACAO.md](docs/LIMPEZA_BRANCHES_PUBLICACAO.md).

## Licença

Este projeto mantém a licença do ClassroomBookings: GNU Affero General Public License v3.0. Consulte [LICENSE.txt](LICENSE.txt).
