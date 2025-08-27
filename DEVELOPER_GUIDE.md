# Developer Guide / Guía del Desarrollador

[English](#english-developer-guide) | [Español](#guía-del-desarrollador-en-español)

---

## English Developer Guide

### Getting Started

This guide will help you integrate the Package Tracking API into your applications.

> 🧪 **Testing the API**: For step-by-step testing instructions for all 5 implementations, see [TESTING_GUIDE.md](TESTING_GUIDE.md)

### API Overview

The Package Tracking API provides a simple REST endpoint to retrieve package tracking information. It follows standard HTTP conventions and returns JSON responses.

**Base URL**: `http://localhost:{port}/api/v1`

**Authentication**: None required for this demo (implement API keys in production)

### Available Implementations & Ports

| Implementation | Port | Health Check | Tracking Endpoint |
|---------------|------|--------------|-------------------|
| Node.js/Express | 3000 | `GET /health` | `GET /api/v1/tracking/{id}` |
| Python/Django | 8000 | `GET /health/` | `GET /api/v1/tracking/{id}/` |
| PHP | 8080 | `GET /health` | `GET /api/v1/tracking/{id}` |
| Go/Gin | 8083 | `GET /health` | `GET /api/v1/tracking/{id}` |
| Rust/Actix-web | 8082 | `GET /health` | `GET /api/v1/tracking/{id}` |

> 📝 **Note**: Django requires trailing slashes in URLs

### Integration Examples

#### JavaScript/Node.js

```javascript
async function getTrackingInfo(trackingNumber) {
  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${trackingNumber}`);
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    const trackingData = await response.json();
    return trackingData;
  } catch (error) {
    console.error('Error fetching tracking info:', error);
    throw error;
  }
}

// Usage
getTrackingInfo('1Z999AA1234567890')
  .then(data => console.log(data))
  .catch(error => console.error(error));
```

#### Python

```python
import requests
import json

def get_tracking_info(tracking_number):
    """Get tracking information for a package"""
    url = f"http://localhost:8000/api/v1/tracking/{tracking_number}"
    
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching tracking info: {e}")
        raise

# Usage
try:
    tracking_data = get_tracking_info('1Z999AA1234567890')
    print(json.dumps(tracking_data, indent=2))
except Exception as e:
    print(f"Error: {e}")
```

#### PHP

```php
<?php

function getTrackingInfo($trackingNumber) {
    $url = "http://localhost:8080/api/v1/tracking/" . urlencode($trackingNumber);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept: application/json',
            'timeout' => 30
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to fetch tracking information');
    }
    
    $httpCode = null;
    if (isset($http_response_header[0])) {
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
        $httpCode = (int)$matches[1];
    }
    
    if ($httpCode >= 400) {
        $error = json_decode($response, true);
        throw new Exception($error['message'] ?? 'HTTP Error');
    }
    
    return json_decode($response, true);
}

