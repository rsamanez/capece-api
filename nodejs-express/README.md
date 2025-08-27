# Node.js Express Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Setup

1. Install dependencies:
```bash
npm install
```

2. Start the server:
```bash
npm start
```

3. For development with auto-reload:
```bash
npm run dev
```

## API Endpoint

```
GET http://localhost:3000/api/v1/tracking/{trackingNumber}
```

## Testing

> 📖 **For complete testing guide**: See [../TESTING_GUIDE.md](../TESTING_GUIDE.md) for step-by-step instructions to test all implementations.

### Quick Test
```bash
# Health check
curl http://localhost:3000/health

# Tracking endpoint
curl http://localhost:3000/api/v1/tracking/1Z999AA1234567890
```

### Available Test Data
- `1Z999AA1234567890` - UPS package in transit
- `FDX123456789012` - FedEx package delivered

## Dependencies

- express: Web framework
- cors: CORS middleware
- helmet: Security middleware
- morgan: Request logging
- joi: Input validation
