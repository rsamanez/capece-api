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
curl -s "http://localhost:8083/health" | jq .

# Valid Tracking Number - UPS
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" | jq .

# Valid Tracking Number - FedEx
curl -s "http://localhost:8083/api/v1/tracking/FDX123456789012" | jq .

# Invalid Tracking Number
curl -s "http://localhost:8083/api/v1/tracking/INVALID123" | jq .
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

## 📎 Evidence Upload Testing

### Overview

Test the evidence upload functionality across all implementations. This section provides comprehensive testing for file upload, retrieval, and deletion.

### Prerequisites for Evidence Testing

- Running server (any implementation)
- Test image file (PNG, JPG, JPEG, or PDF)
- `curl` command line tool

### Creating Test Files

Create test files for upload testing:

```bash
# Create a small test PNG image
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==" | base64 -d > test-image.png

# Create a test text file (for PDF testing if needed)
echo "Test delivery evidence document" > test-document.txt

# Verify file was created
ls -la test-image.png
file test-image.png
```

### Step-by-Step Evidence Testing

#### Step 1: Test Evidence Retrieval (Empty State)

Test that evidence endpoint returns empty results initially:

```bash
# For Node.js (Port 3000)
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# For Python Django (Port 8000) - Note the trailing slash
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/" | jq .

# For Go (Port 8083)
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# For Rust (Port 8082)
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Expected Response:
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 0,
  "evidence": []
}
```

#### Step 2: Upload Evidence

Upload a test image with description:

```bash
# For Node.js (Port 3000)
curl -X POST \
  "http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@test-image.png" \
  -F "description=Test delivery evidence - package at front door" \
  | jq .

# For Python Django (Port 8000) - Note the trailing slash
curl -X POST \
  "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/" \
  -F "image=@test-image.png" \
  -F "description=Test delivery evidence - package at front door" \
  | jq .

# For Go (Port 8083)
curl -X POST \
  "http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@test-image.png" \
  -F "description=Test delivery evidence - package at front door" \
  | jq .

# For Rust (Port 8082)
curl -X POST \
  "http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@test-image.png" \
  -F "description=Test delivery evidence - package at front door" \
  | jq .

# Expected Response:
{
  "success": true,
  "message": "Delivery evidence uploaded successfully",
  "trackingNumber": "1Z999AA1234567890",
  "evidence": {
    "id": "uuid-generated-id",
    "filename": "uuid-filename.png",
    "originalName": "test-image.png",
    "size": 70,
    "mimeType": "image/png",
    "uploadedAt": "2025-08-27T05:46:06.169Z",
    "description": "Test delivery evidence - package at front door",
    "url": "/uploads/evidence/1Z999AA1234567890/uuid-filename.png"
  }
}
```

#### Step 3: Verify Evidence was Stored

Retrieve the evidence list to confirm upload:

```bash
# Use same GET commands as Step 1
curl -s "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Expected Response (now with evidence):
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 1,
  "evidence": [
    {
      "id": "uuid-generated-id",
      "filename": "uuid-filename.png",
      "originalName": "test-image.png",
      "size": 70,
      "mimeType": "image/png",
      "uploadedAt": "2025-08-27T05:46:06.169Z",
      "description": "Test delivery evidence - package at front door",
      "url": "/uploads/evidence/1Z999AA1234567890/uuid-filename.png"
    }
  ]
}
```

#### Step 4: Test File Access

Verify uploaded files are accessible via URL:

```bash
# Test file access via static URL
curl -I "http://localhost:{port}/uploads/evidence/1Z999AA1234567890/uuid-filename.png"

# Expected Response Headers:
HTTP/1.1 200 OK
Content-Type: image/png
Content-Length: 70
```

#### Step 5: Test Error Scenarios

Test various error conditions:

```bash
# Test missing file
curl -X POST \
  "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "description=Test without file" \
  | jq .

# Expected: 400 Bad Request with error message

# Test invalid file type (create a text file with .exe extension)
echo "invalid file" > invalid.exe
curl -X POST \
  "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@invalid.exe" \
  -F "description=Invalid file type test" \
  | jq .

# Expected: 400 Bad Request with file type error
```

#### Step 6: Test Evidence Deletion (Optional)

Test deleting uploaded evidence:

