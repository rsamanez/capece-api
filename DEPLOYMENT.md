# Deployment Guide / Guía de Despliegue

[English](#english-deployment-guide) | [Español](#guía-de-despliegue-en-español)

---

## English Deployment Guide

### Prerequisites

- Docker and Docker Compose (recommended)
- OR individual language runtimes:
  - Node.js 16+
  - Python 3.11+
  - PHP 8.2+
  - Go 1.21+
  - Rust 1.72+

### Quick Start with Docker

1. Clone the repository:
```bash
git clone <repository-url>
cd package-tracking-api
```

2. Start all services:
```bash
docker-compose up -d
```

3. Test the APIs:
```bash
./test-all.sh
```

### Manual Installation

#### Node.js Express
```bash
cd nodejs-express
npm install
npm start
# Runs on http://localhost:3000
```

#### Python Django
```bash
cd python-django
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
python manage.py migrate
python manage.py runserver 8000
# Runs on http://localhost:8000
```

#### PHP Laravel
```bash
cd php-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --port=8080
# Runs on http://localhost:8080
```

#### Go
```bash
cd go
go mod tidy
go run main.go
# Runs on http://localhost:8081
```

#### Rust
```bash
cd rust
cargo build
cargo run
# Runs on http://localhost:8082
```

### Environment Variables

Each implementation supports the following environment variables:

- `PORT`: Server port (optional, uses defaults)
- `DEBUG`: Enable debug mode (development only)

### Production Deployment

#### Using Docker

1. Build production images:
```bash
docker-compose -f docker-compose.prod.yml build
```

2. Deploy:
```bash
docker-compose -f docker-compose.prod.yml up -d
```

#### Using Kubernetes

See `k8s/` directory for Kubernetes manifests.

#### Load Balancer Configuration

Use Nginx or HAProxy to distribute traffic across implementations:

```nginx
upstream tracking_backends {
    server localhost:3000;
    server localhost:8000;
    server localhost:8080;
    server localhost:8081;
    server localhost:8082;
}

server {
    listen 80;
    location /api/v1/tracking/ {
        proxy_pass http://tracking_backends;
    }
}
```

### Monitoring

- Health checks available at `/health` on each service
- Metrics can be collected using Prometheus
- Logs are output to stdout for container orchestration

### Security Considerations

- Enable HTTPS in production
- Implement rate limiting
- Add API authentication (API keys, OAuth)
- Use proper CORS settings
- Keep dependencies updated

---

## Guía de Despliegue en Español

### Prerrequisitos

- Docker y Docker Compose (recomendado)
- O runtimes individuales de lenguajes:
  - Node.js 16+
  - Python 3.11+
  - PHP 8.2+
  - Go 1.21+
  - Rust 1.72+

### Inicio Rápido con Docker

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd package-tracking-api
```

2. Iniciar todos los servicios:
```bash
docker-compose up -d
```

3. Probar las APIs:
```bash
./test-all.sh
```

### Instalación Manual

#### Node.js Express
```bash
cd nodejs-express
npm install
npm start
# Se ejecuta en http://localhost:3000
```

#### Python Django
```bash
cd python-django
python -m venv venv
source venv/bin/activate  # En Windows: venv\Scripts\activate
pip install -r requirements.txt
python manage.py migrate
python manage.py runserver 8000
# Se ejecuta en http://localhost:8000
```

#### PHP Laravel
```bash
cd php-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --port=8080
# Se ejecuta en http://localhost:8080
```

#### Go
```bash
cd go
go mod tidy
go run main.go
# Se ejecuta en http://localhost:8081
```

#### Rust
```bash
cd rust
cargo build
cargo run
# Se ejecuta en http://localhost:8082
```

### Variables de Entorno

Cada implementación soporta las siguientes variables de entorno:

- `PORT`: Puerto del servidor (opcional, usa valores por defecto)
- `DEBUG`: Habilita modo debug (solo desarrollo)

### Despliegue en Producción

#### Usando Docker

1. Construir imágenes de producción:
```bash
docker-compose -f docker-compose.prod.yml build
```

2. Desplegar:
```bash
docker-compose -f docker-compose.prod.yml up -d
```

#### Usando Kubernetes

Ver directorio `k8s/` para manifiestos de Kubernetes.

#### Configuración de Load Balancer

Usar Nginx o HAProxy para distribuir tráfico entre implementaciones:

```nginx
upstream tracking_backends {
    server localhost:3000;
    server localhost:8000;
    server localhost:8080;
    server localhost:8081;
    server localhost:8082;
}

server {
    listen 80;
    location /api/v1/tracking/ {
        proxy_pass http://tracking_backends;
    }
}
```

### Monitoreo

- Health checks disponibles en `/health` en cada servicio
- Métricas pueden ser recolectadas usando Prometheus
- Logs se envían a stdout para orquestación de contenedores

### Consideraciones de Seguridad

- Habilitar HTTPS en producción
- Implementar rate limiting
- Agregar autenticación API (API keys, OAuth)
- Usar configuraciones CORS apropiadas
- Mantener dependencias actualizadas