// Usage
try {
    $trackingData = getTrackingInfo('1Z999AA1234567890');
    echo json_encode($trackingData, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

#### Go

```go
package main

import (
    "encoding/json"
    "fmt"
    "io"
    "net/http"
    "time"
)

type TrackingInfo struct {
    TrackingNumber string `json:"trackingNumber"`
    Status         string `json:"status"`
    Carrier        string `json:"carrier"`
    // Add other fields as needed
}

func getTrackingInfo(trackingNumber string) (*TrackingInfo, error) {
    client := &http.Client{Timeout: 30 * time.Second}
    
    url := fmt.Sprintf("http://localhost:8083/api/v1/tracking/%s", trackingNumber)
    
    resp, err := client.Get(url)
    if err != nil {
        return nil, fmt.Errorf("failed to make request: %w", err)
    }
    defer resp.Body.Close()
    
    body, err := io.ReadAll(resp.Body)
    if err != nil {
        return nil, fmt.Errorf("failed to read response: %w", err)
    }
    
    if resp.StatusCode != http.StatusOK {
        return nil, fmt.Errorf("API returned status %d: %s", resp.StatusCode, string(body))
    }
    
    var trackingInfo TrackingInfo
    if err := json.Unmarshal(body, &trackingInfo); err != nil {
        return nil, fmt.Errorf("failed to parse response: %w", err)
    }
    
    return &trackingInfo, nil
}

func main() {
    trackingInfo, err := getTrackingInfo("1Z999AA1234567890")
    if err != nil {
        fmt.Printf("Error: %v\n", err)
        return
    }
    
    fmt.Printf("Tracking Info: %+v\n", trackingInfo)
}
```

#### Rust

```rust
use serde::{Deserialize, Serialize};
use std::error::Error;

#[derive(Deserialize, Serialize, Debug)]
struct TrackingInfo {
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
    status: String,
    carrier: String,
    // Add other fields as needed
}

async fn get_tracking_info(tracking_number: &str) -> Result<TrackingInfo, Box<dyn Error>> {
    let client = reqwest::Client::new();
    let url = format!("http://localhost:8082/api/v1/tracking/{}", tracking_number);
    
    let response = client.get(&url).send().await?;
    
    if !response.status().is_success() {
        return Err(format!("API returned status: {}", response.status()).into());
    }
    
    let tracking_info: TrackingInfo = response.json().await?;
    Ok(tracking_info)
}

#[tokio::main]
async fn main() -> Result<(), Box<dyn Error>> {
    match get_tracking_info("1Z999AA1234567890").await {
        Ok(tracking_info) => println!("{:#?}", tracking_info),
        Err(e) => eprintln!("Error: {}", e),
    }
    
    Ok(())
}
```

## Evidence Upload Functionality

### Overview

The Evidence Upload API allows you to attach delivery evidence (photos, documents) to package tracking records. This feature is available across all 5 implementations.

### Evidence Endpoints

| Implementation | Port | Upload Evidence | Get Evidence | Delete Evidence |
|---------------|------|----------------|--------------|----------------|
| Node.js/Express | 3000 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Python/Django | 8000 | `POST /api/v1/tracking/{id}/evidence/` | `GET /api/v1/tracking/{id}/evidence/` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}/` |
| PHP/Laravel | 8080 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Go/Gin | 8083 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Rust/Actix-web | 8082 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |

### File Upload Requirements

- **Supported formats**: PNG, JPG, JPEG, PDF
- **Max file size**: 10MB
- **Form field name**: `image` (for file)
- **Description field**: `description` (optional text)
- **Content-Type**: `multipart/form-data`

### Step-by-Step Testing Guide

#### Step 1: Start a Server

Choose one implementation to test:

```bash
# Node.js (Port 3000)
cd nodejs-express && npm start

# Python Django (Port 8000) 
cd python-django && python manage.py runserver 8000

# Go (Port 8083)
cd go && go run main.go

# Rust (Port 8082)
cd rust && cargo run
```

#### Step 2: Test Evidence Retrieval (Empty State)

```bash
# Replace {port} with your chosen port (3000, 8000, 8083, 8082)
curl -X GET 'http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence'

# Expected response:
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 0,
  "evidence": []
}
```

#### Step 3: Upload Evidence

Create a test image or use an existing one:

```bash
# Create a simple test image (if needed)
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==" | base64 -d > test-image.png

# Upload evidence with description
curl -X POST \
  'http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@test-image.png' \
  -F 'description=Delivery proof - package left at front door'

# Expected response:
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
    "description": "Delivery proof - package left at front door",
    "url": "/uploads/evidence/1Z999AA1234567890/uuid-filename.png"
  }
}
```

#### Step 4: Verify Evidence was Stored

```bash
# Get evidence list
curl -X GET 'http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence'

# Expected response:
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
      "description": "Delivery proof - package left at front door",
      "url": "/uploads/evidence/1Z999AA1234567890/uuid-filename.png"
    }
  ]
}
```

#### Step 5: Access Uploaded File

```bash
# Direct file access via static URL
curl -I 'http://localhost:{port}/uploads/evidence/1Z999AA1234567890/uuid-filename.png'

# Should return 200 OK with image headers
```

#### Step 6: Delete Evidence (Optional)

```bash
# Delete specific evidence by ID
curl -X DELETE 'http://localhost:{port}/api/v1/tracking/1Z999AA1234567890/evidence/uuid-generated-id'

