# Sistema de Busca Segura

Projeto acadêmico do grupo 4 (Carlos, Miguel e Odin). Um buscador que indexa apenas fontes confiáveis e cadastradas, respeita o `robots.txt` e aponta o usuário para o site de origem, sem hospedar conteúdo. Os requisitos estão em `Levantamento_Requisitos.pdf` (RF01-RF14, RNF01-RNF06); a situação de cada um está na [matriz de requisitos](#matriz-de-requisitos).

Stack: Laravel 13, Livewire e Flux (telas), Fortify, Horizon, MySQL, Redis, Pest, Pint e Larastan.

## Como rodar

O ambiente usa o Laravel Sail (Docker).

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed   # cria as fontes (OpenAlex, arXiv, Crossref) e um usuário de teste
./vendor/bin/sail artisan horizon          # processa as filas
./vendor/bin/sail artisan schedule:work    # roda as rotinas agendadas
```

Verificações antes de cada commit:

```bash
./vendor/bin/sail test 2>&1 | grep -v Deprecated      # o PHP 8.6 imprime avisos de depreciação do framework
./vendor/bin/sail pint --dirty
./vendor/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G
```

Os testes de busca usam o trait `Tests\Concerns\UsaIndiceFulltext`: o índice FULLTEXT do InnoDB só enxerga linhas commitadas, e a suíte roda cada teste numa transação.

## Configuração

Variáveis em `.env` (padrões em `config/busca.php`):

| Variável | Padrão | Efeito |
|---|---|---|
| `BUSCA_LIMITE_ANONIMO` | 10 | Buscas por dia de visitantes (RF03) |
| `BUSCA_LIMITE_AUTENTICADO` | 50 | Buscas por dia de usuários logados (RF04) |
| `BUSCA_POR_PAGINA` | 10 | Resultados por página |
| `BUSCA_MINIMO_RESULTADOS` | 3 | Abaixo disso, a busca pede uma nova coleta (RF13) |
| `BUSCA_RECOLETA_INTERVALO_HORAS` | 6 | Intervalo mínimo entre coletas do mesmo termo |
| `BUSCA_TOPICOS_DIAS` / `BUSCA_TOPICOS_LIMITE` | 7 / 10 | Período e tamanho da lista de tópicos (RF12) |
| `BUSCA_COLETA_*` | — | Fila, user-agent, limites e atraso do crawler |
| `OPENALEX_API_KEY` | — | Chave da API do OpenAlex |

`QUEUE_CONNECTION` e `CACHE_STORE` devem ser `redis` em produção: os tópicos populares ficam no cache e a coleta roda na fila `coleta`.

## Arquitetura

A busca responde só com o índice local (MySQL FULLTEXT), por isso não depende das fontes externas (RNF04). A coleta acontece em segundo plano, na fila `coleta` do Horizon (RNF05).

```
GET /busca ─ limite.busca ─ BuscaController ─ PesquisaService ─▶ tb_pagina_indexada (só fontes ativas)
                                  │
                                  └─ poucos resultados ─▶ ColetaPorTermoService ─▶ tb_fila_coleta ─▶ ColetarTermoJob
                                                                                                         ├─ ColetarFonteJob (crawler) ─▶ PaginaIndexada
                                                                                                         └─ OpenAlex/ArXiv/Crossref BuscaJob ─▶ IndexadorResultadosService ─▶ PaginaIndexada
```

| Peça | Papel |
|---|---|
| `LimitarBuscasDiarias` + `LimiteBuscaService` | Limite diário por usuário ou por sessão anônima (cookie `sessao_anonima`); páginas além da primeira não consomem limite |
| `FiltroBuscaService` | Recusa termos de `busca.termos_bloqueados` (422) |
| `PesquisaService` | FULLTEXT; ordem = relevância × (1 + credibilidade/10) |
| `ColetorService` / `ColetarFonteJob` | Crawler de uma fonte (spatie/crawler); respeita robots.txt, fica no domínio e ignora `noindex` |
| `OpenAlexService`, `ArXivService`, `CrossrefService` | APIs externas, normalizadas em `ResultadoBusca` e gravadas pelo `IndexadorResultadosService` |
| `Fonte::coletaveis()` | Única porta de entrada das coletas: fonte ativa e com `fl_robots_permitido` (RNF02) |
| `TopicosPopularesService` | Agrega os termos mais buscados (comando `busca:topicos`) |

Toda coleta é registrada em `tb_fila_coleta` (origem Fonte ou Busca, status Pendente, Executando, Concluída ou Falha). O domínio usa tabelas e colunas em português, no padrão do Diagrama ER (`tb_usuario`, `ds_email`...). Autenticação: modelo `Usuario`, com o provider `UsuarioUserProvider` mapeando `email` para `ds_email`.

## Rotas

Respostas em JSON quando a requisição envia `Accept: application/json`; as rotas de ação (POST, PUT, PATCH, DELETE), sem JSON, redirecionam de volta com `status` na sessão. Rotas autenticadas exigem login.

| Rota | Acesso | Resposta |
|---|---|---|
| `GET /busca?q=&page=` | público | `data[]` (`id`, `titulo`, `resumo`, `url`, `fonte{nome, credibilidade}`), `links`, `meta`, `termo`; 422 termo inválido ou bloqueado; 429 limite diário (cabeçalho `Retry-After`) |
| `GET /topicos` | público | `data[]` com `termo` e `total` |
| `GET /compartilhado/{token}` | público | Favorito ou grupo compartilhado |
| `GET /bookmarks[?grupo=]` | login | Favoritos paginados, com página e fonte |
| `PUT /bookmarks/{pagina}` | login | Favorita: 201 ao criar, 200 se já existia |
| `DELETE /bookmarks/{pagina}` | login | Desfavorita: 204, idempotente |
| `PATCH /bookmarks/{bookmark}/grupo` | login | Move para um grupo (`id_grupo_bookmark`, ou `null`) |
| `GET/POST /grupos`, `PUT/PATCH/DELETE /grupos/{grupo}` | login | Grupos de favoritos (`nm_grupo`, único por usuário) |
| `POST /compartilhamentos` | login | `id_bookmark` ou `id_grupo_bookmark`; 201 com `url` |
| `DELETE /compartilhamentos/{link}` | login | Revoga o link (RNF06) |
| `GET /historico` | login | Buscas do usuário, mais recentes primeiro, paginadas |
| `DELETE /historico/{busca}` | login | Remove um item (204; 403 se for de outro usuário) |
| `DELETE /historico` | login | Limpa todo o histórico (204) |

## Pendências

- Telas: com o gkOdin (issues #12, #13 e #32); RNF01 (responsivo) depende delas.
- Diagrama ER (fora do repositório): incluir `tb_grupo_bookmark`, `tb_link_compartilhamento`, a coluna `id_grupo_bookmark` em `tb_bookmark`, `remember_token` em `tb_usuario` e remover a tabela `users`.

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
| RF01 Indexar só fontes cadastradas | Pronto | `ColetorService`, `ColetarFonteJob`, `IndexadorResultadosService`, `Fonte::coletaveis()` |
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
