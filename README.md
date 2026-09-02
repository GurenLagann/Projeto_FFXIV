# FFXIV Market Analyzer

Aplicação web para análise de lucratividade de crafting no Final Fantasy XIV. Cruza dados de receitas (XIVAPI) com preços de mercado em tempo real (Universalis) para identificar as melhores oportunidades de lucro por servidor.

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 11 (PHP 8.3) |
| Interatividade | Livewire 4.3 |
| Frontend | TailwindCSS + Alpine.js + Chart.js (CDN) |
| Banco | MySQL 8.0 |
| Cache / Fila | Redis 7 (`predis/predis`) |
| Auth | Laravel Breeze (blade stack) |
| Infra | Docker Compose |

## Funcionalidades

- **Market Analyzer** — filtra receitas por classe, nível e stars; calcula lucro, margem e velocidade de vendas em tempo real via Universalis
- **Item Browser** — catálogo de itens craftáveis e coletáveis com preços ao vivo por servidor
- **Integração Lodestone** — vincula personagem FFXIV, importa níveis de job e pré-preenche filtros automaticamente
- **Alertas de lucro** — notificações quando um item ultrapassa threshold configurado
- **Histórico de análises** — salva e exporta resultados anteriores
- **Sync de receitas** — importa ~12.000 receitas da XIVAPI com RecipeLookup por job
- **Sync de coleta** — mapeia itens de MIN/BTN e FSH com nível e stars

## Pré-requisitos

- Docker e Docker Compose

## Instalação

```bash
git clone <repo-url>
cd FFXIV_marketplace

cp .env.example .env

docker compose up -d

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Adicione `127.0.0.1 ffxiv.local` ao `/etc/hosts` e acesse `https://ffxiv.local` (servido via Traefik compartilhado; HTTP redireciona automaticamente). Rode `mkcert -install` uma vez na máquina para o navegador confiar no certificado local sem aviso.

## Sincronização de dados

```bash
# Receitas (todas as páginas, ~12.000 itens)
docker compose exec app php artisan market:sync:items --all --limit=100

# Itens de coleta (MIN/BTN + FSH)
docker compose exec app php artisan market:sync:gathering --limit=500
```

## Comandos úteis

```bash
# Análise de mercado via CLI
docker compose exec app php artisan market:analyze Balmung
docker compose exec app php artisan market:analyze Balmung --min-profit=0 --min-sales=0 --top=30
docker compose exec app php artisan market:analyze Balmung --job=9 --json

# Verificar alertas
docker compose exec app php artisan market:alerts:check

# Fila de jobs
docker compose exec app php artisan queue:work

# Limpar cache de preços (força re-fetch da Universalis)
docker compose exec redis redis-cli FLUSHDB

# Logs
docker compose exec app tail -f storage/logs/laravel.log
```

## APIs externas

| API | Descrição | Cache |
|---|---|---|
| [Universalis](https://universalis.app) | Preços de mercado em tempo real | 5 min |
| [XIVAPI](https://xivapi.com) | Receitas, itens e dados do jogo | 24 h |

## Estrutura relevante

```
app/
  Clients/         — UniversalisClient, XIVApiClient
  DTOs/            — ItemPrice, ProfitResult
  Enums/           — Job, CostMetric, RevenueMetric
  Livewire/        — MarketAnalyzer, ItemBrowser, RecipeBrowser, HealthMonitor
  Models/          — Item, Recipe, RecipeLookup, GatheringItem, Server, Analysis, Alert
  Services/        — MarketAnalyzerService, ProfitCalculator
  Console/Commands/ — market:sync:items, market:sync:gathering, market:analyze, market:alerts:check
docs/
  apis-reference.md  — referência completa das APIs Universalis e XIVAPI
  CHANGELOG.md
```

## Licença

MIT