```bash
# Get evidence ID from previous responses, then delete
curl -X DELETE \
  "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence/uuid-generated-id" \
  | jq .

# Expected Response:
{
  "success": true,
  "message": "Evidence deleted successfully"
}

# Verify deletion
curl -s "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Should show evidenceCount: 0 again
```

### Evidence Testing Checklist

Use this checklist to verify evidence functionality:

- [ ] **Empty State**: GET request returns empty evidence list
- [ ] **Upload Success**: POST with valid image uploads successfully
- [ ] **Upload Validation**: POST without file returns error
- [ ] **File Type Validation**: Invalid file types are rejected
- [ ] **File Access**: Uploaded files are accessible via URL
- [ ] **Evidence Retrieval**: GET request shows uploaded evidence
- [ ] **Evidence Deletion**: DELETE request removes evidence (✅ Disponible en todas las implementaciones)
- [ ] **UUID Generation**: Each upload gets unique filename
- [ ] **Metadata Storage**: Original filename, size, and description are stored

> ✅ **Actualizado**: Todas las implementaciones (Node.js, Python, PHP, Go, Rust) ahora incluyen funcionalidad completa de DELETE para evidencia.

### Multiple File Upload Testing

Test uploading multiple evidence files:

```bash
# Upload first evidence
curl -X POST "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@test-image.png" \
  -F "description=Front door delivery photo"

# Upload second evidence  
curl -X POST "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@test-image.png" \
  -F "description=Package close-up photo"

# Verify both are stored
curl -s "http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Should show evidenceCount: 2 with both files
```

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
   - URL: `http://localhost:8083/health`

8. **Go Tracking**
   - Method: GET
   - URL: `http://localhost:8083/api/v1/tracking/1Z999AA1234567890`

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
time curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Go
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
curl -s "http://localhost:8083/health" | jq .

# Número de Seguimiento Válido - UPS
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" | jq .

# Número de Seguimiento Válido - FedEx
curl -s "http://localhost:8083/api/v1/tracking/FDX123456789012" | jq .

# Número de Seguimiento Inválido
curl -s "http://localhost:8083/api/v1/tracking/INVALID123" | jq .
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

## 📎 Pruebas de Subida de Evidencia

### Resumen

Prueba la funcionalidad de subida de evidencia en todas las implementaciones. Esta sección proporciona pruebas exhaustivas para subida, recuperación y eliminación de archivos.

### Prerrequisitos para Pruebas de Evidencia

- Servidor ejecutándose (cualquier implementación)
- Archivo de imagen de prueba (PNG, JPG, JPEG, o PDF)
- Herramienta de línea de comandos `curl`

### Creando Archivos de Prueba

Crear archivos de prueba para testing de subidas:

```bash
# Crear una imagen PNG de prueba pequeña
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==" | base64 -d > imagen-prueba.png

# Crear un archivo de texto de prueba (para testing PDF si es necesario)
echo "Documento de evidencia de entrega de prueba" > documento-prueba.txt

# Verificar que el archivo fue creado
ls -la imagen-prueba.png
file imagen-prueba.png
```

### Pruebas de Evidencia Paso a Paso

#### Paso 1: Probar Recuperación de Evidencia (Estado Vacío)

Probar que el endpoint de evidencia retorna resultados vacíos inicialmente:

```bash
# Para Node.js (Puerto 3000)
curl -s "http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Para Python Django (Puerto 8000) - Nota la barra final
curl -s "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/" | jq .

# Para Go (Puerto 8083)
curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Para Rust (Puerto 8082)
curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Respuesta Esperada:
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 0,
  "evidence": []
}
```

#### Paso 2: Subir Evidencia

Subir una imagen de prueba con descripción:

```bash
# Para Node.js (Puerto 3000)
curl -X POST \
  "http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@imagen-prueba.png" \
  -F "description=Evidencia de entrega de prueba - paquete en puerta principal" \
  | jq .

# Para Python Django (Puerto 8000) - Nota la barra final
curl -X POST \
  "http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/" \
  -F "image=@imagen-prueba.png" \
  -F "description=Evidencia de entrega de prueba - paquete en puerta principal" \
  | jq .

# Para Go (Puerto 8083)
curl -X POST \
  "http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@imagen-prueba.png" \
  -F "description=Evidencia de entrega de prueba - paquete en puerta principal" \
  | jq .

# Para Rust (Puerto 8082)
curl -X POST \
  "http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@imagen-prueba.png" \
  -F "description=Evidencia de entrega de prueba - paquete en puerta principal" \
  | jq .

# Respuesta Esperada:
{
  "success": true,
  "message": "Delivery evidence uploaded successfully",
  "trackingNumber": "1Z999AA1234567890",
  "evidence": {
    "id": "uuid-generado",
    "filename": "uuid-nombre-archivo.png",
    "originalName": "imagen-prueba.png",
    "size": 70,
    "mimeType": "image/png",
    "uploadedAt": "2025-08-27T05:46:06.169Z",
    "description": "Evidencia de entrega de prueba - paquete en puerta principal",
    "url": "/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png"
  }
}
```

