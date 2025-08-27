# Rust Implementation

**Created by:** Rommel Samanez Carrillo | **Donated by:** [BOSS.TECHNOLOGIES](https://boss.technologies)

## Setup

1. Build the project:
```bash
cargo build
```

2. Run the server:
```bash
cargo run
```

3. For development with auto-reload:
```bash
cargo install cargo-watch
cargo watch -x run
```

## API Endpoint

```
GET http://localhost:8082/api/v1/tracking/{trackingNumber}
```

## Testing

```bash
curl http://localhost:8082/api/v1/tracking/1Z999AA1234567890
```

## Dependencies

- actix-web: Web framework
- serde: Serialization/deserialization
- tokio: Async runtime
