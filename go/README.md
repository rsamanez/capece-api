# Go Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Setup

1. Initialize Go module:
```bash
go mod init tracking-api
```

2. Install dependencies:
```bash
go mod tidy
```

3. Run the server:
```bash
go run main.go
```

## API Endpoint

```
GET http://localhost:8081/api/v1/tracking/{trackingNumber}
```

## Testing

```bash
curl http://localhost:8081/api/v1/tracking/1Z999AA1234567890
```

## Dependencies

- Gin: Web framework
- Built with Go's standard library for JSON handling
