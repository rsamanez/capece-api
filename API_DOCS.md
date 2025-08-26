# API Documentation / Documentación de la API

[English](#english-api-documentation) | [Español](#documentación-de-la-api-en-español)

---

## English API Documentation

### Overview

The Package Tracking API provides a standardized interface to track packages across different carriers. It follows REST principles and returns data in a format similar to major logistics providers.

### Base URL

```
http://localhost:{port}/api/v1
```

### Authentication

Currently, no authentication is required for this demo API. In production, consider implementing API keys or OAuth.

### Endpoints

#### Get Tracking Information

**GET** `/tracking/{trackingNumber}`

Retrieves tracking information for a specific package.

**Parameters:**
- `trackingNumber` (string, required): The tracking number of the package

**Response Format:**

```json
{
  "trackingNumber": "string",
  "status": "string",
  "estimatedDelivery": "ISO 8601 datetime",
  "actualDelivery": "ISO 8601 datetime",
  "carrier": "string",
  "service": "string",
  "origin": {
    "address": "string",
    "city": "string",
    "state": "string",
    "country": "string",
    "postalCode": "string"
  },
  "destination": {
    "address": "string",
    "city": "string",
    "state": "string",
    "country": "string",
    "postalCode": "string"
  },
  "package": {
    "weight": "number",
    "dimensions": {
      "length": "number",
      "width": "number",
      "height": "number"
    },
    "description": "string"
  },
  "events": [
    {
      "timestamp": "ISO 8601 datetime",
      "status": "string",
      "location": "string",
      "description": "string",
      "facilityType": "string"
    }
  ]
}
```

### Status Values

| Status | Description |
|--------|-------------|
| `label_created` | Shipping label created |
| `picked_up` | Package picked up by carrier |
| `in_transit` | Package is in transit |
| `out_for_delivery` | Package is out for delivery |
| `delivered` | Package has been delivered |
| `delivery_attempted` | Delivery was attempted |
| `exception` | An exception occurred |
| `returned` | Package is being returned |

### Error Responses

#### 404 - Tracking Number Not Found

```json
{
  "error": "tracking_not_found",
  "message": "Tracking number not found",
  "trackingNumber": "1Z999AA1234567890"
}
```

#### 400 - Invalid Tracking Number

```json
{
  "error": "invalid_tracking_number",
  "message": "Invalid tracking number format",
  "trackingNumber": "invalid123"
}
```

### Example Request

```bash
curl -X GET "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" \
     -H "Accept: application/json"
```

### Example Response

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
    },
    {
      "timestamp": "2025-08-27T08:30:00Z",
      "status": "in_transit",
      "location": "Philadelphia, PA",
      "description": "Departed from facility",
      "facilityType": "sort_facility"
    }
  ]
}
```

---

## Documentación de la API en Español

### Resumen

La API de Seguimiento de Paquetes proporciona una interfaz estandarizada para rastrear paquetes a través de diferentes transportistas. Sigue principios REST y devuelve datos en un formato similar a los principales proveedores logísticos.

### URL Base

```
http://localhost:{puerto}/api/v1
```

### Autenticación

Actualmente, no se requiere autenticación para esta API de demostración. En producción, considera implementar claves API u OAuth.

### Endpoints

#### Obtener Información de Seguimiento

**GET** `/tracking/{numeroSeguimiento}`

Recupera información de seguimiento para un paquete específico.

**Parámetros:**
- `numeroSeguimiento` (string, requerido): El número de seguimiento del paquete

**Formato de Respuesta:**

```json
{
  "trackingNumber": "string",
  "status": "string",
  "estimatedDelivery": "fecha ISO 8601",
  "actualDelivery": "fecha ISO 8601",
  "carrier": "string",
  "service": "string",
  "origin": {
    "address": "string",
    "city": "string",
    "state": "string",
    "country": "string",
    "postalCode": "string"
  },
  "destination": {
    "address": "string",
    "city": "string",
    "state": "string",
    "country": "string",
    "postalCode": "string"
  },
  "package": {
    "weight": "number",
    "dimensions": {
      "length": "number",
      "width": "number",
      "height": "number"
    },
    "description": "string"
  },
  "events": [
    {
      "timestamp": "fecha ISO 8601",
      "status": "string",
      "location": "string",
      "description": "string",
      "facilityType": "string"
    }
  ]
}
```

### Valores de Estado

| Estado | Descripción |
|--------|-------------|
| `label_created` | Etiqueta de envío creada |
| `picked_up` | Paquete recogido por el transportista |
| `in_transit` | Paquete en tránsito |
| `out_for_delivery` | Paquete en reparto |
| `delivered` | Paquete entregado |
| `delivery_attempted` | Se intentó la entrega |
| `exception` | Ocurrió una excepción |
| `returned` | Paquete siendo devuelto |

### Respuestas de Error

#### 404 - Número de Seguimiento No Encontrado

```json
{
  "error": "tracking_not_found",
  "message": "Número de seguimiento no encontrado",
  "trackingNumber": "1Z999AA1234567890"
}
```

#### 400 - Número de Seguimiento Inválido

```json
{
  "error": "invalid_tracking_number",
  "message": "Formato de número de seguimiento inválido",
  "trackingNumber": "invalid123"
}
```

### Ejemplo de Solicitud

```bash
curl -X GET "http://localhost:3000/api/v1/tracking/1Z999AA1234567890" \
     -H "Accept: application/json"
```

### Ejemplo de Respuesta

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
    },
    {
      "timestamp": "2025-08-27T08:30:00Z",
      "status": "in_transit",
      "location": "Philadelphia, PA",
      "description": "Departed from facility",
      "facilityType": "sort_facility"
    }
  ]
}
```
