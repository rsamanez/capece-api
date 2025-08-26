#!/bin/bash

# Package Tracking API - Test All Implementations
# This script tests all API implementations
# For detailed step-by-step testing instructions, see TESTING_GUIDE.md

echo "🧪 Testing Package Tracking API implementations..."
echo "================================================="
echo "📖 For detailed testing guide, see: TESTING_GUIDE.md"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test endpoints
ENDPOINTS=(
    "http://localhost:3000/api/v1/tracking/1Z999AA1234567890|Node.js"
    "http://localhost:8000/api/v1/tracking/1Z999AA1234567890|Python"
    "http://localhost:8080/api/v1/tracking/1Z999AA1234567890|PHP"
    "http://localhost:8081/api/v1/tracking/1Z999AA1234567890|Go"
    "http://localhost:8082/api/v1/tracking/1Z999AA1234567890|Rust"
)

# Test tracking numbers
VALID_TRACKING="1Z999AA1234567890"
INVALID_TRACKING="invalid123"
NOT_FOUND_TRACKING="NOTFOUND123456"

# Function to test endpoint
test_endpoint() {
    local url=$1
    local name=$2
    local tracking=$3
    local expected_status=$4
    
    echo -e "${BLUE}Testing $name with tracking: $tracking${NC}"
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" "$url")
    http_code=$(echo $response | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    body=$(echo $response | sed -e 's/HTTPSTATUS\:.*//g')
    
    if [ "$http_code" = "$expected_status" ]; then
        echo -e "${GREEN}✓ $name - HTTP $http_code (Expected: $expected_status)${NC}"
        if [ "$expected_status" = "200" ]; then
            # Validate JSON structure for successful responses
            if echo "$body" | jq -e '.trackingNumber' > /dev/null 2>&1; then
                echo -e "${GREEN}✓ $name - Valid JSON structure${NC}"
            else
                echo -e "${RED}✗ $name - Invalid JSON structure${NC}"
            fi
        fi
    else
        echo -e "${RED}✗ $name - HTTP $http_code (Expected: $expected_status)${NC}"
    fi
    echo ""
}

# Function to test health endpoints
test_health() {
    local port=$1
    local name=$2
    
    echo -e "${BLUE}Testing $name health endpoint${NC}"
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code}" "http://localhost:$port/health")
    http_code=$(echo $response | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
    
    if [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✓ $name health - HTTP $http_code${NC}"
    else
        echo -e "${RED}✗ $name health - HTTP $http_code${NC}"
    fi
    echo ""
}

# Function to check if service is running
check_service() {
    local port=$1
    local name=$2
    
    if curl -s "http://localhost:$port/health" > /dev/null 2>&1; then
        return 0
    else
        echo -e "${YELLOW}⚠ $name service not running on port $port${NC}"
        return 1
    fi
}

# Main test function
main() {
    echo "Checking which services are running..."
    echo ""
    
    # Test health endpoints first
    check_service 3000 "Node.js" && test_health 3000 "Node.js"
    check_service 8000 "Python" && test_health 8000 "Python"
    check_service 8080 "PHP" && test_health 8080 "PHP"
    check_service 8081 "Go" && test_health 8081 "Go"
    check_service 8082 "Rust" && test_health 8082 "Rust"
    
    echo "================================================="
    echo "Testing valid tracking number: $VALID_TRACKING"
    echo "================================================="
    
    # Test valid tracking numbers
    for endpoint in "${ENDPOINTS[@]}"; do
        IFS='|' read -r url name <<< "$endpoint"
        if check_service $(echo $url | sed 's/.*localhost://;s/\/.*//') "$name"; then
            test_endpoint "$url" "$name" "$VALID_TRACKING" "200"
        fi
    done
    
    echo "================================================="
    echo "Testing invalid tracking number: $INVALID_TRACKING"
    echo "================================================="
    
    # Test invalid tracking numbers
    for endpoint in "${ENDPOINTS[@]}"; do
        IFS='|' read -r url name <<< "$endpoint"
        invalid_url=$(echo $url | sed "s/$VALID_TRACKING/$INVALID_TRACKING/")
        if check_service $(echo $url | sed 's/.*localhost://;s/\/.*//') "$name"; then
            test_endpoint "$invalid_url" "$name" "$INVALID_TRACKING" "400"
        fi
    done
    
    echo "================================================="
    echo "Testing not found tracking number: $NOT_FOUND_TRACKING"
    echo "================================================="
    
    # Test not found tracking numbers
    for endpoint in "${ENDPOINTS[@]}"; do
        IFS='|' read -r url name <<< "$endpoint"
        notfound_url=$(echo $url | sed "s/$VALID_TRACKING/$NOT_FOUND_TRACKING/")
        if check_service $(echo $url | sed 's/.*localhost://;s/\/.*//') "$name"; then
            test_endpoint "$notfound_url" "$name" "$NOT_FOUND_TRACKING" "404"
        fi
    done
    
    echo "================================================="
    echo -e "${GREEN}Testing completed!${NC}"
    echo ""
    echo "To start all services, run: ./start-all.sh"
    echo "To test individual endpoints:"
    echo "  curl http://localhost:3000/api/v1/tracking/1Z999AA1234567890"
    echo "  curl http://localhost:8000/api/v1/tracking/FDX123456789012"
}

# Check if curl and jq are available
if ! command -v curl &> /dev/null; then
    echo -e "${RED}Error: curl is required but not installed.${NC}"
    echo "📖 See TESTING_GUIDE.md for installation instructions"
    exit 1
fi

if ! command -v jq &> /dev/null; then
    echo -e "${YELLOW}Warning: jq not found. JSON validation will be skipped.${NC}"
    echo "📖 See TESTING_GUIDE.md for jq installation instructions"
fi

echo ""
echo "📖 For detailed testing instructions and troubleshooting:"
echo "   👉 See TESTING_GUIDE.md"
echo ""
echo "🔧 To test individual implementations manually:"
echo "   👉 Follow the step-by-step guide in TESTING_GUIDE.md"
echo ""

# Run tests
main
