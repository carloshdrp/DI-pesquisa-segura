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

## Matriz de requisitos

Situação do backend, conforme `Levantamento_Requisitos.pdf`. "Telas" são do gkOdin (issues #12, #13 e #32).

| Requisito | Situação | Onde |
|---|---|---|
| RF01 Indexar só fontes cadastradas | Parcial: crawler pronto; falta persistir os resultados das APIs (#16, depende da #14) | `ColetorService`, `ColetarFonteJob`, `Fonte::coletaveis()` |
| RF02 Busca para qualquer usuário | Pronto (tela: #13) | `GET /busca`, `BuscaController` |
| RF03 Limite diário para visitantes | Pronto | `limite.busca`, `LimiteBuscaService` |
| RF04 Limite maior para autenticados | Pronto | `busca.limite_autenticado` |
| RF05 Cadastro | Pronto | Fortify, `Usuario` |
| RF06 Login | Pronto | Fortify, `UsuarioUserProvider` |
| RF07 Favoritos | Pronto (tela: #12) | `BookmarkController` |
| RF08 Grupos de favoritos | Pronto (tela: #12) | `GrupoBookmarkController`, `BookmarkGrupoController` |
| RF09 Compartilhar por link | Pronto (tela: #12) | `LinkCompartilhamentoController`, `CompartilhadoController` |
| RF10 Histórico | Pronto (tela: #32) | `HistoricoController` |
| RF11 Excluir do histórico | Pronto (tela: #32) | `HistoricoController` |
| RF12 Tópicos mais pesquisados | Pronto (exibição: #13) | `busca:topicos`, `GET /topicos` |
| RF13 Nova coleta com poucos resultados | Pronto | `ColetaPorTermoService`, `ColetarTermoJob` |
| RF14 Direcionar ao site de origem | Pronto (a resposta traz a URL; sem hospedar conteúdo) | `ResultadoBuscaResource` |
| RNF01 Responsivo | Pendente: depende das telas (gkOdin) | — |
| RNF02 Respeitar robots.txt | Pronto | `fl_robots_permitido`, `Fonte::coletaveis()`, `ColetorService` |
| RNF03 Senhas criptografadas | Pronto | hash do Laravel em `ds_senha` |
| RNF04 Resposta rápida sem fontes externas | Pronto | busca só no índice local |
| RNF05 Coleta sem impactar a busca | Pronto | fila `coleta` (Horizon) |
| RNF06 Revogar links | Pronto | `DELETE /compartilhamentos/{link}` |

## Rotinas agendadas

O agendador (`php artisan schedule:work` ou cron) precisa estar rodando, assim como o Horizon.

| Comando | Frequência | O que faz |
|---|---|---|
| `busca:topicos` | a cada hora | Atualiza os tópicos mais pesquisados e pede a coleta deles |
| `coleta:fontes` | todo dia, 03:00 | Recoleta as fontes ativas e liberadas para robôs |