#### Paso 3: Verificar que la Evidencia fue Almacenada

Recuperar la lista de evidencia para confirmar la subida:

```bash
# Usar los mismos comandos GET del Paso 1
curl -s "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Respuesta Esperada (ahora con evidencia):
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 1,
  "evidence": [
    {
      "id": "uuid-generado",
      "filename": "uuid-nombre-archivo.png",
      "originalName": "imagen-prueba.png",
      "size": 70,
      "mimeType": "image/png",
      "uploadedAt": "2025-08-27T05:46:06.169Z",
      "description": "Evidencia de entrega de prueba - paquete en puerta principal",
      "url": "/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png"
    }
  ]
}
```

#### Paso 4: Probar Acceso a Archivos

Verificar que los archivos subidos son accesibles vía URL:

```bash
# Probar acceso a archivo vía URL estática
curl -I "http://localhost:{puerto}/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png"

# Headers de Respuesta Esperados:
HTTP/1.1 200 OK
Content-Type: image/png
Content-Length: 70
```

#### Paso 5: Probar Escenarios de Error

Probar varias condiciones de error:

```bash
# Probar archivo faltante
curl -X POST \
  "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "description=Prueba sin archivo" \
  | jq .

# Esperado: 400 Bad Request con mensaje de error

# Probar tipo de archivo inválido (crear archivo de texto con extensión .exe)
echo "archivo inválido" > invalido.exe
curl -X POST \
  "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@invalido.exe" \
  -F "description=Prueba de tipo de archivo inválido" \
  | jq .

# Esperado: 400 Bad Request con error de tipo de archivo
```

#### Paso 6: Probar Eliminación de Evidencia (Opcional)

Probar eliminar evidencia subida:

```bash
# Obtener ID de evidencia de respuestas anteriores, luego eliminar
curl -X DELETE \
  "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence/uuid-generado" \
  | jq .

# Respuesta Esperada:
{
  "success": true,
  "message": "Evidence deleted successfully"
}

# Verificar eliminación
curl -s "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Debería mostrar evidenceCount: 0 nuevamente
```

### Lista de Verificación de Pruebas de Evidencia

Usar esta lista para verificar funcionalidad de evidencia:

- [ ] **Estado Vacío**: Solicitud GET retorna lista de evidencia vacía
- [ ] **Subida Exitosa**: POST con imagen válida sube exitosamente
- [ ] **Validación de Subida**: POST sin archivo retorna error
- [ ] **Validación de Tipo de Archivo**: Tipos de archivo inválidos son rechazados
- [ ] **Acceso a Archivos**: Archivos subidos son accesibles vía URL
- [ ] **Recuperación de Evidencia**: Solicitud GET muestra evidencia subida
- [ ] **Eliminación de Evidencia**: Solicitud DELETE remueve evidencia
- [ ] **Generación de UUID**: Cada subida obtiene nombre de archivo único
- [ ] **Almacenamiento de Metadatos**: Nombre original, tamaño y descripción son almacenados

### Pruebas de Subida de Múltiples Archivos

Probar subir múltiples archivos de evidencia:

```bash
# Subir primera evidencia
curl -X POST "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@imagen-prueba.png" \
  -F "description=Foto de entrega en puerta principal"

# Subir segunda evidencia  
curl -X POST "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" \
  -F "image=@imagen-prueba.png" \
  -F "description=Foto de primer plano del paquete"

# Verificar que ambas están almacenadas
curl -s "http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence" | jq .

# Debería mostrar evidenceCount: 2 con ambos archivos
```

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
time curl -s "http://localhost:8083/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Go
time curl -s "http://localhost:8082/api/v1/tracking/1Z999AA1234567890" > /dev/null  # Rust
```
