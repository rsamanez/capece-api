package main

import (
	"fmt"
	"log"
	"mime/multipart"
	"net/http"
	"os"
	"path/filepath"
	"regexp"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

// TrackingEvent represents a single tracking event
type TrackingEvent struct {
	Timestamp    string `json:"timestamp"`
	Status       string `json:"status"`
	Location     string `json:"location"`
	Description  string `json:"description"`
	FacilityType string `json:"facilityType"`
}

// Address represents an address
type Address struct {
	Address    string `json:"address,omitempty"`
	City       string `json:"city"`
	State      string `json:"state"`
	Country    string `json:"country"`
	PostalCode string `json:"postalCode"`
}

// Dimensions represents package dimensions
type Dimensions struct {
	Length float64 `json:"length"`
	Width  float64 `json:"width"`
	Height float64 `json:"height"`
}

// Package represents package information
type Package struct {
	Weight      float64    `json:"weight"`
	Dimensions  Dimensions `json:"dimensions"`
	Description string     `json:"description"`
}

// TrackingInfo represents the complete tracking information
type TrackingInfo struct {
	TrackingNumber   string           `json:"trackingNumber"`
	Status           string           `json:"status"`
	EstimatedDelivery string          `json:"estimatedDelivery"`
	ActualDelivery   *string          `json:"actualDelivery,omitempty"`
	Carrier          string           `json:"carrier"`
	Service          string           `json:"service"`
	Origin           Address          `json:"origin"`
	Destination      Address          `json:"destination"`
	Package          Package          `json:"package"`
	Events           []TrackingEvent  `json:"events"`
}

// Evidence represents delivery evidence
type Evidence struct {
	ID           string `json:"id"`
	TrackingNumber string `json:"trackingNumber"`
	Filename     string `json:"filename"`
	OriginalName string `json:"originalName"`
	Size         int64  `json:"size"`
	MimeType     string `json:"mimeType"`
	UploadedAt   string `json:"uploadedAt"`
	Description  string `json:"description"`
	Location     string `json:"location"`
	URL          string `json:"url"`
	FilePath     string `json:"-"`
}

// EvidenceResponse represents evidence API response
type EvidenceResponse struct {
	Success        bool     `json:"success"`
	Message        string   `json:"message"`
	TrackingNumber string   `json:"trackingNumber"`
	Evidence       Evidence `json:"evidence"`
}

// EvidenceListResponse represents evidence list API response
type EvidenceListResponse struct {
	TrackingNumber string     `json:"trackingNumber"`
	EvidenceCount  int        `json:"evidenceCount"`
	Evidence       []Evidence `json:"evidence"`
}

// ErrorResponse represents an error response
type ErrorResponse struct {
	Error          string `json:"error"`
	Message        string `json:"message"`
	TrackingNumber string `json:"trackingNumber"`
}

// In-memory evidence store (use database in production)
var evidenceStore = make(map[string][]Evidence)

// Mock tracking data
var trackingData = map[string]TrackingInfo{
	"1Z999AA1234567890": {
		TrackingNumber:    "1Z999AA1234567890",
		Status:           "in_transit",
		EstimatedDelivery: "2025-08-30T15:30:00Z",
		Carrier:          "UPS",
		Service:          "Ground",
		Origin: Address{
			City:       "New York",
			State:      "NY",
			Country:    "USA",
			PostalCode: "10001",
		},
		Destination: Address{
			City:       "Los Angeles",
			State:      "CA",
			Country:    "USA",
			PostalCode: "90210",
		},
		Package: Package{
			Weight: 2.5,
			Dimensions: Dimensions{
				Length: 12,
				Width:  8,
				Height: 6,
			},
			Description: "Electronics",
		},
		Events: []TrackingEvent{
			{
				Timestamp:    "2025-08-26T10:00:00Z",
				Status:       "picked_up",
				Location:     "New York, NY",
				Description:  "Package picked up",
				FacilityType: "origin",
			},
			{
				Timestamp:    "2025-08-27T08:30:00Z",
				Status:       "in_transit",
				Location:     "Philadelphia, PA",
				Description:  "Departed from facility",
				FacilityType: "sort_facility",
			},
		},
	},
	"FDX123456789012": {
		TrackingNumber:    "FDX123456789012",
		Status:           "delivered",
		EstimatedDelivery: "2025-08-28T14:00:00Z",
		ActualDelivery:   stringPtr("2025-08-28T13:45:00Z"),
		Carrier:          "FedEx",
		Service:          "Express",
		Origin: Address{
			City:       "Chicago",
			State:      "IL",
			Country:    "USA",
			PostalCode: "60601",
		},
		Destination: Address{
			City:       "Miami",
			State:      "FL",
			Country:    "USA",
			PostalCode: "33101",
		},
		Package: Package{
			Weight: 1.2,
			Dimensions: Dimensions{
				Length: 10,
				Width:  6,
				Height: 4,
			},
			Description: "Documents",
		},
		Events: []TrackingEvent{
			{
				Timestamp:    "2025-08-26T09:00:00Z",
				Status:       "picked_up",
				Location:     "Chicago, IL",
				Description:  "Package picked up",
				FacilityType: "origin",
			},
			{
				Timestamp:    "2025-08-27T12:00:00Z",
				Status:       "in_transit",
				Location:     "Memphis, TN",
				Description:  "In transit",
				FacilityType: "sort_facility",
			},
			{
				Timestamp:    "2025-08-28T08:00:00Z",
				Status:       "out_for_delivery",
				Location:     "Miami, FL",
				Description:  "Out for delivery",
				FacilityType: "delivery",
			},
			{
				Timestamp:    "2025-08-28T13:45:00Z",
				Status:       "delivered",
				Location:     "Miami, FL",
				Description:  "Delivered",
				FacilityType: "delivery",
			},
		},
	},
}

// Helper function to create string pointer
func stringPtr(s string) *string {
	return &s
}

// Validate tracking number format
func isValidTrackingNumber(trackingNumber string) bool {
	pattern := `^[A-Z0-9]{10,20}$`
	matched, _ := regexp.MatchString(pattern, trackingNumber)
	return matched
}

// Get tracking information handler
func getTrackingInfo(c *gin.Context) {
	trackingNumber := c.Param("trackingNumber")

	// Validate tracking number format
	if !isValidTrackingNumber(trackingNumber) {
		c.JSON(http.StatusBadRequest, ErrorResponse{
			Error:          "invalid_tracking_number",
			Message:        "Invalid tracking number format",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Get tracking information
	trackingInfo, exists := trackingData[trackingNumber]
	if !exists {
		c.JSON(http.StatusNotFound, ErrorResponse{
			Error:          "tracking_not_found",
			Message:        "Tracking number not found",
			TrackingNumber: trackingNumber,
		})
		return
	}

	c.JSON(http.StatusOK, trackingInfo)
}

// Validate uploaded file
func validateFile(file *multipart.FileHeader) error {
	// Check file size (5MB max)
	maxSize := int64(5 * 1024 * 1024)
	if file.Size > maxSize {
		return fmt.Errorf("file size %d exceeds maximum allowed size of %d bytes", file.Size, maxSize)
	}

	// Check MIME type
	allowedTypes := []string{"image/jpeg", "image/png", "image/gif", "image/webp"}
	contentType := file.Header.Get("Content-Type")
	
	valid := false
	for _, allowedType := range allowedTypes {
		if contentType == allowedType {
			valid = true
			break
		}
	}
	
	if !valid {
		return fmt.Errorf("invalid file format: %s. Only JPEG, PNG, GIF, WebP are allowed", contentType)
	}

	return nil
}

// Upload evidence handler
func uploadEvidence(c *gin.Context) {
	trackingNumber := c.Param("trackingNumber")

	// Validate tracking number format
	if !isValidTrackingNumber(trackingNumber) {
		c.JSON(http.StatusBadRequest, ErrorResponse{
			Error:          "invalid_tracking_number",
			Message:        "Invalid tracking number format",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Check if tracking number exists
	_, exists := trackingData[trackingNumber]
	if !exists {
		c.JSON(http.StatusNotFound, ErrorResponse{
			Error:          "tracking_not_found",
			Message:        "Tracking number not found",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Handle file upload
	file, err := c.FormFile("image")
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{
			"error":   "missing_file",
			"message": "No image file provided",
			"field":   "image",
		})
		return
	}

	// Validate file
	if err := validateFile(file); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{
			"error":   "invalid_file",
			"message": err.Error(),
		})
		return
	}

	// Generate unique ID and filename
	evidenceID := uuid.New().String()
	ext := filepath.Ext(file.Filename)
	filename := evidenceID + ext

	// Create directory
	evidenceDir := filepath.Join("uploads", "evidence", trackingNumber)
	if err := os.MkdirAll(evidenceDir, 0755); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"error":   "upload_failed",
			"message": "Failed to create upload directory",
		})
		return
	}

	// Save file
	filePath := filepath.Join(evidenceDir, filename)
	if err := c.SaveUploadedFile(file, filePath); err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"error":   "upload_failed",
			"message": "Failed to save evidence file",
		})
		return
	}

	// Create evidence metadata
	evidence := Evidence{
		ID:             evidenceID,
		TrackingNumber: trackingNumber,
		Filename:       filename,
		OriginalName:   file.Filename,
		Size:           file.Size,
		MimeType:       file.Header.Get("Content-Type"),
		UploadedAt:     time.Now().Format(time.RFC3339),
		Description:    c.PostForm("description"),
		Location:       c.PostForm("location"),
		URL:            fmt.Sprintf("/uploads/evidence/%s/%s", trackingNumber, filename),
		FilePath:       filePath,
	}

	// Store in memory (use database in production)
	evidenceStore[trackingNumber] = append(evidenceStore[trackingNumber], evidence)

	c.JSON(http.StatusCreated, EvidenceResponse{
		Success:        true,
		Message:        "Delivery evidence uploaded successfully",
		TrackingNumber: trackingNumber,
		Evidence:       evidence,
	})
}