# Expected response:
{
  "success": true,
  "message": "Evidence deleted successfully"
}
```

### Integration Examples for Evidence Upload

#### JavaScript/Node.js

```javascript
async function uploadEvidence(trackingNumber, file, description) {
  const formData = new FormData();
  formData.append('image', file);
  formData.append('description', description);

  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${trackingNumber}/evidence`, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }

    return await response.json();
  } catch (error) {
    console.error('Error uploading evidence:', error);
    throw error;
  }
}

async function getEvidence(trackingNumber) {
  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${trackingNumber}/evidence`);
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error fetching evidence:', error);
    throw error;
  }
}

// Usage example
const fileInput = document.getElementById('evidenceFile');
const file = fileInput.files[0];
const description = 'Package delivered to front door';

uploadEvidence('1Z999AA1234567890', file, description)
  .then(result => console.log('Upload successful:', result))
  .catch(error => console.error('Upload failed:', error));
```

#### Python

```python
import requests

def upload_evidence(tracking_number, file_path, description):
    """Upload evidence file for a package"""
    url = f"http://localhost:8000/api/v1/tracking/{tracking_number}/evidence/"
    
    with open(file_path, 'rb') as file:
        files = {'image': file}
        data = {'description': description}
        
        try:
            response = requests.post(url, files=files, data=data)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            print(f"Error uploading evidence: {e}")
            raise

def get_evidence(tracking_number):
    """Get all evidence for a package"""
    url = f"http://localhost:8000/api/v1/tracking/{tracking_number}/evidence/"
    
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching evidence: {e}")
        raise

# Usage example
try:
    # Upload evidence
    result = upload_evidence('1Z999AA1234567890', 'delivery-photo.jpg', 'Package left at door')
    print(f"Upload successful: {result}")
    
    # Get evidence list
    evidence_list = get_evidence('1Z999AA1234567890')
    print(f"Evidence count: {evidence_list['evidenceCount']}")
    
except Exception as e:
    print(f"Error: {e}")
```

#### cURL Examples for Different Implementations

```bash
# Node.js (Port 3000)
curl -X POST 'http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@photo.jpg' \
  -F 'description=Delivered to recipient'

# Python Django (Port 8000) - Note the trailing slash
curl -X POST 'http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/' \
  -F 'image=@photo.jpg' \
  -F 'description=Delivered to recipient'

# Go (Port 8083)
curl -X POST 'http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@photo.jpg' \
  -F 'description=Delivered to recipient'

# Rust (Port 8082)
curl -X POST 'http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@photo.jpg' \
  -F 'description=Delivered to recipient'
```

### Evidence API Error Responses

#### Missing File (400)
```json
{
  "error": "missing_file",
  "field": "image",
  "message": "No image file provided"
}
```

#### Invalid File Type (400)
```json
{
  "error": "invalid_file_type",
  "message": "Only PNG, JPG, JPEG, and PDF files are allowed",
  "allowedTypes": ["image/png", "image/jpeg", "application/pdf"]
}
```

#### File Too Large (400)
```json
{
  "error": "file_too_large",
  "message": "File size exceeds maximum limit of 10MB",
  "maxSize": "10MB"
}
```

#### Evidence Not Found (404)
```json
{
  "error": "evidence_not_found",
  "message": "Evidence with specified ID not found"
}
```

### Status Codes and Error Handling

#### Success Response (200)
```json
{
  "trackingNumber": "1Z999AA1234567890",
  "status": "in_transit",
  "carrier": "UPS",
  // ... other fields
}
```

#### Bad Request (400) - Invalid Tracking Number
```json
{
  "error": "invalid_tracking_number",
  "message": "Invalid tracking number format",
  "trackingNumber": "invalid123"
}
```

#### Not Found (404) - Tracking Number Not Found
```json
{
  "error": "tracking_not_found",
  "message": "Tracking number not found",
  "trackingNumber": "UNKNOWN123456"
}
```

### Data Model

#### TrackingInfo Object

| Field | Type | Description |
|-------|------|-------------|
| `trackingNumber` | string | The tracking number |
| `status` | string | Current package status |
| `estimatedDelivery` | string (ISO 8601) | Estimated delivery date |
| `actualDelivery` | string (ISO 8601) | Actual delivery date (if delivered) |
| `carrier` | string | Shipping carrier name |
| `service` | string | Shipping service type |
| `origin` | Address | Origin address |
| `destination` | Address | Destination address |
| `package` | Package | Package details |
| `events` | TrackingEvent[] | Array of tracking events |

### Best Practices

1. **Error Handling**: Always implement proper error handling for network requests
2. **Caching**: Cache responses appropriately to reduce API calls
3. **Rate Limiting**: Implement client-side rate limiting to avoid overwhelming the API
4. **Validation**: Validate tracking numbers before making API calls
5. **Timeouts**: Set appropriate timeouts for HTTP requests
6. **Retry Logic**: Implement exponential backoff for retries on transient failures

### Testing Your Integration

Use the provided test tracking numbers:

- `1Z999AA1234567890` - UPS package in transit
- `FDX123456789012` - FedEx package delivered
- `DHL9876543210` - DHL package with exception

### Production Considerations

- Implement API authentication
- Use HTTPS endpoints
- Monitor API usage and performance
- Set up proper logging and error tracking
- Consider implementing webhooks for real-time updates

---

## Guía del Desarrollador en Español

### Comenzando

Esta guía te ayudará a integrar la API de Seguimiento de Paquetes en tus aplicaciones.

### Resumen de la API

La API de Seguimiento de Paquetes proporciona un endpoint REST simple para recuperar información de seguimiento de paquetes. Sigue convenciones HTTP estándar y devuelve respuestas JSON.

**URL Base**: `http://localhost:{puerto}/api/v1`

