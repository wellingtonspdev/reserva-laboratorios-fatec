# Suporte de Deploy

Este documento é para a equipe que fará o deploy em produção. O deploy real não depende de dados locais do desenvolvedor e deve usar credenciais, domínio, banco e backups próprios do ambiente de produção.

## Visão Geral

- Aplicação PHP 8.2 com CodeIgniter 3.
- Build por Dockerfile.
- MySQL externo ou serviço MySQL provisionado pela plataforma.
- O container copia o código para dentro da imagem. Toda mudança de código exige rebuild da imagem.

## Variáveis Obrigatórias

```env
APP_ENV=production
APP_BASE_URL=https://dominio-publico/
TZ=America/Sao_Paulo
DB_HOST=host-do-mysql
DB_PORT=3306
DB_NAME=nome-do-banco
DB_USER=usuario-do-banco
DB_PASSWORD=senha-forte
AUTO_BOOTSTRAP_DB=1
INCLUDE_SEED=0
EXTEND_SEED_UNTIL=
```

Use `INCLUDE_SEED=0` em produção real. Habilite seed apenas em homologação controlada, com CSV privado e senha temporária definidos por variável de ambiente.

## Docker Compose Local ou Homologação

```powershell
docker compose build app
docker compose up -d --no-deps app
docker compose ps
```

Se a mudança não aparecer:

```powershell
docker compose up -d --force-recreate --no-deps app
```

Para subir banco local junto:

```powershell
docker compose up -d --build
```

## Railway

O arquivo `railway.json` usa o Dockerfile do projeto. Configure um serviço para a aplicação e um MySQL.

Se Railway expuser variáveis `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER` e `MYSQLPASSWORD`, o entrypoint consegue usá-las. Se preferir, configure explicitamente:

```env
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

Depois de gerar domínio público, defina:

```env
APP_BASE_URL=https://dominio.up.railway.app/
```

## Bootstrap do Banco

Com `AUTO_BOOTSTRAP_DB=1`, o container:

1. cria `local/config.php`;
2. espera o MySQL responder;
3. importa `structure.sql` se o banco estiver vazio;
4. marca a baseline de migrations;
5. cria `local/installed` quando as tabelas essenciais existem.

O bootstrap não deve apagar dados de produção. Mesmo assim, confirme backup antes do primeiro deploy.

## Seed Privado

Para homologação:

```env
INCLUDE_SEED=1
SEED_CSV_PATH=/caminho/privado/exportacao.csv
SEED_USER_PASSWORD=senha-temporaria-forte
SEED_ADMIN_USERS=aux_coord,aux_doc
EXTEND_SEED_UNTIL=2026-07-31
```

Não versionar CSVs, dumps SQL, arquivos `.env` ou senhas.

## Checklist Pós-Deploy

- Abrir a URL pública.
- Confirmar que não redireciona para instalador indevidamente.
- Verificar logs do app.
- Validar login com usuário criado pelo operador.
- Abrir `/bookings`.
- Abrir `/setup`.
- Abrir `/profile/edit`.
- Validar idioma padrão em português.
- Criar e cancelar uma reserva de teste em ambiente controlado.
- Confirmar que uploads, sessões e logs persistem conforme a plataforma escolhida.

## Rollback

- Voltar a imagem/release anterior na plataforma.
- Não executar `docker compose down -v` em produção, pois isso remove volumes.
- Se houver alteração de banco, restaurar backup validado.

## Responsabilidades do Operador

- Provisionar banco.
- Gerenciar credenciais.
- Configurar domínio e HTTPS.
- Definir política de backup.
- Monitorar logs e disponibilidade.
- Configurar usuários reais.
- Garantir conformidade com regras internas de dados.