// Get evidence handler
func getEvidence(c *gin.Context) {
	trackingNumber := c.Param("trackingNumber")

	// Validate tracking number format
	if !isValidTrackingNumber(trackingNumber) {
		c.JSON(http.StatusBadRequest, ErrorResponse{
			Error:          "invalid_tracking_number",
			Message:        "Invalid tracking number format",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Check if tracking number exists
	_, exists := trackingData[trackingNumber]
	if !exists {
		c.JSON(http.StatusNotFound, ErrorResponse{
			Error:          "tracking_not_found",
			Message:        "Tracking number not found",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Get evidence
	evidenceList := evidenceStore[trackingNumber]
	if evidenceList == nil {
		evidenceList = []Evidence{}
	}

	c.JSON(http.StatusOK, EvidenceListResponse{
		TrackingNumber: trackingNumber,
		EvidenceCount:  len(evidenceList),
		Evidence:       evidenceList,
	})
}

// Delete evidence handler
func deleteEvidence(c *gin.Context) {
	trackingNumber := c.Param("trackingNumber")
	evidenceID := c.Param("evidenceId")

	// Validate tracking number format
	if !isValidTrackingNumber(trackingNumber) {
		c.JSON(http.StatusBadRequest, ErrorResponse{
			Error:          "invalid_tracking_number",
			Message:        "Invalid tracking number format",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Check if tracking number exists
	_, exists := trackingData[trackingNumber]
	if !exists {
		c.JSON(http.StatusNotFound, ErrorResponse{
			Error:          "tracking_not_found",
			Message:        "Tracking number not found",
			TrackingNumber: trackingNumber,
		})
		return
	}

	// Find and delete evidence
	evidenceList := evidenceStore[trackingNumber]
	var evidenceToDelete *Evidence
	var newEvidenceList []Evidence

	for _, evidence := range evidenceList {
		if evidence.ID == evidenceID {
			evidenceToDelete = &evidence
		} else {
			newEvidenceList = append(newEvidenceList, evidence)
		}
	}

	if evidenceToDelete == nil {
		c.JSON(http.StatusNotFound, gin.H{
			"error":   "evidence_not_found",
			"message": "Evidence with specified ID not found",
		})
		return
	}

	// Delete file
	if err := os.Remove(evidenceToDelete.FilePath); err != nil {
		log.Printf("Warning: Failed to delete file %s: %v", evidenceToDelete.FilePath, err)
	}

	// Update store
	evidenceStore[trackingNumber] = newEvidenceList

	c.JSON(http.StatusOK, gin.H{
		"success":        true,
		"message":        "Evidence deleted successfully",
		"trackingNumber": trackingNumber,
		"evidenceId":     evidenceID,
	})
}

// Health check handler
func healthCheck(c *gin.Context) {
	c.JSON(http.StatusOK, gin.H{
		"status":    "OK",
		"timestamp": time.Now().Format(time.RFC3339),
	})
}

func main() {
	// Create Gin router
	router := gin.Default()

	// Serve static files
	router.Static("/uploads", "./uploads")

	// API routes
	api := router.Group("/api/v1")
	{
		api.GET("/tracking/:trackingNumber", getTrackingInfo)
		
		// Evidence routes
		api.POST("/tracking/:trackingNumber/evidence", uploadEvidence)
		api.GET("/tracking/:trackingNumber/evidence", getEvidence)
		api.DELETE("/tracking/:trackingNumber/evidence/:evidenceId", deleteEvidence)
	}

	// Health check
	router.GET("/health", healthCheck)

	// Start server
	port := ":8083"
	log.Printf("🚀 Go server running on port %s", port)
	log.Printf("📡 API endpoint: http://localhost%s/api/v1/tracking/{trackingNumber}", port)
	log.Printf("📎 Evidence endpoint: http://localhost%s/api/v1/tracking/{trackingNumber}/evidence", port)
	log.Printf("📁 Evidence files: http://localhost%s/uploads/", port)
	
	if err := router.Run(port); err != nil {
		log.Fatal("Failed to start server:", err)
	}
}
