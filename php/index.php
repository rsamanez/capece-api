<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Mock tracking data
function getTrackingData() {
    return [
        '1Z999AA1234567890' => [
            'trackingNumber' => '1Z999AA1234567890',
            'status' => 'in_transit',
            'estimatedDelivery' => '2025-08-30T15:30:00Z',
            'carrier' => 'UPS',
            'service' => 'Ground',
            'origin' => [
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postalCode' => '10001'
            ],
            'destination' => [
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postalCode' => '90210'
            ],
            'package' => [
                'weight' => 2.5,
                'dimensions' => [
                    'length' => 12,
                    'width' => 8,
                    'height' => 6
                ],
                'description' => 'Electronics'
            ],
            'events' => [
                [
                    'timestamp' => '2025-08-26T10:00:00Z',
                    'status' => 'picked_up',
                    'location' => 'New York, NY',
                    'description' => 'Package picked up',
                    'facilityType' => 'origin'
                ],
                [
                    'timestamp' => '2025-08-27T08:30:00Z',
                    'status' => 'in_transit',
                    'location' => 'Philadelphia, PA',
                    'description' => 'Departed from facility',
                    'facilityType' => 'sort_facility'
                ]
            ]
        ],
        'FDX123456789012' => [
            'trackingNumber' => 'FDX123456789012',
            'status' => 'delivered',
            'estimatedDelivery' => '2025-08-28T14:00:00Z',
            'actualDelivery' => '2025-08-28T13:45:00Z',
            'carrier' => 'FedEx',
            'service' => 'Express',
            'origin' => [
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'postalCode' => '60601'
            ],
            'destination' => [
                'city' => 'Miami',
                'state' => 'FL',
                'country' => 'USA',
                'postalCode' => '33101'
            ],
            'package' => [
                'weight' => 1.2,
                'dimensions' => [
                    'length' => 10,
                    'width' => 6,
                    'height' => 4
                ],
                'description' => 'Documents'
            ],
            'events' => [
                [
                    'timestamp' => '2025-08-26T09:00:00Z',
                    'status' => 'picked_up',
                    'location' => 'Chicago, IL',
                    'description' => 'Package picked up',
                    'facilityType' => 'origin'
                ],
                [
                    'timestamp' => '2025-08-27T12:00:00Z',
                    'status' => 'in_transit',
                    'location' => 'Memphis, TN',
                    'description' => 'In transit',
                    'facilityType' => 'sort_facility'
                ],
                [
                    'timestamp' => '2025-08-28T08:00:00Z',
                    'status' => 'out_for_delivery',
                    'location' => 'Miami, FL',
                    'description' => 'Out for delivery',
                    'facilityType' => 'delivery'
                ],
                [
                    'timestamp' => '2025-08-28T13:45:00Z',
                    'status' => 'delivered',
                    'location' => 'Miami, FL',
                    'description' => 'Delivered',
                    'facilityType' => 'delivery'
                ]
            ]
        ]
    ];
}

