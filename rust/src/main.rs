use actix_web::{web, App, HttpResponse, HttpServer, Result, middleware::Logger};
use serde::{Deserialize, Serialize};
use regex::Regex;
use std::collections::HashMap;

#[derive(Serialize, Deserialize, Clone)]
struct TrackingEvent {
    timestamp: String,
    status: String,
    location: String,
    description: String,
    #[serde(rename = "facilityType")]
    facility_type: String,
}

#[derive(Serialize, Deserialize, Clone)]
struct Address {
    #[serde(skip_serializing_if = "Option::is_none")]
    address: Option<String>,
    city: String,
    state: String,
    country: String,
    #[serde(rename = "postalCode")]
    postal_code: String,
}

#[derive(Serialize, Deserialize, Clone)]
struct Dimensions {
    length: f64,
    width: f64,
    height: f64,
}

#[derive(Serialize, Deserialize, Clone)]
struct Package {
    weight: f64,
    dimensions: Dimensions,
    description: String,
}

#[derive(Serialize, Deserialize, Clone)]
struct TrackingInfo {
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
    status: String,
    #[serde(rename = "estimatedDelivery")]
    estimated_delivery: String,
    #[serde(rename = "actualDelivery", skip_serializing_if = "Option::is_none")]
    actual_delivery: Option<String>,
    carrier: String,
    service: String,
    origin: Address,
    destination: Address,
    package: Package,
    events: Vec<TrackingEvent>,
}

#[derive(Serialize)]
struct ErrorResponse {
    error: String,
    message: String,
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
}

#[derive(Serialize)]
struct HealthResponse {
    status: String,
    timestamp: String,
}

// Mock tracking data
fn get_tracking_data() -> HashMap<String, TrackingInfo> {
    let mut data = HashMap::new();
    
    data.insert(
        "1Z999AA1234567890".to_string(),
        TrackingInfo {
            tracking_number: "1Z999AA1234567890".to_string(),
            status: "in_transit".to_string(),
            estimated_delivery: "2025-08-30T15:30:00Z".to_string(),
            actual_delivery: None,
            carrier: "UPS".to_string(),
            service: "Ground".to_string(),
            origin: Address {
                address: None,
                city: "New York".to_string(),
                state: "NY".to_string(),
                country: "USA".to_string(),
                postal_code: "10001".to_string(),
            },
            destination: Address {
                address: None,
                city: "Los Angeles".to_string(),
                state: "CA".to_string(),
                country: "USA".to_string(),
                postal_code: "90210".to_string(),
            },
            package: Package {
                weight: 2.5,
                dimensions: Dimensions {
                    length: 12.0,
                    width: 8.0,
                    height: 6.0,
                },
                description: "Electronics".to_string(),
            },
            events: vec![
                TrackingEvent {
                    timestamp: "2025-08-26T10:00:00Z".to_string(),
                    status: "picked_up".to_string(),
                    location: "New York, NY".to_string(),
                    description: "Package picked up".to_string(),
                    facility_type: "origin".to_string(),
                },
                TrackingEvent {
                    timestamp: "2025-08-27T08:30:00Z".to_string(),
                    status: "in_transit".to_string(),
                    location: "Philadelphia, PA".to_string(),
                    description: "Departed from facility".to_string(),
                    facility_type: "sort_facility".to_string(),
                },
            ],
        },
    );
    
    data.insert(
        "FDX123456789012".to_string(),
        TrackingInfo {
            tracking_number: "FDX123456789012".to_string(),
            status: "delivered".to_string(),
            estimated_delivery: "2025-08-28T14:00:00Z".to_string(),
            actual_delivery: Some("2025-08-28T13:45:00Z".to_string()),
            carrier: "FedEx".to_string(),
            service: "Express".to_string(),
            origin: Address {
                address: None,
                city: "Chicago".to_string(),
                state: "IL".to_string(),
                country: "USA".to_string(),
                postal_code: "60601".to_string(),
            },
            destination: Address {
                address: None,
                city: "Miami".to_string(),
                state: "FL".to_string(),
                country: "USA".to_string(),
                postal_code: "33101".to_string(),
            },
            package: Package {
                weight: 1.2,
                dimensions: Dimensions {
                    length: 10.0,
                    width: 6.0,
                    height: 4.0,
                },
                description: "Documents".to_string(),
            },
            events: vec![
                TrackingEvent {
                    timestamp: "2025-08-26T09:00:00Z".to_string(),
                    status: "picked_up".to_string(),
                    location: "Chicago, IL".to_string(),
                    description: "Package picked up".to_string(),
                    facility_type: "origin".to_string(),
                },
                TrackingEvent {
                    timestamp: "2025-08-27T12:00:00Z".to_string(),
                    status: "in_transit".to_string(),
                    location: "Memphis, TN".to_string(),
                    description: "In transit".to_string(),
                    facility_type: "sort_facility".to_string(),
                },
                TrackingEvent {
                    timestamp: "2025-08-28T08:00:00Z".to_string(),
                    status: "out_for_delivery".to_string(),
                    location: "Miami, FL".to_string(),
                    description: "Out for delivery".to_string(),
                    facility_type: "delivery".to_string(),
                },
                TrackingEvent {
                    timestamp: "2025-08-28T13:45:00Z".to_string(),
                    status: "delivered".to_string(),
                    location: "Miami, FL".to_string(),
                    description: "Delivered".to_string(),
                    facility_type: "delivery".to_string(),
                },
            ],
        },
    );
    
    data
}

// Validate tracking number format
fn is_valid_tracking_number(tracking_number: &str) -> bool {
    let re = Regex::new(r"^[A-Z0-9]{10,20}$").unwrap();
    re.is_match(tracking_number)
}

// Get tracking information handler
async fn get_tracking_info(path: web::Path<String>) -> Result<HttpResponse> {
    let tracking_number = path.into_inner();
    
    // Validate tracking number format
    if !is_valid_tracking_number(&tracking_number) {
        return Ok(HttpResponse::BadRequest().json(ErrorResponse {
            error: "invalid_tracking_number".to_string(),
            message: "Invalid tracking number format".to_string(),
            tracking_number,
        }));
    }
    
    // Get tracking data
    let tracking_data = get_tracking_data();
    
    match tracking_data.get(&tracking_number) {
        Some(tracking_info) => Ok(HttpResponse::Ok().json(tracking_info)),
        None => Ok(HttpResponse::NotFound().json(ErrorResponse {
            error: "tracking_not_found".to_string(),
            message: "Tracking number not found".to_string(),
            tracking_number,
        })),
    }
}

// Health check handler
async fn health_check() -> Result<HttpResponse> {
    Ok(HttpResponse::Ok().json(HealthResponse {
        status: "OK".to_string(),
        timestamp: chrono::Utc::now().to_rfc3339(),
    }))
}

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    env_logger::init();
    
    let port = 8082;
    println!("🚀 Rust server running on port {}", port);
    println!("📡 API endpoint: http://localhost:{}/api/v1/tracking/{{trackingNumber}}", port);
    
    HttpServer::new(|| {
        App::new()
            .wrap(Logger::default())
            .route("/health", web::get().to(health_check))
            .service(
                web::scope("/api/v1")
                    .route("/tracking/{tracking_number}", web::get().to(get_tracking_info))
            )
    })
    .bind(("127.0.0.1", port))?
    .run()
    .await
}