**Autenticación**: No requerida para esta demo (implementar API keys en producción)

### Ejemplos de Integración

#### JavaScript/Node.js

```javascript
async function obtenerInfoSeguimiento(numeroSeguimiento) {
  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${numeroSeguimiento}`);
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    const datosSeguimiento = await response.json();
    return datosSeguimiento;
  } catch (error) {
    console.error('Error obteniendo info de seguimiento:', error);
    throw error;
  }
}

// Uso
obtenerInfoSeguimiento('1Z999AA1234567890')
  .then(data => console.log(data))
  .catch(error => console.error(error));
```

## Funcionalidad de Subida de Evidencia

### Resumen

La API de Subida de Evidencia permite adjuntar evidencia de entrega (fotos, documentos) a los registros de seguimiento de paquetes. Esta funcionalidad está disponible en las 5 implementaciones.

### Endpoints de Evidencia

| Implementación | Puerto | Subir Evidencia | Obtener Evidencia | Eliminar Evidencia |
|---------------|--------|----------------|-------------------|-------------------|
| Node.js/Express | 3000 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Python/Django | 8000 | `POST /api/v1/tracking/{id}/evidence/` | `GET /api/v1/tracking/{id}/evidence/` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}/` |
| PHP/Laravel | 8080 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Go/Gin | 8083 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |
| Rust/Actix-web | 8082 | `POST /api/v1/tracking/{id}/evidence` | `GET /api/v1/tracking/{id}/evidence` | `DELETE /api/v1/tracking/{id}/evidence/{evidenceId}` |

### Requisitos para Subida de Archivos

- **Formatos soportados**: PNG, JPG, JPEG, PDF
- **Tamaño máximo**: 10MB
- **Nombre del campo**: `image` (para el archivo)
- **Campo descripción**: `description` (texto opcional)
- **Content-Type**: `multipart/form-data`

### Guía de Pruebas Paso a Paso

#### Paso 1: Iniciar un Servidor

Elige una implementación para probar:

```bash
# Node.js (Puerto 3000)
cd nodejs-express && npm start

# Python Django (Puerto 8000) 
cd python-django && python manage.py runserver 8000

# Go (Puerto 8083)
cd go && go run main.go

# Rust (Puerto 8082)
cd rust && cargo run
```

#### Paso 2: Probar Recuperación de Evidencia (Estado Vacío)

```bash
# Reemplaza {puerto} con el puerto elegido (3000, 8000, 8083, 8082)
curl -X GET 'http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence'

# Respuesta esperada:
{
  "trackingNumber": "1Z999AA1234567890",
  "evidenceCount": 0,
  "evidence": []
}
```

#### Paso 3: Subir Evidencia

Crea una imagen de prueba o usa una existente:

