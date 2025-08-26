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
| Go/Gin | 8081 | `GET /health` | `GET /api/v1/tracking/{id}` |
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
    
    url := fmt.Sprintf("http://localhost:8081/api/v1/tracking/%s", trackingNumber)
    
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
