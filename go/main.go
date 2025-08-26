package main

import (
	"log"
	"net/http"
	"regexp"
	"time"

	"github.com/gin-gonic/gin"
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

// ErrorResponse represents an error response
type ErrorResponse struct {
	Error          string `json:"error"`
	Message        string `json:"message"`
	TrackingNumber string `json:"trackingNumber"`
}

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

	// API routes
	api := router.Group("/api/v1")
	{
		api.GET("/tracking/:trackingNumber", getTrackingInfo)
	}

	// Health check
	router.GET("/health", healthCheck)

	// Start server
	port := ":8081"
	log.Printf("🚀 Go server running on port %s", port)
	log.Printf("📡 API endpoint: http://localhost%s/api/v1/tracking/{trackingNumber}", port)
	
	if err := router.Run(port); err != nil {
		log.Fatal("Failed to start server:", err)
	}
}
