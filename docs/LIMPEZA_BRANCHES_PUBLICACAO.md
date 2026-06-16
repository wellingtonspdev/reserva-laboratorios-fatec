# Checklist de Limpeza Para Publicação

Antes de tornar o repositório público, valide `main`, `develop`, branches remotas e tags.

## Auditoria de Branches

Listar branches remotas:

```powershell
git branch -r
```

Buscar arquivos sensíveis por branch:

```powershell
git grep -n -I "<senha-antiga-do-banco>" $(git for-each-ref --format="%(refname)" refs/remotes/origin)
git grep -n -I "<senha-padrao-antiga-do-seed>" $(git for-each-ref --format="%(refname)" refs/remotes/origin)
```

Também verificar:

- CSVs de exportação;
- dumps `.sql`;
- arquivos `.env`;
- nomes ou e-mails reais;
- chaves, certificados e tokens;
- diretórios `.agents`, `.planning`, `.reversa` e `_reversa_sdd`.

## Branches Remotas Encontradas na Auditoria

Na auditoria de preparação pública, estas branches remotas ainda exigem limpeza, merge sanitizado ou exclusão antes da abertura pública:

- `origin/backup/remoto-antes-substituicao-20260429-145625`
- `origin/corrige-seed-sem-departamentos`
- `origin/deploy-railway-e-preparacao-testes`
- `origin/develop`
- `origin/main`
- `origin/padroniza-idioma-interface`
- `origin/seed-dados-reais-junho-railway`
- `origin/sync/classroombookings-master-funcional`

Após o merge desta branch de preparação, `main` e `develop` devem apontar para a versão sanitizada. As demais branches devem ser excluídas se não houver necessidade operacional comprovada.

## Depois do Merge Final

1. Fazer merge da branch sanitizada em `main`.
2. Atualizar `develop` a partir da `main` sanitizada.
3. Excluir branches remotas antigas que contenham dados reais ou documentação interna.
4. Confirmar que só restaram branches necessárias.
5. Confirmar que tags antigas não apontam para commits com dados reais.

## Risco de Histórico

Remover arquivos em um commit não apaga o histórico. Se os dados já foram enviados ao GitHub, use uma das rotas:

- criar um repositório público novo a partir de um snapshot limpo;
- ou reescrever histórico de todas as branches/tags relevantes e comunicar todos os colaboradores antes do force push.

A opção mais segura para publicação é criar um repositório público limpo.
