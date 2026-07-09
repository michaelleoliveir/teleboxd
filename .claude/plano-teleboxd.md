# Plano de Projeto — Teleboxd

## 1. Visão Geral

Teleboxd é um catálogo social de séries onde o usuário mantém um diário de
reviews, com uma timeline destacando as avaliações mais curtidas da
comunidade. Projeto pessoal com foco em aprofundar Laravel + PostgreSQL,
usando Docker.

## 2. Stack e Decisões Técnicas

| Camada       | Escolha                          |
|--------------|-----------------------------------|
| Backend      | Laravel                          |
| Frontend     | Livewire + Tailwind               |
| Banco        | PostgreSQL                        |
| Fila / Cache | Redis                             |
| Ambiente     | Docker Compose                    |
| Auth         | Laravel Breeze (stack Livewire)   |
| Dados de séries | TMDB API                       |

## 3. Fonte de Dados — TMDB API

- Gratuita para uso não-comercial, desde que haja atribuição ao TMDB.
- Não existe mais rate limit rígido (era 40 req/10s até 2019); hoje o teto
  prático fica em torno de 40-50 req/s — mais que suficiente pra um projeto
  pessoal.
- **Estratégia**: nunca consultar a API TMDB em tempo real durante o acesso
  do usuário. Um job agendado (`artisan schedule` + queue) sincroniza séries
  populares periodicamente e grava localmente em `shows`/`genres` (upsert).
  A aplicação sempre lê do Postgres, nunca da API diretamente.
- **Imagens**: não baixar/armazenar. Guardar só o `poster_path` retornado
  pela API e montar a URL final na hora de exibir:
  `https://image.tmdb.org/t/p/w500/{poster_path}`.

## 4. Escopo do MVP

1. **Tela de perfil** — dados básicos do usuário (nome, avatar, bio).
2. **Listagem de séries disponíveis** — catálogo paginado, populado via sync
   do TMDB.
3. **Timeline com reviews populares** — feed das reviews com mais curtidas.

> "Reviews famosas" = reviews com mais curtidas dentro da própria
> plataforma (confirmado).

## 5. Fora de Escopo (por enquanto)

Fica para as próximas fases, depois do MVP:

- Busca full-text
- Listas customizadas (tipo "Top 10 séries de suspense")
- Seguir usuários / feed social
- Estatísticas ("seu ano em séries")
- Recomendações baseadas em outros usuários

## 6. Modelo de Dados Inicial

### `users` (estende o padrão do Laravel/Breeze)
| Coluna       | Tipo          | Observação                     |
|--------------|---------------|----------------------------------|
| id           | bigint PK     |                                   |
| name         | string        |                                   |
| email        | string unique |                                   |
| password     | string        |                                   |
| avatar_path  | string, null  |                                   |
| bio          | text, null    |                                   |

### `genres`
| Coluna    | Tipo          | Observação        |
|-----------|---------------|--------------------|
| id        | bigint PK     |                     |
| tmdb_id   | integer unique| id do gênero no TMDB|
| name      | string        |                     |

### `shows` (sincronizada do TMDB)
| Coluna          | Tipo           | Observação                       |
|-----------------|----------------|-----------------------------------|
| id              | bigint PK      |                                     |
| tmdb_id         | integer unique | id da série no TMDB                 |
| name            | string         |                                     |
| overview        | text, null     | sinopse                            |
| poster_path     | string, null   | usado pra montar URL da imagem      |
| first_air_date  | date, null     |                                     |
| average_rating  | decimal(2,1), null | nota calculada a partir das reviews dos próprios usuários (não vem do TMDB) |
| reviews_count   | integer, default 0 | total de reviews recebidas, denormalizado |
| synced_at       | timestamp      | última vez que foi atualizada       |

### `show_genre` (pivot)
| Coluna    | Tipo       |
|-----------|------------|
| show_id   | FK shows   |
| genre_id  | FK genres  |

