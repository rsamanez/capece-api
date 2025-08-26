// Mock tracking data - In production, this would come from a database or external APIs
const trackingData = {
  "1Z999AA1234567890": {
    trackingNumber: "1Z999AA1234567890",
    status: "in_transit",
    estimatedDelivery: "2025-08-30T15:30:00Z",
    carrier: "UPS",
    service: "Ground",
    origin: {
      city: "New York",
      state: "NY",
      country: "USA",
      postalCode: "10001"
    },
    destination: {
      city: "Los Angeles",
      state: "CA",
      country: "USA",
      postalCode: "90210"
    },
    package: {
      weight: 2.5,
      dimensions: {
        length: 12,
        width: 8,
        height: 6
      },
      description: "Electronics"
    },
    events: [
      {
        timestamp: "2025-08-26T10:00:00Z",
        status: "picked_up",
        location: "New York, NY",
        description: "Package picked up",
        facilityType: "origin"
      },
      {
        timestamp: "2025-08-27T08:30:00Z",
        status: "in_transit",
        location: "Philadelphia, PA",
        description: "Departed from facility",
        facilityType: "sort_facility"
      }
    ]
  },
  "FDX123456789012": {
    trackingNumber: "FDX123456789012",
    status: "delivered",
    estimatedDelivery: "2025-08-28T14:00:00Z",
    actualDelivery: "2025-08-28T13:45:00Z",
    carrier: "FedEx",
    service: "Express",
    origin: {
      city: "Chicago",
      state: "IL",
      country: "USA",
      postalCode: "60601"
    },
    destination: {
      city: "Miami",
      state: "FL",
      country: "USA",
      postalCode: "33101"
    },
    package: {
      weight: 1.2,
      dimensions: {
        length: 10,
        width: 6,
        height: 4
      },
      description: "Documents"
    },
    events: [
      {
        timestamp: "2025-08-26T09:00:00Z",
        status: "picked_up",
        location: "Chicago, IL",
        description: "Package picked up",
        facilityType: "origin"
      },
      {
        timestamp: "2025-08-27T12:00:00Z",
        status: "in_transit",
        location: "Memphis, TN",
        description: "In transit",
        facilityType: "sort_facility"
      },
      {
        timestamp: "2025-08-28T08:00:00Z",
        status: "out_for_delivery",
        location: "Miami, FL",
        description: "Out for delivery",
        facilityType: "delivery"
      },
      {
        timestamp: "2025-08-28T13:45:00Z",
        status: "delivered",
        location: "Miami, FL",
        description: "Delivered",
        facilityType: "delivery"
      }
    ]
  },
  "DHL9876543210": {
    trackingNumber: "DHL9876543210",
    status: "exception",
    estimatedDelivery: "2025-08-29T16:00:00Z",
    carrier: "DHL",
    service: "Express",
    origin: {
      city: "San Francisco",
      state: "CA",
      country: "USA",
      postalCode: "94102"
    },
    destination: {
      city: "Seattle",
      state: "WA",
      country: "USA",
      postalCode: "98101"
    },
    package: {
      weight: 3.8,
      dimensions: {
        length: 15,
        width: 10,
        height: 8
      },
      description: "Books"
    },
    events: [
      {
        timestamp: "2025-08-26T14:00:00Z",
        status: "picked_up",
        location: "San Francisco, CA",
        description: "Package picked up",
        facilityType: "origin"
      },
      {
        timestamp: "2025-08-27T18:00:00Z",
        status: "exception",
        location: "Portland, OR",
        description: "Weather delay",
        facilityType: "sort_facility"
      }
    ]
  }
};

module.exports = {
  trackingData
};