```bash
# Crear una imagen de prueba simple (si es necesario)
echo "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==" | base64 -d > imagen-prueba.png

# Subir evidencia con descripción
curl -X POST \
  'http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@imagen-prueba.png' \
  -F 'description=Prueba de entrega - paquete dejado en puerta principal'

# Respuesta esperada:
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
    "description": "Prueba de entrega - paquete dejado en puerta principal",
    "url": "/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png"
  }
}
```

#### Paso 4: Verificar que la Evidencia fue Almacenada

```bash
# Obtener lista de evidencia
curl -X GET 'http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence'

# Respuesta esperada:
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
      "description": "Prueba de entrega - paquete dejado en puerta principal",
      "url": "/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png"
    }
  ]
}
```

#### Paso 5: Acceder al Archivo Subido

```bash
# Acceso directo al archivo vía URL estática
curl -I 'http://localhost:{puerto}/uploads/evidence/1Z999AA1234567890/uuid-nombre-archivo.png'

# Debe retornar 200 OK con headers de imagen
```

#### Paso 6: Eliminar Evidencia (Opcional)

```bash
# Eliminar evidencia específica por ID
curl -X DELETE 'http://localhost:{puerto}/api/v1/tracking/1Z999AA1234567890/evidence/uuid-generado'

# Respuesta esperada:
{
  "success": true,
  "message": "Evidence deleted successfully"
}
```

### Ejemplos de Integración para Subida de Evidencia

#### JavaScript/Node.js

```javascript
async function subirEvidencia(numeroSeguimiento, archivo, descripcion) {
  const formData = new FormData();
  formData.append('image', archivo);
  formData.append('description', descripcion);

  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${numeroSeguimiento}/evidence`, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }

    return await response.json();
  } catch (error) {
    console.error('Error subiendo evidencia:', error);
    throw error;
  }
}

async function obtenerEvidencia(numeroSeguimiento) {
  try {
    const response = await fetch(`http://localhost:3000/api/v1/tracking/${numeroSeguimiento}/evidence`);
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    return await response.json();
  } catch (error) {
    console.error('Error obteniendo evidencia:', error);
    throw error;
  }
}

// Ejemplo de uso
const inputArchivo = document.getElementById('archivoEvidencia');
const archivo = inputArchivo.files[0];
const descripcion = 'Paquete entregado en puerta principal';

subirEvidencia('1Z999AA1234567890', archivo, descripcion)
  .then(resultado => console.log('Subida exitosa:', resultado))
  .catch(error => console.error('Fallo en subida:', error));
```

#### Python

```python
import requests

def subir_evidencia(numero_seguimiento, ruta_archivo, descripcion):
    """Subir archivo de evidencia para un paquete"""
    url = f"http://localhost:8000/api/v1/tracking/{numero_seguimiento}/evidence/"
    
    with open(ruta_archivo, 'rb') as archivo:
        files = {'image': archivo}
        data = {'description': descripcion}
        
        try:
            response = requests.post(url, files=files, data=data)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            print(f"Error subiendo evidencia: {e}")
            raise

def obtener_evidencia(numero_seguimiento):
    """Obtener toda la evidencia para un paquete"""
    url = f"http://localhost:8000/api/v1/tracking/{numero_seguimiento}/evidence/"
    
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error obteniendo evidencia: {e}")
        raise

# Ejemplo de uso
try:
    # Subir evidencia
    resultado = subir_evidencia('1Z999AA1234567890', 'foto-entrega.jpg', 'Paquete dejado en puerta')
    print(f"Subida exitosa: {resultado}")
    
    # Obtener lista de evidencia
    lista_evidencia = obtener_evidencia('1Z999AA1234567890')
    print(f"Cantidad de evidencia: {lista_evidencia['evidenceCount']}")
    
except Exception as e:
    print(f"Error: {e}")
```

### Ejemplos cURL para Diferentes Implementaciones

```bash
# Node.js (Puerto 3000)
curl -X POST 'http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@foto.jpg' \
  -F 'description=Entregado al destinatario'

# Python Django (Puerto 8000) - Nota la barra final
curl -X POST 'http://localhost:8000/api/v1/tracking/1Z999AA1234567890/evidence/' \
  -F 'image=@foto.jpg' \
  -F 'description=Entregado al destinatario'

