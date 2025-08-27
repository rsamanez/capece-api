use actix_web::{web, App, HttpResponse, HttpServer, Result, middleware::Logger};
use actix_multipart::Multipart;
use actix_files as fs;
use serde::{Deserialize, Serialize};
use regex::Regex;
use std::collections::HashMap;
use std::sync::Mutex;
use std::io::Write;
use futures_util::TryStreamExt as _;
use uuid::Uuid;

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

#[derive(Serialize, Deserialize, Clone)]
struct Evidence {
    id: String,
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
    filename: String,
    #[serde(rename = "originalName")]
    original_name: String,
    size: u64,
    #[serde(rename = "mimeType")]
    mime_type: String,
    #[serde(rename = "uploadedAt")]
    uploaded_at: String,
    description: String,
    location: String,
    url: String,
    #[serde(skip_serializing)]
    file_path: String,
}

#[derive(Serialize)]
struct EvidenceResponse {
    success: bool,
    message: String,
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
    evidence: Evidence,
}

#[derive(Serialize)]
struct EvidenceListResponse {
    #[serde(rename = "trackingNumber")]
    tracking_number: String,
    #[serde(rename = "evidenceCount")]
    evidence_count: usize,
    evidence: Vec<Evidence>,
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

// Application state
struct AppState {
    tracking_data: HashMap<String, TrackingInfo>,
    evidence_store: Mutex<HashMap<String, Vec<Evidence>>>,
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
async fn get_tracking_info(
    path: web::Path<String>,
    data: web::Data<AppState>,
) -> Result<HttpResponse> {
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
    match data.tracking_data.get(&tracking_number) {
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

// Upload evidence handler
async fn upload_evidence(
    path: web::Path<String>,
    mut payload: Multipart,
    data: web::Data<AppState>,
) -> Result<HttpResponse> {
    let tracking_number = path.into_inner();

    // Validate tracking number format
    if !is_valid_tracking_number(&tracking_number) {
        return Ok(HttpResponse::BadRequest().json(ErrorResponse {
            error: "invalid_tracking_number".to_string(),
            message: "Invalid tracking number format".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    // Check if tracking number exists
    if !data.tracking_data.contains_key(&tracking_number) {
        return Ok(HttpResponse::NotFound().json(ErrorResponse {
            error: "tracking_not_found".to_string(),
            message: "Tracking number not found".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    let mut file_data: Vec<u8> = Vec::new();
    let mut original_filename = String::new();
    let mut content_type = String::new();
    let mut description = String::new();
    let mut location = String::new();

    // Process multipart data
    while let Some(mut field) = payload.try_next().await? {
        let content_disposition = field.content_disposition();
        
        if let Some(field_name) = content_disposition.get_name() {
            match field_name {
                "image" => {
                    if let Some(filename) = content_disposition.get_filename() {
                        original_filename = filename.to_string();
                        content_type = field.content_type().map(|ct| ct.to_string()).unwrap_or_default();
                    }
                    
                    // Read file data
                    while let Some(chunk) = field.try_next().await? {
                        file_data.extend_from_slice(&chunk);
                    }
                }
                "description" => {
                    while let Some(chunk) = field.try_next().await? {
                        description.push_str(&String::from_utf8_lossy(&chunk));
                    }
                }
                "location" => {
                    while let Some(chunk) = field.try_next().await? {
                        location.push_str(&String::from_utf8_lossy(&chunk));
                    }
                }
                _ => {}
            }
        }
    }

    // Validate file
    if file_data.is_empty() {
        return Ok(HttpResponse::BadRequest().json(serde_json::json!({
            "error": "missing_file",
            "message": "No image file provided",
            "field": "image"
        })));
    }

    // Validate file size (5MB max)
    const MAX_SIZE: usize = 5 * 1024 * 1024;
    if file_data.len() > MAX_SIZE {
        return Ok(HttpResponse::PayloadTooLarge().json(serde_json::json!({
            "error": "file_too_large",
            "message": format!("File size {} exceeds maximum allowed size of {} bytes", file_data.len(), MAX_SIZE),
            "maxSize": "5MB",
            "actualSize": format!("{} bytes", file_data.len())
        })));
    }

    // Validate MIME type
    let allowed_types = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    if !allowed_types.contains(&content_type.as_str()) {
        return Ok(HttpResponse::BadRequest().json(serde_json::json!({
            "error": "invalid_file",
            "message": "Invalid file format. Only JPEG, PNG, GIF, WebP are allowed",
            "allowedTypes": allowed_types,
            "actualType": content_type
        })));
    }

    // Generate unique ID and filename
    let evidence_id = Uuid::new_v4().to_string();
    let extension = std::path::Path::new(&original_filename)
        .extension()
        .and_then(|ext| ext.to_str())
        .unwrap_or("png");
    let filename = format!("{}.{}", evidence_id, extension);

    // Create directory and save file
    let evidence_dir = format!("uploads/evidence/{}", tracking_number);
    std::fs::create_dir_all(&evidence_dir).map_err(|_| {
        actix_web::error::ErrorInternalServerError("Failed to create upload directory")
    })?;

    let file_path = format!("{}/{}", evidence_dir, filename);
    std::fs::write(&file_path, &file_data).map_err(|_| {
        actix_web::error::ErrorInternalServerError("Failed to save evidence file")
    })?;

    // Create evidence metadata
    let evidence = Evidence {
        id: evidence_id,
        tracking_number: tracking_number.clone(),
        filename: filename.clone(),
        original_name: original_filename,
        size: file_data.len() as u64,
        mime_type: content_type,
        uploaded_at: chrono::Utc::now().to_rfc3339(),
        description,
        location,
        url: format!("/uploads/evidence/{}/{}", tracking_number, filename),
        file_path,
    };

    // Store in memory (use database in production)
    {
        let mut store = data.evidence_store.lock().unwrap();
        store.entry(tracking_number.clone()).or_insert_with(Vec::new).push(evidence.clone());
    }

    Ok(HttpResponse::Created().json(EvidenceResponse {
        success: true,
        message: "Delivery evidence uploaded successfully".to_string(),
        tracking_number,
        evidence,
    }))
}

// Get evidence handler
async fn get_evidence(
    path: web::Path<String>,
    data: web::Data<AppState>,
) -> Result<HttpResponse> {
    let tracking_number = path.into_inner();

    // Validate tracking number format
    if !is_valid_tracking_number(&tracking_number) {
        return Ok(HttpResponse::BadRequest().json(ErrorResponse {
            error: "invalid_tracking_number".to_string(),
            message: "Invalid tracking number format".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    // Check if tracking number exists
    if !data.tracking_data.contains_key(&tracking_number) {
        return Ok(HttpResponse::NotFound().json(ErrorResponse {
            error: "tracking_not_found".to_string(),
            message: "Tracking number not found".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    // Get evidence
    let evidence_list = {
        let store = data.evidence_store.lock().unwrap();
        store.get(&tracking_number).cloned().unwrap_or_default()
    };

    Ok(HttpResponse::Ok().json(EvidenceListResponse {
        tracking_number,
        evidence_count: evidence_list.len(),
        evidence: evidence_list,
    }))
}

// Delete evidence handler
async fn delete_evidence(
    path: web::Path<(String, String)>,
    data: web::Data<AppState>,
) -> Result<HttpResponse> {
    let (tracking_number, evidence_id) = path.into_inner();

    // Validate tracking number format
    if !is_valid_tracking_number(&tracking_number) {
        return Ok(HttpResponse::BadRequest().json(ErrorResponse {
            error: "invalid_tracking_number".to_string(),
            message: "Invalid tracking number format".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    // Check if tracking number exists
    if !data.tracking_data.contains_key(&tracking_number) {
        return Ok(HttpResponse::NotFound().json(ErrorResponse {
            error: "tracking_not_found".to_string(),
            message: "Tracking number not found".to_string(),
            tracking_number: tracking_number.clone(),
        }));
    }

    // Find and delete evidence
    let mut evidence_to_delete = None;
    {
        let mut store = data.evidence_store.lock().unwrap();
        if let Some(evidence_list) = store.get_mut(&tracking_number) {
            if let Some(pos) = evidence_list.iter().position(|e| e.id == evidence_id) {
                evidence_to_delete = Some(evidence_list.remove(pos));
            }
        }
    }

    if let Some(evidence) = evidence_to_delete {
        // Delete file
        if std::path::Path::new(&evidence.file_path).exists() {
            let _ = std::fs::remove_file(&evidence.file_path);
        }

        Ok(HttpResponse::Ok().json(serde_json::json!({
            "success": true,
            "message": "Evidence deleted successfully",
            "trackingNumber": tracking_number,
            "evidenceId": evidence_id
        })))
    } else {
        Ok(HttpResponse::NotFound().json(serde_json::json!({
            "error": "evidence_not_found",
            "message": "Evidence with specified ID not found"
        })))
    }
}

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    env_logger::init();
    
    let port = 8082;
    println!("🚀 Rust server running on port {}", port);
    println!("📡 API endpoint: http://localhost:{}/api/v1/tracking/{{trackingNumber}}", port);
    println!("📎 Evidence endpoint: http://localhost:{}/api/v1/tracking/{{trackingNumber}}/evidence", port);
    println!("📁 Evidence files: http://localhost:{}/uploads/", port);
    
    // Initialize app state
    let app_state = web::Data::new(AppState {
        tracking_data: get_tracking_data(),
        evidence_store: Mutex::new(HashMap::new()),
    });
    
    HttpServer::new(move || {
        App::new()
            .app_data(app_state.clone())
            .wrap(Logger::default())
            .service(fs::Files::new("/uploads", "./uploads").show_files_listing())
            .route("/health", web::get().to(health_check))
            .service(
                web::scope("/api/v1")
                    .route("/tracking/{tracking_number}", web::get().to(get_tracking_info))
                    .route("/tracking/{tracking_number}/evidence", web::post().to(upload_evidence))
                    .route("/tracking/{tracking_number}/evidence", web::get().to(get_evidence))
                    .route("/tracking/{tracking_number}/evidence/{evidence_id}", web::delete().to(delete_evidence))
            )
    })
    .bind(("127.0.0.1", port))?
    .run()
    .await
}
