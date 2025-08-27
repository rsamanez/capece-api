# PHP Vanilla Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Overview

This is a pure PHP implementation without any framework dependencies. It demonstrates how to build a complete REST API using only native PHP features.

## Features

- **Zero dependencies**: Pure PHP 8.2+ implementation
- **Self-contained**: Single `index.php` file with all functionality
- **REST API**: Complete CRUD operations for tracking and evidence
- **File uploads**: Evidence upload with validation
- **JSON storage**: File-based data persistence
- **Docker ready**: Containerized deployment

## Setup

### Option 1: Using PHP Built-in Server

1. Start the server:
```bash
php -S localhost:8080 index.php
```

### Option 2: Using Docker

1. Build the image:
```bash
docker build -t tracking-api-php .
```

2. Run the container:
```bash
docker run -d -p 8080:8080 --name tracking-php tracking-api-php
```
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