// Evidence storage - Simple file-based storage for Docker
function getEvidenceStorage() {
    $storageFile = __DIR__ . '/storage/evidence.json';
    if (file_exists($storageFile)) {
        $data = file_get_contents($storageFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveEvidenceStorage($evidence) {
    $storageFile = __DIR__ . '/storage/evidence.json';
    return file_put_contents($storageFile, json_encode($evidence, JSON_PRETTY_PRINT));
}

function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function validateTrackingNumber($trackingNumber) {
    return preg_match('/^[A-Z0-9]{10,20}$/', $trackingNumber);
}

function saveEvidence($trackingNumber, $fileData, $originalName, $description, $location) {
    // Create uploads directory if it doesn't exist
    $uploadDir = './uploads/evidence/' . $trackingNumber;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $id = generateUuid();
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = $id . '.' . $extension;
    $filePath = $uploadDir . '/' . $filename;
    
    // Save file
    if (file_put_contents($filePath, $fileData) === false) {
        return null;
    }
    
    // Create evidence record
    $evidenceRecord = [
        'id' => $id,
        'trackingNumber' => $trackingNumber,
        'filename' => $filename,
        'originalName' => $originalName,
        'size' => strlen($fileData),
        'mimeType' => 'image/' . strtolower($extension),
        'uploadedAt' => date('c'),
        'description' => $description,
        'location' => $location,
        'url' => '/uploads/evidence/' . $trackingNumber . '/' . $filename,
        'filePath' => $filePath
    ];
    
    // Store in file-based storage
    $evidence = getEvidenceStorage();
    if (!isset($evidence[$trackingNumber])) {
        $evidence[$trackingNumber] = [];
    }
    $evidence[$trackingNumber][] = $evidenceRecord;
    saveEvidenceStorage($evidence);
    
    return $evidenceRecord;
}

function getEvidenceByTracking($trackingNumber) {
    $evidence = getEvidenceStorage();
    return isset($evidence[$trackingNumber]) ? $evidence[$trackingNumber] : [];
}

function deleteEvidence($trackingNumber, $evidenceId) {
    $evidence = getEvidenceStorage();
    
    if (!isset($evidence[$trackingNumber])) {
        return ['error' => 'tracking_not_found'];
    }
    
    $evidenceList = $evidence[$trackingNumber];
    $evidenceToDelete = null;
    $evidenceIndex = null;
    
    // Find evidence by ID
    foreach ($evidenceList as $index => $item) {
        if ($item['id'] === $evidenceId) {
            $evidenceToDelete = $item;
            $evidenceIndex = $index;
            break;
        }
    }
    
    if (!$evidenceToDelete) {
        return ['error' => 'evidence_not_found'];
    }
    
    // Delete physical file if it exists
    if (isset($evidenceToDelete['filePath']) && file_exists($evidenceToDelete['filePath'])) {
        if (!unlink($evidenceToDelete['filePath'])) {
            return ['error' => 'delete_failed', 'message' => 'Failed to delete evidence file'];
        }
    }
    
    // Remove from evidence list
    array_splice($evidenceList, $evidenceIndex, 1);
    $evidence[$trackingNumber] = $evidenceList;
    
    // Save updated evidence storage
    $evidenceFile = __DIR__ . '/storage/evidence.json';
    if (!file_put_contents($evidenceFile, json_encode($evidence, JSON_PRETTY_PRINT))) {
        return ['error' => 'storage_error', 'message' => 'Failed to update evidence storage'];
    }
    
    return ['success' => true];
}

// Routes
if ($method === 'GET') {
    if ($path === '/health') {
        echo json_encode([
            'status' => 'OK',
            'timestamp' => date('c')
        ]);
        exit;
    }
    
    if (preg_match('/^\/api\/v1\/tracking\/([A-Z0-9]+)$/', $path, $matches)) {
        $trackingNumber = $matches[1];
        $trackingData = getTrackingData();
        
        if (!validateTrackingNumber($trackingNumber)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        if (isset($trackingData[$trackingNumber])) {
            echo json_encode($trackingData[$trackingNumber]);
        } else {
            http_response_code(404);
            echo json_encode([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ]);
        }
        exit;
    }
    
    // Get evidence for tracking number
    if (preg_match('/^\/api\/v1\/tracking\/([A-Z0-9]+)\/evidence\/?$/', $path, $matches)) {
        $trackingNumber = $matches[1];
        $trackingData = getTrackingData();
        
        if (!validateTrackingNumber($trackingNumber)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        if (!isset($trackingData[$trackingNumber])) {
            http_response_code(404);
            echo json_encode([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        $evidenceList = getEvidenceByTracking($trackingNumber);
        echo json_encode([
            'trackingNumber' => $trackingNumber,
            'evidenceCount' => count($evidenceList),
            'evidence' => array_map(function($e) {
                unset($e['filePath']); // Don't expose internal file path
                return $e;
            }, $evidenceList)
        ]);
        exit;
    }
}

if ($method === 'POST') {
    // Upload evidence
    if (preg_match('/^\/api\/v1\/tracking\/([A-Z0-9]+)\/evidence\/?$/', $path, $matches)) {
        $trackingNumber = $matches[1];
        $trackingData = getTrackingData();
        
        if (!validateTrackingNumber($trackingNumber)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        if (!isset($trackingData[$trackingNumber])) {
            http_response_code(404);
            echo json_encode([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'error' => 'missing_file',
                'message' => 'No image file provided',
                'field' => 'image'
            ]);
            exit;
        }
        
        $uploadedFile = $_FILES['image'];
        $description = $_POST['description'] ?? '';
        $location = $_POST['location'] ?? '';
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_file_type',
                'message' => 'Only JPEG, PNG, and GIF images are allowed',
                'allowedTypes' => $allowedTypes
            ]);
            exit;
        }
        
        // Validate file size (max 5MB)
        if ($uploadedFile['size'] > 5 * 1024 * 1024) {
            http_response_code(413);
            echo json_encode([
                'error' => 'file_too_large',
                'message' => 'File size must be less than 5MB',
                'maxSize' => '5MB'
            ]);
            exit;
        }
        
        // Read file data
        $fileData = file_get_contents($uploadedFile['tmp_name']);
        if ($fileData === false) {
            http_response_code(500);
            echo json_encode([
                'error' => 'file_read_error',
                'message' => 'Failed to read uploaded file'
            ]);
            exit;
        }
        
        // Save evidence
        $evidence = saveEvidence($trackingNumber, $fileData, $uploadedFile['name'], $description, $location);
        if (!$evidence) {
            http_response_code(500);
            echo json_encode([
                'error' => 'save_error',
                'message' => 'Failed to save evidence file'
            ]);
            exit;
        }
        
        // Remove internal fields from response
        unset($evidence['filePath']);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Delivery evidence uploaded successfully',
            'trackingNumber' => $trackingNumber,
            'evidence' => $evidence
        ]);
        exit;
    }
}

// DELETE requests
if ($method === 'DELETE') {
    // Delete specific evidence
    if (preg_match('/^\/api\/v1\/tracking\/([A-Z0-9]+)\/evidence\/([a-f0-9\-]+)\/?$/', $path, $matches)) {
        $trackingNumber = $matches[1];
        $evidenceId = $matches[2];
        $trackingData = getTrackingData();
        
        if (!validateTrackingNumber($trackingNumber)) {
            http_response_code(400);
            echo json_encode([
                'error' => 'invalid_tracking_number',
                'message' => 'Invalid tracking number format',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        if (!isset($trackingData[$trackingNumber])) {
            http_response_code(404);
            echo json_encode([
                'error' => 'tracking_not_found',
                'message' => 'Tracking number not found',
                'trackingNumber' => $trackingNumber
            ]);
            exit;
        }
        
        // Delete evidence
        $result = deleteEvidence($trackingNumber, $evidenceId);
        
        if (isset($result['error'])) {
            if ($result['error'] === 'evidence_not_found') {
                http_response_code(404);
                echo json_encode([
                    'error' => 'evidence_not_found',
                    'message' => 'Evidence not found for this tracking number',
                    'trackingNumber' => $trackingNumber,
                    'evidenceId' => $evidenceId
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'error' => $result['error'],
                    'message' => $result['message'] ?? 'Failed to delete evidence',
                    'trackingNumber' => $trackingNumber,
                    'evidenceId' => $evidenceId
                ]);
            }
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Evidence deleted successfully',
            'trackingNumber' => $trackingNumber,
            'evidenceId' => $evidenceId
        ]);
        exit;
    }
}

// 404 for other routes
http_response_code(404);
echo json_encode([
    'error' => 'not_found',
    'message' => 'Endpoint not found'
]);

?>
