# Order & Campaign Service

A REST API built with Symfony 8 for managing shopping quotes, campaign discounts, and order processing.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Runtime | PHP 8.4 |
| Framework | Symfony 8.0 |
| Database | MariaDB 11.3 |
| Cache | Redis 7.2 |
| ORM | Doctrine ORM 3 |
| Web Server | Nginx + PHP-FPM |
| API Docs | Nelmio API Doc (Swagger UI) |
| Container | Docker + Docker Compose |

## Getting Started

```bash
bin/start
```

This single command:
- Builds and starts all Docker containers
- Runs `composer install`
- Adds `orderService` to `/etc/hosts`
- Runs database migrations
- Seeds the database with products, categories and authors

All endpoints require an `X-API-Token` header.

## API Documentation:
> **All request/response schemas, parameters and examples are documented interactively in Swagger UI. After running `bin/start`, the API documentation will be available at:**
> 
> ### http://orderService/api-docs.html
> ### http://127.0.0.1/api-docs.html
**API base URL:** `http://orderService/api`
## Authentication



| Token | Customer ID |
|-------|------------|
| `api-token-user1` | 1 |
| `api-token-user2` | 2 |

```http
X-API-Token: api-token-user1
```

## API Endpoints

### Quote

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/quote/add-items` | Add multiple items to quote atomically and apply the best campaign discount |
| `GET` | `/api/quote` | Get active quote |
| `DELETE` | `/api/quote/remove-items` | Remove items from quote |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/orders/create` | Create order from active quote |
| `GET` | `/api/orders/list` | List orders (paginated, max 100 per page) |
| `GET` | `/api/orders/show/{orderNumber}` | Get order by order number (Redis cached, 1h TTL) |

## Campaign Engine

The campaign engine evaluates all active campaigns and applies the one with the **highest discount**. Campaigns are automatically discovered via `CampaignInterface` — adding a new campaign requires no changes outside of its own class.

### Active Campaigns

| Campaign | Condition | Discount |
|----------|-----------|----------|
| **3 Al 2 Öde** | Total quantity ≥ 3 | Cheapest item free |
| **Roman Kategorisi %15** | Cart contains a book from category Roman (ID: 1) | 15% off entire cart |
| **Sabahattin Ali %20** | Cart contains a book by Sabahattin Ali (author ID: 3) | 20% off all matching items |
| **20 TL Üzeri %10** | Cart subtotal ≥ 20 TL | 10% off entire cart |

### Shipping

| Condition | Shipping Fee |
|-----------|-------------|
| Grand total ≥ 50 TL | Free |
| Grand total < 50 TL | 10 TL |

Shipping is calculated after the campaign discount is applied.

## Caching

Order show endpoint uses **Doctrine Second Level Cache (L2 Cache)** backed by Redis.

| Entity | Cache Type | TTL |
|--------|-----------|-----|
| `Order` | READ_ONLY | 1h |
| `OrderItem` collection | READ_ONLY | 1h |
| `OrderAddress` collection | READ_ONLY | 1h |

Config: `doctrine.yaml` → `second_level_cache`, pool: `doctrine.second_level_cache_pool` → `cache.app` (Redis).

## Request Logging

Every API request is automatically logged to the `request_logs` table via `RequestLogListener`.


## Architecture

```
src/
├── Controller/         # QuoteController, OrderController
├── EventListener/      # RequestLogListener, ApiExceptionListener
├── Service/
│   ├── QuoteService    # Quote management, campaign application, shipping
│   ├── OrderService    # Order creation with stock lock, Redis cache
│   └── Campaign/
│       ├── CampaignEngine          # Evaluates all campaigns, picks best
│       ├── CampaignInterface       # Contract for all campaigns
│       └── Rule/
│           ├── ThreeForTwoCampaign
│           ├── CategoryDiscountCampaign
│           ├── AuthorDiscountCampaign
│           └── PercentageDiscountCampaign
├── Entity/             # Product, Quote, QuoteItem, Order, OrderItem, RequestLog
├── Repository/         # Doctrine repositories
├── Request/            # MapRequestPayload DTOs with validation
├── Security/           # API token authentication
└── Exception/          # Domain exceptions
```

### Adding a New Campaign

1. Create a class implementing `CampaignInterface`
2. Implement `getName()`, `isEligible()`, `apply()`, `distributeDiscount()`
3. Done — the engine picks it up automatically via `_instanceof` service tagging

```php
class MyNewCampaign implements CampaignInterface
{
    public function getName(): string { return 'My Campaign'; }
    public function isEligible(Quote $quote): bool { ... }
    public function apply(Quote $quote): float { ... }
    public function distributeDiscount(Quote $quote): void { ... }
}
```

### Quote Add Items Flow

```
POST /api/quote/add-items
  └── Validate all items (product exists, stock sufficient)
  └── Get or create quote
  └── Apply all items to quote
  └── recalculate()
        ├── Subtotal
        ├── CampaignEngine.evaluate() → picks highest discount
        │     ├── ThreeForTwoCampaign.isEligible()?
        │     ├── CategoryDiscountCampaign.isEligible()?
        │     ├── AuthorDiscountCampaign.isEligible()?
        │     └── PercentageDiscountCampaign.isEligible()?
        ├── Distribute discount to items
        └── Shipping: grandTotal < 50 TL → +10 TL
```

### Order Creation Flow

```
POST /api/orders/create
  └── Load active quote
  └── Validate quote is not empty
  └── Begin DB transaction
  └── SELECT products FOR UPDATE  ← pessimistic write lock
  └── Check stock for each item
  └── Deduct stock
  └── Create Order + OrderItems
  └── Delete Quote
  └── Commit
```

## Other Commands

```bash
bin/stop      # Stop containers
bin/restart   # Restart containers
bin/composer  # Run composer commands
bin/console   # Run Symfony console commands
```