# Package Tracking API / API de Seguimiento de Paquetes

[English](#english) | [Español](#español)

---

## English

A comprehensive package tracking API with implementations in multiple programming languages, providing a standardized data model similar to major logistics providers like UPS, FedEx, and DHL.

### 🚀 Features

- **Multi-language implementations**: Node.js/Express, Python/Django, PHP/Laravel, Go, Rust
- **Evidence upload functionality**: Upload and manage delivery evidence (photos, documents)
- **Standardized data model**: Compatible with major logistics providers
- **RESTful API endpoints**: GET tracking info, POST/GET/DELETE evidence
- **File upload support**: PNG, JPG, JPEG, PDF with validation
- **Comprehensive documentation**: Available in English and Spanish
- **Production-ready**: Includes error handling, validation, and best practices
- **Docker support**: Easy deployment with Docker Compose
- **Testing suite**: Automated tests for all implementations

### 📋 Available Implementations

| Language | Framework | Status | Port | Directory |
|----------|-----------|--------|------|-----------|
| Node.js  | Express   | ✅     | 3000 | `nodejs-express/` |
| Python   | Django    | ✅     | 8000 | `python-django/` |
| PHP      | Vanilla   | ✅     | 8080 | `php/` |
| Go       | Gin       | ✅     | 8083 | `go/` |
| Rust     | Actix-web | ✅     | 8082 | `rust/` |

### 🔧 Quick Start

#### Option 1: Start All Services (Recommended)
```bash
# Make scripts executable
chmod +x start-all.sh test-all.sh

# Start all implementations
./start-all.sh

# Test all endpoints
./test-all.sh
```

#### Option 2: Docker Compose
```bash
# Start all services with Docker
docker-compose up -d

# Test all endpoints
./test-all.sh
```

#### Option 3: Individual Services
See individual README files in each implementation directory.

### 📡 API Endpoint

```
GET /api/v1/tracking/{trackingNumber}
```

### 📎 Evidence Upload Endpoints

```
# Upload evidence
POST /api/v1/tracking/{trackingNumber}/evidence

# Get evidence list
GET /api/v1/tracking/{trackingNumber}/evidence

# Delete evidence
DELETE /api/v1/tracking/{trackingNumber}/evidence/{evidenceId}
```

> 📘 **Evidence Documentation**: For complete evidence upload integration guide, see [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md#evidence-upload-functionality)

### 📊 Response Format

```json
{
  "trackingNumber": "1Z999AA1234567890",
  "status": "in_transit",
  "estimatedDelivery": "2025-08-30T15:30:00Z",
  "carrier": "UPS",
  "service": "Ground",
  "origin": {
    "city": "New York",
    "state": "NY",
    "country": "USA",
    "postalCode": "10001"
  },
  "destination": {
    "city": "Los Angeles",
    "state": "CA",
    "country": "USA",
    "postalCode": "90210"
  },
  "package": {
    "weight": 2.5,
    "dimensions": {
      "length": 12,
      "width": 8,
      "height": 6
    },
    "description": "Electronics"
  },
  "events": [
    {
      "timestamp": "2025-08-26T10:00:00Z",
      "status": "picked_up",
      "location": "New York, NY",
      "description": "Package picked up",
      "facilityType": "origin"
    }
  ]
}
```

### 🧪 Testing

For detailed step-by-step testing instructions, see the [Testing Guide](TESTING_GUIDE.md).

#### Quick Test
```bash
# Start all services
./start-all.sh

# Test all implementations
./test-all.sh
```

#### Test Tracking Numbers

- `1Z999AA1234567890` - UPS package in transit
- `FDX123456789012` - FedEx package delivered

#### Manual Testing Examples
```bash
# Node.js (port 3000)
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" | jq .

# Python/Django (port 8000) - note trailing slash
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" | jq .

# PHP (port 8080) - requires Docker
curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" | jq .

# Go (port 8083)
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" | jq .

# Rust (port 8082)
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" | jq .
```

### 📚 Documentation

- [API Documentation](API_DOCS.md) - Complete API reference
- [Developer Guide](DEVELOPER_GUIDE.md) - Integration examples and best practices
- [Testing Guide](TESTING_GUIDE.md) - **Step-by-step testing instructions for all implementations**
- [Deployment Guide](DEPLOYMENT.md) - Production deployment instructions

### 🛠️ Development

#### VS Code Tasks
This project includes VS Code tasks for easy development:
- `Start All APIs` - Launch all implementations
- `Test All APIs` - Run comprehensive tests
- Individual language tasks available

#### File Structure
```
package-tracking-api/
├── nodejs-express/     # Node.js implementation
├── python-django/      # Python implementation
├── php-laravel/        # PHP implementation
├── go/                 # Go implementation
├── rust/               # Rust implementation
├── start-all.sh        # Script to start all services
├── test-all.sh         # Script to test all services
├── docker-compose.yml  # Docker composition
└── docs/               # Additional documentation
```

### 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass (`./test-all.sh`)
6. Commit your changes (`git commit -am 'Add new feature'`)
7. Push to the branch (`git push origin feature/new-feature`)
8. Submit a pull request

### 📄 License

MIT License - see [LICENSE](LICENSE) file for details.

## 👨‍💻 Credits

**Created by:** Rommel Samanez Carrillo  
**Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

This project has been developed and donated to the open source community as a contribution for learning and development of multi-language APIs. The complete implementation in 5 different programming languages (Node.js, Python, PHP, Go, Rust) demonstrates best practices in modern RESTful API development.

📋 **For detailed credits and acknowledgments, see [CREDITS.md](CREDITS.md)**

---

## Español

Una API completa de seguimiento de paquetes con implementaciones en múltiples lenguajes de programación, proporcionando un modelo de datos estandarizado similar a los principales proveedores logísticos como UPS, FedEx y DHL.

### 🚀 Características

- **Implementaciones multi-lenguaje**: Node.js/Express, Python/Django, PHP/Laravel, Go, Rust
- **Funcionalidad de subida de evidencia**: Subir y gestionar evidencia de entrega (fotos, documentos)
- **Modelo de datos estandarizado**: Compatible con principales proveedores logísticos
- **Endpoints API RESTful**: GET info de seguimiento, POST/GET/DELETE evidencia
- **Soporte de subida de archivos**: PNG, JPG, JPEG, PDF con validación
- **Documentación completa**: Disponible en inglés y español
- **Listo para producción**: Incluye manejo de errores, validación y mejores prácticas
- **Soporte Docker**: Fácil despliegue con Docker Compose
- **Suite de pruebas**: Pruebas automatizadas para todas las implementaciones

### 📋 Implementaciones Disponibles

| Lenguaje | Framework | Estado | Puerto | Directorio |
|----------|-----------|--------|--------|------------|
| Node.js  | Express   | ✅     | 3000   | `nodejs-express/` |
| Python   | Django    | ✅     | 8000   | `python-django/` |
| PHP      | Vanilla   | ✅     | 8080   | `php/` |
| Go       | Gin       | ✅     | 8083   | `go/` |
| Rust     | Actix-web | ✅     | 8082   | `rust/` |

### 🔧 Inicio Rápido

#### Opción 1: Iniciar Todos los Servicios (Recomendado)
```bash
# Hacer scripts ejecutables
chmod +x start-all.sh test-all.sh

# Iniciar todas las implementaciones
./start-all.sh

# Probar todos los endpoints
./test-all.sh
```

#### Opción 2: Docker Compose
```bash
# Iniciar todos los servicios con Docker
docker-compose up -d

# Probar todos los endpoints
./test-all.sh
```

#### Opción 3: Servicios Individuales
Ver archivos README individuales en cada directorio de implementación.

### 📡 Endpoint de la API

```
GET /api/v1/tracking/{numeroSeguimiento}
```

### 📎 Endpoints de Subida de Evidencia

```
# Subir evidencia
POST /api/v1/tracking/{numeroSeguimiento}/evidence

# Obtener lista de evidencia
GET /api/v1/tracking/{numeroSeguimiento}/evidence

# Eliminar evidencia
DELETE /api/v1/tracking/{numeroSeguimiento}/evidence/{evidenceId}
```

> 📘 **Documentación de Evidencia**: Para la guía completa de integración de subida de evidencia, ver [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md#funcionalidad-de-subida-de-evidencia)

### 🧪 Pruebas

Para instrucciones detalladas paso a paso de pruebas, consulta la [Guía de Pruebas](TESTING_GUIDE.md).

#### Prueba Rápida
```bash
# Iniciar todos los servicios
./start-all.sh

# Probar todas las implementaciones
./test-all.sh
```

#### Números de Seguimiento de Prueba

- `1Z999AA1234567890` - Paquete UPS en tránsito
- `FDX123456789012` - Paquete FedEx entregado

#### Ejemplos de Pruebas Manuales
```bash
# Node.js (puerto 3000)
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" | jq .

# Python/Django (puerto 8000) - nota la barra al final
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" | jq .

# PHP (puerto 8080) - requiere Docker
curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" | jq .

# Go (puerto 8083)
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" | jq .

# Rust (puerto 8082)
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" | jq .
```

### 📚 Documentación

- [Documentación de la API](API_DOCS.md) - Referencia completa de la API
- [Guía del Desarrollador](DEVELOPER_GUIDE.md) - Ejemplos de integración y mejores prácticas
- [Guía de Pruebas](TESTING_GUIDE.md) - **Instrucciones paso a paso para probar todas las implementaciones**
- [Guía de Despliegue](DEPLOYMENT.md) - Instrucciones de despliegue en producción

- [Documentación de la API](API_DOCS.md) - Referencia completa de la API
- [Guía del Desarrollador](DEVELOPER_GUIDE.md) - Ejemplos de integración y mejores prácticas
- [Guía de Despliegue](DEPLOYMENT.md) - Instrucciones de despliegue en producción

### 🛠️ Desarrollo

#### Tareas de VS Code
Este proyecto incluye tareas de VS Code para facilitar el desarrollo:
- `Start All APIs` - Lanzar todas las implementaciones
- `Test All APIs` - Ejecutar pruebas completas
- Tareas individuales por lenguaje disponibles

### 🤝 Contribuir

1. Haz fork del repositorio
2. Crea una rama de características (`git checkout -b feature/nueva-caracteristica`)
3. Realiza tus cambios
4. Agrega pruebas para nueva funcionalidad
5. Asegúrate de que todas las pruebas pasen (`./test-all.sh`)
6. Confirma tus cambios (`git commit -am 'Agregar nueva característica'`)
7. Empuja a la rama (`git push origin feature/nueva-caracteristica`)
8. Envía un pull request

### 📄 Licencia

Licencia MIT - ver archivo [LICENSE](LICENSE) para detalles.

## 👨‍💻 Créditos

**Creado por:** Rommel Samanez Carrillo  
**Donado por:** [BOSS.TECHNOLOGIES](https://boss.technologies)

Este proyecto ha sido desarrollado y donado a la comunidad open source como una contribución para el aprendizaje y desarrollo de APIs multi-lenguaje. La implementación completa en 5 lenguajes de programación diferentes (Node.js, Python, PHP, Go, Rust) demuestra las mejores prácticas en el desarrollo de APIs RESTful modernas.

📋 **Para más detalles sobre créditos y reconocimientos, ver [CREDITS.md](CREDITS.md)**

---

*💡 Si este proyecto te resulta útil, considera darle una ⭐ en GitHub y compartirlo con otros desarrolladores.*