# Go (Puerto 8083)
curl -X POST 'http://localhost:8083/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@foto.jpg' \
  -F 'description=Entregado al destinatario'

# Rust (Puerto 8082)
curl -X POST 'http://localhost:8082/api/v1/tracking/1Z999AA1234567890/evidence' \
  -F 'image=@foto.jpg' \
  -F 'description=Entregado al destinatario'
```

### Respuestas de Error de la API de Evidencia

#### Archivo Faltante (400)
```json
{
  "error": "missing_file",
  "field": "image",
  "message": "No image file provided"
}
```

#### Tipo de Archivo Inválido (400)
```json
{
  "error": "invalid_file_type",
  "message": "Only PNG, JPG, JPEG, and PDF files are allowed",
  "allowedTypes": ["image/png", "image/jpeg", "application/pdf"]
}
```

#### Archivo Muy Grande (400)
```json
{
  "error": "file_too_large",
  "message": "File size exceeds maximum limit of 10MB",
  "maxSize": "10MB"
}
```

#### Evidencia No Encontrada (404)
```json
{
  "error": "evidence_not_found",
  "message": "Evidence with specified ID not found"
}
```

### Códigos de Estado y Manejo de Errores

#### Respuesta Exitosa (200)
```json
{
  "trackingNumber": "1Z999AA1234567890",
  "status": "in_transit",
  "carrier": "UPS",
  // ... otros campos
}
```

#### Solicitud Incorrecta (400) - Número de Seguimiento Inválido
```json
{
  "error": "invalid_tracking_number",
  "message": "Formato de número de seguimiento inválido",
  "trackingNumber": "invalid123"
}
```

#### No Encontrado (404) - Número de Seguimiento No Encontrado
```json
{
  "error": "tracking_not_found",
  "message": "Número de seguimiento no encontrado",
  "trackingNumber": "UNKNOWN123456"
}
```

### Mejores Prácticas

1. **Manejo de Errores**: Siempre implementar manejo adecuado de errores para solicitudes de red
2. **Caché**: Cachear respuestas apropiadamente para reducir llamadas a la API
3. **Rate Limiting**: Implementar rate limiting del lado del cliente para evitar sobrecargar la API
4. **Validación**: Validar números de seguimiento antes de hacer llamadas a la API
5. **Timeouts**: Establecer timeouts apropiados para solicitudes HTTP
6. **Lógica de Reintentos**: Implementar backoff exponencial para reintentos en fallas transitorias

### Probando tu Integración

Usa los números de seguimiento de prueba proporcionados:

- `1Z999AA1234567890` - Paquete UPS en tránsito
- `FDX123456789012` - Paquete FedEx entregado
- `DHL9876543210` - Paquete DHL con excepción

### Consideraciones de Producción

- Implementar autenticación API
- Usar endpoints HTTPS
- Monitorear uso y rendimiento de la API
- Configurar logging y seguimiento de errores apropiados
- Considerar implementar webhooks para actualizaciones en tiempo real

### Diferencias entre Implementaciones

| Funcionalidad | Node.js | Python | PHP | Go | Rust |
|---------------|---------|--------|-----|-------|------|
| Tracking básico | ✅ | ✅ | ✅ | ✅ | ✅ |
| Health check | ✅ | ✅ | ✅ | ✅ | ✅ |
| Subida de evidencia | ✅ | ✅ | ✅ | ✅ | ✅ |
| Recuperar evidencia | ✅ | ✅ | ✅ | ✅ | ✅ |
| Eliminar evidencia | ✅ | ✅ | ✅ | ✅ | ✅ |
| Base de datos | En memoria | SQLite | JSON files | En memoria | En memoria |
| Validación de archivos | ✅ | ✅ | ✅ | ✅ | ✅ |
| Docker support | ✅ | ✅ | ✅ | ✅ | ✅ |

**Notas importantes:**
- Todas las implementaciones ahora incluyen funcionalidad completa de CRUD para evidencia
- Django requiere barras diagonales al final de las URLs (`/`)
- Todas las implementaciones soportan archivos hasta 10MB
- Los formatos soportados son: JPG, JPEG, PNG, PDF