### `reviews`
| Coluna       | Tipo          | Observação                          |
|--------------|---------------|----------------------------------------|
| id           | bigint PK     |                                          |
| user_id      | FK users      |                                          |
| show_id      | FK shows      |                                          |
| rating       | decimal(2,1)  | 0 a 5 estrelas, com meia estrela (0.5 em 0.5 — avisa se quiser só inteiros) |
| body         | text, null    | texto opcional                          |
| likes_count  | integer, default 0 | contador desnormalizado (ver nota abaixo) |
| created_at   | timestamp     |                                          |

Sem constraint de unicidade em `(user_id, show_id)` — o usuário pode ter
mais de uma review para a mesma série (rewatch). Cada review conta
separadamente no cálculo de `average_rating` da série.

Índice sugerido: `(show_id, created_at)` para listar reviews de uma série
em ordem cronológica.

### `review_likes` (pivot)

Essa tabela existe pra registrar **quem** curtiu **qual** review — cada
linha é "usuário X curtiu a review Y". Sem ela, você teria só o número
total (`likes_count`), mas não conseguiria saber:

- se o usuário logado já curtiu aquela review (pra pintar o coração de
  vermelho ou deixar cinza na tela)
- quem curtiu, caso queira mostrar avatares de quem curtiu
- como "descurtir" — remover o like de uma pessoa específica sem
  mexer na contagem de outras

| Coluna      | Tipo       | Observação                              |
|-------------|------------|-------------------------------------------|
| id          | bigint PK  |                                             |
| review_id   | FK reviews |                                             |
| user_id     | FK users   |                                             |
| created_at  | timestamp  |                                             |

Constraint **unique** em `(review_id, user_id)` — impede curtir a mesma
review duas vezes; dar "unlike" é simplesmente deletar a linha
correspondente.

**Decisão técnica a testar**: manter `likes_count` (em `reviews`) e
`average_rating`/`reviews_count` (em `shows`) desnormalizados, atualizados
via **Model Observer** — toda vez que uma review é criada/editada/deletada
ou uma curtida é criada/removida, o Observer recalcula os valores. Isso
evita rodar `COUNT()`/`AVG()` toda vez que a timeline ou o catálogo carrega.
Pontos a testar: usar `increment()`/`decrement()` (atômico, sem lock
explícito) para `likes_count`, e recalcular `average_rating` com uma query
`AVG()` real no Observer, já que não dá pra somar/subtrair uma média
incrementalmente sem guardar a soma total à parte.

## 7. Roadmap de Implementação

| Fase | Entrega |
|------|---------|
| 0 — Setup | `docker-compose` com Laravel + Postgres + Redis; Breeze (stack Livewire) instalado; chave da API TMDB configurada |
| 1 — Sync de séries | Comando artisan + job em fila que sincroniza séries populares do TMDB (upsert em `shows`/`genres`) |
| 2 — Perfil | Tela de perfil (editar dados, avatar, bio) |
| 3 — Catálogo | Listagem paginada de séries, com filtro por gênero |
| 4 — Reviews + Timeline | Criar review, curtir review, feed ordenado por `likes_count` |
| 5 — Polimento | Índices, cache de queries pesadas, ajustes no Docker |

Fases futuras (pós-MVP): busca full-text, listas customizadas, seguir
usuários, estatísticas anuais.

## 8. Decisões Confirmadas e Perguntas em Aberto

Confirmado nesta rodada:
- Nota da série vem da média das reviews dos usuários (não do TMDB)
- "Reviews famosas" = mais curtidas dentro da plataforma
- Escala de 0 a 5 estrelas
- Usuário pode ter mais de uma review por série (rewatch)

Ainda em aberto:
- [ ] Meia estrela (0.5, 1.5, 2.5...) ou só estrelas inteiras?
- [ ] Recalcular `average_rating`/`likes_count` de forma síncrona (na
      própria request) ou assíncrona (job em fila)? Síncrono é mais simples
      pro MVP; assíncrono evita que a criação de uma review fique lenta se
      o show tiver muitas reviews — mas adiciona uma camada de fila que
      talvez valha mais a pena guardar pra quando o volume de dados crescer.