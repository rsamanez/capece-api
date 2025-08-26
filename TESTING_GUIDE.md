# Testing Guide / Guía de Pruebas

[English](#english-testing-guide) | [Español](#guía-de-pruebas-en-español)

---

## English Testing Guide

### 🧪 Complete Step-by-Step Testing Guide

This guide provides detailed instructions for testing all 5 implementations of the Package Tracking API.

### Prerequisites

Ensure you have the following installed:
- **Node.js** (v16+): `node --version`
- **Python** (v3.8+): `python --version`
- **Go** (v1.21+): `go version`
- **Rust** (v1.72+): `rustc --version`
- **Docker** (optional for PHP): `docker --version`
- **curl** for testing: `curl --version`
- **jq** for JSON formatting: `jq --version`

### 🚀 Method 1: Quick Test All Services

#### Step 1: Clone and Navigate
```bash
git clone <repository-url>
cd package-tracking-api
```

#### Step 2: Make Scripts Executable
```bash
chmod +x start-all.sh test-all.sh
```

#### Step 3: Start All Services
```bash
./start-all.sh
```

#### Step 4: Test All Services
```bash
./test-all.sh
```

### 🔧 Method 2: Manual Testing (Individual Services)

#### Testing Node.js/Express Implementation

**Step 1: Navigate and Install**
```bash
cd nodejs-express
npm install
```

**Step 2: Start Server**
```bash
npm start
# Or: node server.js
```

**Step 3: Test Endpoints**
```bash
# Health Check
curl -s "http://localhost:3000/health" | jq .

# Valid Tracking Number - UPS
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:3000/api/v1/tracking/FDX123456789012" | jq .

# Invalid Tracking Number
curl -s "http://localhost:3000/api/v1/tracking/INVALID123" | jq .
```

**Expected Results:**
- Health: `{"status": "OK", "timestamp": "..."}`
- Valid tracking: Full tracking object with events
- Invalid tracking: `{"error": "tracking_not_found", "message": "...", "trackingNumber": "INVALID123"}`

---

#### Testing Python/Django Implementation

**Step 1: Navigate and Setup**
```bash
cd python-django
python -m venv .venv
source .venv/bin/activate  # On Windows: .venv\Scripts\activate
pip install -r requirements.txt
```

**Step 2: Start Server**
```bash
python manage.py runserver 8000
# Or in background: nohup python manage.py runserver 8000 > django.log 2>&1 &
```

**Step 3: Test Endpoints**
```bash
# Health Check
curl -s "http://localhost:8000/health/" | jq .

# Valid Tracking Number - UPS (note the trailing slash)
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:8000/api/v1/tracking/FDX123456789012/" | jq .

# Invalid Tracking Number
curl -s "http://localhost:8000/api/v1/tracking/INVALID123/" | jq .
```

**Important Notes:**
- Django requires trailing slashes in URLs
- Virtual environment must be activated

---

#### Testing PHP Implementation (Docker)

**Step 1: Navigate and Build**
```bash
cd php-laravel
docker build -t tracking-api-php .
```

**Step 2: Start Container**
```bash
docker run -d -p 8080:8080 --name tracking-php tracking-api-php
```

**Step 3: Test Endpoints**
```bash
# Health Check
curl -s "http://localhost:8080/health" | jq .

# Valid Tracking Number - UPS
curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:8080/api/v1/tracking/FDX123456789012" | jq .

# Invalid Tracking Number
curl -s "http://localhost:8080/api/v1/tracking/INVALID123" | jq .
```

**Step 4: Stop Container**
```bash
docker stop tracking-php
docker rm tracking-php
```

---

#### Testing Go/Gin Implementation

**Step 1: Navigate and Install Dependencies**
```bash
cd go
go mod tidy
```

**Step 2: Start Server**
```bash
go run main.go
# Or in background: nohup go run main.go > go.log 2>&1 &
```

**Step 3: Test Endpoints**
```bash
# Health Check
curl -s "http://localhost:8081/health" | jq .

# Valid Tracking Number - UPS
curl -s "http://localhost:8081/api/v1/tracking/1Z999AA1234567890" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:8081/api/v1/tracking/FDX123456789012" | jq .

# Invalid Tracking Number
curl -s "http://localhost:8081/api/v1/tracking/INVALID123" | jq .
```

---

#### Testing Rust/Actix-web Implementation

**Step 1: Navigate and Build**
```bash
cd rust
cargo build
```

**Step 2: Start Server**
```bash
cargo run
# Or in background: nohup cargo run > rust.log 2>&1 &
```

**Step 3: Test Endpoints**
```bash
# Health Check
curl -s "http://localhost:8082/health" | jq .

# Valid Tracking Number - UPS
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:8082/api/v1/tracking/FDX123456789012" | jq .

# Invalid Tracking Number
curl -s "http://localhost:8082/api/v1/tracking/INVALID123" | jq .
```

### 🧾 Test Data Reference

#### Available Tracking Numbers

| Tracking Number | Carrier | Status | Description |
|----------------|---------|--------|-------------|
| `1Z999AA1234567890` | UPS | in_transit | Package from NY to LA |
| `FDX123456789012` | FedEx | delivered | Documents from Chicago to Miami |

#### Expected Status Codes

- **200**: Successful tracking found
- **404**: Tracking number not found
- **400**: Invalid tracking number format (if validation implemented)

### 🐛 Troubleshooting

#### Common Issues

1. **Port Already in Use**
   ```bash
   # Check what's using the port
   lsof -i :3000
   # Kill the process
   kill -9 <PID>
   ```

2. **Python Virtual Environment Issues**
   ```bash
   # Recreate virtual environment
   rm -rf .venv
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```

3. **Go Module Issues**
   ```bash
   # Clean module cache
   go clean -modcache
   go mod download
   ```

4. **Rust Compilation Issues**
   ```bash
   # Clean and rebuild
   cargo clean
   cargo build
   ```

5. **Docker Issues**
   ```bash
   # Remove container and rebuild
   docker stop tracking-php
   docker rm tracking-php
   docker build -t tracking-api-php .
   ```

### 📱 Testing with Postman

#### Import Collection
Create a Postman collection with these requests:

**Collection: Package Tracking API**

1. **Node.js Health**
   - Method: GET
   - URL: `http://localhost:3000/health`

2. **Node.js Tracking**
   - Method: GET
   - URL: `http://localhost:3000/api/v1/tracking/1Z999AA1234567890`

3. **Python Health**
   - Method: GET
   - URL: `http://localhost:8000/health/`

4. **Python Tracking**
   - Method: GET
   - URL: `http://localhost:8000/api/v1/tracking/1Z999AA1234567890/`

5. **PHP Health**
   - Method: GET
   - URL: `http://localhost:8080/health`

6. **PHP Tracking**
   - Method: GET
   - URL: `http://localhost:8080/api/v1/tracking/1Z999AA1234567890`

7. **Go Health**
   - Method: GET
   - URL: `http://localhost:8081/health`

8. **Go Tracking**
   - Method: GET
   - URL: `http://localhost:8081/api/v1/tracking/1Z999AA1234567890`

9. **Rust Health**
   - Method: GET
   - URL: `http://localhost:8082/health`

10. **Rust Tracking**
    - Method: GET
    - URL: `http://localhost:8082/api/v1/tracking/1Z999AA1234567890`

### 📊 Performance Testing

#### Simple Load Test with curl
```bash
# Test 100 requests to each endpoint
for i in {1..100}; do
  curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" > /dev/null
done
```

#### Response Time Comparison
```bash
# Measure response times
time curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Node.js
time curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" > /dev/null # Python
time curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" > /dev/null  # PHP
time curl -s "http://localhost:8081/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Go
time curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Rust
```

---

## Guía de Pruebas en Español

### 🧪 Guía Completa de Pruebas Paso a Paso

Esta guía proporciona instrucciones detalladas para probar las 5 implementaciones de la API de Seguimiento de Paquetes.

### Requisitos Previos

Asegúrate de tener instalado lo siguiente:
- **Node.js** (v16+): `node --version`
- **Python** (v3.8+): `python --version`
- **Go** (v1.21+): `go version`
- **Rust** (v1.72+): `rustc --version`
- **Docker** (opcional para PHP): `docker --version`
- **curl** para pruebas: `curl --version`
- **jq** para formateo JSON: `jq --version`

### 🚀 Método 1: Prueba Rápida de Todos los Servicios

#### Paso 1: Clonar y Navegar
```bash
git clone <url-del-repositorio>
cd package-tracking-api
```

#### Paso 2: Hacer Ejecutables los Scripts
```bash
chmod +x start-all.sh test-all.sh
```

#### Paso 3: Iniciar Todos los Servicios
```bash
./start-all.sh
```

#### Paso 4: Probar Todos los Servicios
```bash
./test-all.sh
```

### 🔧 Método 2: Pruebas Manuales (Servicios Individuales)

#### Probando la Implementación Node.js/Express

**Paso 1: Navegar e Instalar**
```bash
cd nodejs-express
npm install
```

**Paso 2: Iniciar Servidor**
```bash
npm start
# O: node server.js
```

**Paso 3: Probar Endpoints**
```bash
# Verificación de Salud
curl -s "http://localhost:3000/health" | jq .

# Número de Seguimiento Válido - UPS
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:3000/api/v1/tracking/FDX123456789012" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:3000/api/v1/tracking/INVALID123" | jq .
```

**Resultados Esperados:**
- Salud: `{"status": "OK", "timestamp": "..."}`
- Seguimiento válido: Objeto completo de seguimiento con eventos
- Seguimiento inválido: `{"error": "tracking_not_found", "message": "...", "trackingNumber": "INVALID123"}`

---

#### Probando la Implementación Python/Django

**Paso 1: Navegar y Configurar**
```bash
cd python-django
python -m venv .venv
source .venv/bin/activate  # En Windows: .venv\Scripts\activate
pip install -r requirements.txt
```

**Paso 2: Iniciar Servidor**
```bash
python manage.py runserver 8000
# O en background: nohup python manage.py runserver 8000 > django.log 2>&1 &
```

**Paso 3: Probar Endpoints**
```bash
# Verificación de Salud
curl -s "http://localhost:8000/health/" | jq .

# Número de Seguimiento Válido - UPS (nota la barra al final)
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:8000/api/v1/tracking/FDX123456789012/" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:8000/api/v1/tracking/INVALID123/" | jq .
```

**Notas Importantes:**
- Django requiere barras al final en las URLs
- El entorno virtual debe estar activado

---

#### Probando la Implementación PHP (Docker)

**Paso 1: Navegar y Construir**
```bash
cd php-laravel
docker build -t tracking-api-php .
```

**Paso 2: Iniciar Contenedor**
```bash
docker run -d -p 8080:8080 --name tracking-php tracking-api-php
```

**Paso 3: Probar Endpoints**
```bash
# Verificación de Salud
curl -s "http://localhost:8080/health" | jq .

# Número de Seguimiento Válido - UPS
curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:8080/api/v1/tracking/FDX123456789012" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:8080/api/v1/tracking/INVALID123" | jq .
```

**Paso 4: Detener Contenedor**
```bash
docker stop tracking-php
docker rm tracking-php
```

---

#### Probando la Implementación Go/Gin

**Paso 1: Navegar e Instalar Dependencias**
```bash
cd go
go mod tidy
```

**Paso 2: Iniciar Servidor**
```bash
go run main.go
# O en background: nohup go run main.go > go.log 2>&1 &
```

**Paso 3: Probar Endpoints**
```bash
# Verificación de Salud
curl -s "http://localhost:8081/health" | jq .

# Número de Seguimiento Válido - UPS
curl -s "http://localhost:8081/api/v1/tracking/1Z999AA1234567890" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:8081/api/v1/tracking/FDX123456789012" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:8081/api/v1/tracking/INVALID123" | jq .
```

---

#### Probando la Implementación Rust/Actix-web

**Paso 1: Navegar y Construir**
```bash
cd rust
cargo build
```

**Paso 2: Iniciar Servidor**
```bash
cargo run
# O en background: nohup cargo run > rust.log 2>&1 &
```

**Paso 3: Probar Endpoints**
```bash
# Verificación de Salud
curl -s "http://localhost:8082/health" | jq .

# Número de Seguimiento Válido - UPS
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:8082/api/v1/tracking/FDX123456789012" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:8082/api/v1/tracking/INVALID123" | jq .
```

### 🧾 Referencia de Datos de Prueba

#### Números de Seguimiento Disponibles

| Número de Seguimiento | Transportista | Estado | Descripción |
|----------------------|---------------|--------|-------------|
| `1Z999AA1234567890` | UPS | en_tránsito | Paquete de NY a LA |
| `FDX123456789012` | FedEx | entregado | Documentos de Chicago a Miami |

#### Códigos de Estado Esperados

- **200**: Seguimiento encontrado exitosamente
- **404**: Número de seguimiento no encontrado
- **400**: Formato de número de seguimiento inválido (si se implementa validación)

### 🐛 Solución de Problemas

#### Problemas Comunes

1. **Puerto Ya en Uso**
   ```bash
   # Verificar qué está usando el puerto
   lsof -i :3000
   # Eliminar el proceso
   kill -9 <PID>
   ```

2. **Problemas con Entorno Virtual de Python**
   ```bash
   # Recrear entorno virtual
   rm -rf .venv
   python -m venv .venv
   source .venv/bin/activate
   pip install -r requirements.txt
   ```

3. **Problemas con Módulos de Go**
   ```bash
   # Limpiar caché de módulos
   go clean -modcache
   go mod download
   ```

4. **Problemas de Compilación de Rust**
   ```bash
   # Limpiar y reconstruir
   cargo clean
   cargo build
   ```

5. **Problemas con Docker**
   ```bash
   # Remover contenedor y reconstruir
   docker stop tracking-php
   docker rm tracking-php
   docker build -t tracking-api-php .
   ```

### 📱 Pruebas con Postman

#### Importar Colección
Crea una colección de Postman con estas peticiones:

**Colección: API de Seguimiento de Paquetes**

1. **Salud Node.js**
   - Método: GET
   - URL: `http://localhost:3000/health`

2. **Seguimiento Node.js**
   - Método: GET
   - URL: `http://localhost:3000/api/v1/tracking/1Z999AA1234567890`

[... resto de endpoints similar a la versión en inglés]

### 📊 Pruebas de Rendimiento

#### Prueba de Carga Simple con curl
```bash
# Probar 100 peticiones a cada endpoint
for i in {1..100}; do
  curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" > /dev/null
done
```

#### Comparación de Tiempos de Respuesta
```bash
# Medir tiempos de respuesta
time curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Node.js
time curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/" > /dev/null # Python
time curl -s "http://localhost:8080/api/v1/tracking/1Z999AA1234567890" > /dev/null  # PHP
time curl -s "http://localhost:8081/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Go
time curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Rust
```
