# Implementación de Upload de Evidencia - Node.js

## ✅ Completado exitosamente

### Funcionalidad implementada:
1. **POST** `/api/v1/tracking/{trackingNumber}/evidence` - Subir evidencia de entrega
2. **GET** `/api/v1/tracking/{trackingNumber}/evidence` - Obtener todas las evidencias
3. **DELETE** `/api/v1/tracking/{trackingNumber}/evidence/{evidenceId}` - Eliminar evidencia específica

### Características técnicas:
- ✅ Upload de archivos con Multer
- ✅ Validación de tipos de archivo (JPEG, PNG, GIF, WebP)
- ✅ Límite de tamaño de archivo (5MB)
- ✅ Almacenamiento organizado por tracking number
- ✅ Generación de UUID para nombres únicos
- ✅ Servicio de archivos estáticos habilitado
- ✅ Metadatos personalizables (descripción, ubicación)
- ✅ Validación de tracking numbers existentes
- ✅ Manejo completo de errores

### Archivos creados/modificados:
1. `src/services/evidenceService.js` - Servicio completo de gestión de evidencia
2. `src/routes/evidence.js` - Endpoints REST para evidencia
3. `src/server.js` - Configuración de rutas y archivos estáticos
4. `package.json` - Dependencias agregadas (multer, uuid)

### Estructura de directorios:
```
uploads/
  evidence/
    {trackingNumber}/
      {evidenceId}.{extension}
```

### Ejemplo de uso exitoso:
```bash
# Subir evidencia
curl -X POST \
  'http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence' \
  -H 'Content-Type: multipart/form-data' \
  -F 'image=@imagen.png' \
  -F 'description=Paquete entregado en puerta principal' \
  -F 'location=Puerta principal, 123 Main St'

# Obtener evidencias
curl -X GET 'http://localhost:3000/api/v1/tracking/1Z999AA1234567890/evidence'

# Acceso directo al archivo
http://localhost:3000/uploads/evidence/1Z999AA1234567890/{evidenceId}.png
```

### Estado actual:
🟢 **FUNCIONANDO COMPLETAMENTE** - Todos los endpoints probados y validados
