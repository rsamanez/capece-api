# PHP Laravel Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Setup

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Start the server:
```bash
php artisan serve --port=8080
```

## API Endpoint

```
GET http://localhost:8080/api/v1/tracking/{trackingNumber}
```

## Testing

```bash
curl http://localhost:8080/api/v1/tracking/1Z999AA1234567890
```

## Dependencies

- Laravel: Web framework
- Laravel Sanctum: API authentication (optional)
