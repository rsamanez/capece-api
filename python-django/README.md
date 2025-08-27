# Python Django Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Testing

> 📖 **For complete testing guide**: See [../TESTING_GUIDE.md](../TESTING_GUIDE.md) for step-by-step instructions to test all implementations.

### Quick Test
```bash
# Health check
curl http://localhost:8000/health/

# Tracking endpoint (note trailing slash)
curl http://localhost:8000/api/v1/tracking/1Z999AA1234567890/
```

### Available Test Data
- `1Z999AA1234567890` - UPS package in transit
- `FDX123456789012` - FedEx package delivered

**Important**: Django URLs require trailing slashes.etup

1. Create virtual environment:
```bash
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Run migrations:
```bash
python manage.py migrate
```

4. Start the server:
```bash
python manage.py runserver 8000
```

## API Endpoint

```
GET http://localhost:8000/api/v1/tracking/{trackingNumber}
```

## Testing

```bash
curl http://localhost:8000/api/v1/tracking/1Z999AA1234567890
```

## Dependencies

- Django: Web framework
- djangorestframework: REST API framework
- django-cors-headers: CORS handling
