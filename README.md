# Padrões do projeto

## Branch

```
<tipo>/task-<id>-<descrição-curta-sem-acentos>
```

Tipos: `feat`, `fix`, `hotfix`, `release`, `chore`

Tudo minúsculo, palavras separadas por hífen.

Exemplos:
```
feat/task-5-inicializar-projeto
fix/task-12-corrige-timeout-login
chore/task-20-atualiza-dependencias
```

## Commit

```
task-<id> <tipo>(<escopo opcional>): <descrição>
```

Tipos: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`

```
feat(auth): adiciona validação de login
```

O hook injeta o ID da task automaticamente a partir do nome da branch:
```
task-5 feat(auth): adiciona validação de login
```

- Descrição em minúsculo, modo imperativo.
- Escopo é opcional.
- Commits pequenos e frequentes, não um só no final da tarefa.

## Fluxo

1. Pegar a tarefa no quadro do projeto, atribuí-la a sí mesmo e mover para "Em andamento".
2. Criar a branch a partir da `master` atualizada:
   ```bash
   git checkout master
   git pull
   git checkout -b feat/task-5-inicializar-projeto
   ```
3. Commits frequentes com mensagens no padrão acima.
4. Abrir a Pull Request:
   ```bash
   git push -u origin feat/task-5-inicializar-projeto
   ```
5. Mover a tarefa para "Em revisão" e linkar a PR à tarefa.
