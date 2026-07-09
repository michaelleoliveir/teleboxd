# Teleboxd — Contexto do Projeto

## Visão Geral
Catálogo social de séries: diário de reviews (nota + curtidas) e uma
timeline com as reviews mais curtidas da comunidade. Projeto pessoal
para praticar Laravel + PostgreSQL a fundo.

Plano completo (schema, roadmap, decisões em aberto): @plano-teleboxd.md

## Stack
- Backend: Laravel
- Frontend: Livewire + Tailwind (sem SPA separada)
- Banco: PostgreSQL
- Fila / Cache: Redis
- Ambiente: Docker Compose
- Auth: Laravel Breeze (stack Livewire)
- Dados de séries: TMDB API (gratuita para uso não-comercial, exige atribuição)

## Decisões de Arquitetura (não mudar sem discutir antes)

- **Nunca** chamar a API do TMDB em tempo real durante o acesso do
  usuário. Um job agendado sincroniza séries populares periodicamente e
  grava (upsert) em `shows`/`genres`. A aplicação sempre lê do Postgres.
- Pôsteres: não baixar/armazenar imagem. Guardar só `poster_path` e montar
  a URL na hora de exibir: `https://image.tmdb.org/t/p/w500/{poster_path}`.
- `shows.average_rating` vem da média das reviews dos usuários do sistema
  — nunca do `vote_average` do TMDB.
- `shows.average_rating`, `shows.reviews_count` e `reviews.likes_count`
  são campos desnormalizados. Recalcular via **Model Observer** sempre
  que uma review ou curtida é criada, editada ou removida — não calcular
  com `COUNT()`/`AVG()` na hora de exibir a tela.
- Usuário pode ter mais de uma review por série (rewatch é permitido).
  Nunca adicionar unique constraint em `(user_id, show_id)` em `reviews`.
- `review_likes` precisa de unique constraint em `(review_id, user_id)`
  para impedir curtida duplicada.
- Rating: escala de 0 a 5 estrelas.

## Convenções

- Tabelas e colunas em `snake_case`, em inglês (padrão Laravel).
- Uma tabela por migration, com nome descritivo da ação (ex:
  `create_reviews_table`, não `update_db`).
- Toda FK usada em filtro ou ordenação ganha índice (ex:
  `(show_id, created_at)` em `reviews`).

## Comandos (atualizar assim que o projeto for scaffolded)

- Subir ambiente: `docker compose up -d`
- Rodar migrations: `docker compose exec app php artisan migrate`
- Sync de séries do TMDB: `docker compose exec app php artisan tmdb:sync` (planejado, ainda não existe)
- Tinker: `docker compose exec app php artisan tinker`

## Status Atual

Projeto ainda não scaffolded. Próximo passo: Fase 0 do roadmap (setup do
Docker + Breeze + chave da API TMDB). Ver seção 7 de
@plano-teleboxd.md.