#!/bin/bash

# Package Tracking API - Start All Implementations
# This script starts all API implementations on different ports

echo "🚀 Starting Package Tracking API implementations..."
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to check if a port is available
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${RED}Port $1 is already in use${NC}"
        return 1
    fi
    return 0
}

# Function to start Node.js implementation
start_nodejs() {
    echo -e "${BLUE}Starting Node.js Express implementation...${NC}"
    cd nodejs-express
    if [ ! -d "node_modules" ]; then
        echo "Installing Node.js dependencies..."
        npm install
    fi
    npm start &
    NODE_PID=$!
    cd ..
    echo -e "${GREEN}Node.js server started on port 3000 (PID: $NODE_PID)${NC}"
}

# Function to start Python Django implementation
start_python() {
    echo -e "${BLUE}Starting Python Django implementation...${NC}"
    cd python-django
    if [ ! -d "venv" ]; then
        echo "Creating Python virtual environment..."
        python3 -m venv venv
    fi
    source venv/bin/activate
    pip install -r requirements.txt > /dev/null 2>&1
    python manage.py runserver 8000 &
    PYTHON_PID=$!
    cd ..
    echo -e "${GREEN}Python server started on port 8000 (PID: $PYTHON_PID)${NC}"
}

# Function to start PHP Laravel implementation
start_php() {
    echo -e "${BLUE}Starting PHP Laravel implementation...${NC}"
    cd php-laravel
    if [ ! -d "vendor" ]; then
        echo "Installing PHP dependencies..."
        composer install > /dev/null 2>&1
    fi
    php artisan serve --port=8080 &
    PHP_PID=$!
    cd ..
    echo -e "${GREEN}PHP server started on port 8080 (PID: $PHP_PID)${NC}"
}

# Function to start Go implementation
start_go() {
    echo -e "${BLUE}Starting Go implementation...${NC}"
    cd go
    go run main.go &
    GO_PID=$!
    cd ..
    echo -e "${GREEN}Go server started on port 8081 (PID: $GO_PID)${NC}"
}

# Function to start Rust implementation
start_rust() {
    echo -e "${BLUE}Starting Rust implementation...${NC}"
    cd rust
    cargo run &
    RUST_PID=$!
    cd ..
    echo -e "${GREEN}Rust server started on port 8082 (PID: $RUST_PID)${NC}"
}

# Check if required tools are installed
check_dependencies() {
    echo "Checking dependencies..."
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        echo -e "${YELLOW}Warning: Node.js not found. Skipping Node.js implementation.${NC}"
        SKIP_NODE=1
    fi
    
    # Check Python
    if ! command -v python3 &> /dev/null; then
        echo -e "${YELLOW}Warning: Python3 not found. Skipping Python implementation.${NC}"
        SKIP_PYTHON=1
    fi
    
    # Check PHP
    if ! command -v php &> /dev/null; then
        echo -e "${YELLOW}Warning: PHP not found. Skipping PHP implementation.${NC}"
        SKIP_PHP=1
    fi
    
    # Check Go
    if ! command -v go &> /dev/null; then
        echo -e "${YELLOW}Warning: Go not found. Skipping Go implementation.${NC}"
        SKIP_GO=1
    fi
    
    # Check Rust
    if ! command -v cargo &> /dev/null; then
        echo -e "${YELLOW}Warning: Rust/Cargo not found. Skipping Rust implementation.${NC}"
        SKIP_RUST=1
    fi
}

# Main execution
main() {
    check_dependencies
    
    # Start implementations
    if [ -z "$SKIP_NODE" ]; then
        check_port 3000 && start_nodejs
        sleep 2
    fi
    
    if [ -z "$SKIP_PYTHON" ]; then
        check_port 8000 && start_python
        sleep 2
    fi
    
    if [ -z "$SKIP_PHP" ]; then
        check_port 8080 && start_php
        sleep 2
    fi
    
    if [ -z "$SKIP_GO" ]; then
        check_port 8081 && start_go
        sleep 2
    fi
    
    if [ -z "$SKIP_RUST" ]; then
        check_port 8082 && start_rust
        sleep 2
    fi
    
    echo ""
    echo "================================================="
    echo -e "${GREEN}All available servers started!${NC}"
    echo ""
    echo "API Endpoints:"
    [ -z "$SKIP_NODE" ] && echo "  Node.js:  http://localhost:3000/api/v1/tracking/{trackingNumber}"
    [ -z "$SKIP_PYTHON" ] && echo "  Python:   http://localhost:8000/api/v1/tracking/{trackingNumber}"
    [ -z "$SKIP_PHP" ] && echo "  PHP:      http://localhost:8080/api/v1/tracking/{trackingNumber}"
    [ -z "$SKIP_GO" ] && echo "  Go:       http://localhost:8081/api/v1/tracking/{trackingNumber}"
    [ -z "$SKIP_RUST" ] && echo "  Rust:     http://localhost:8082/api/v1/tracking/{trackingNumber}"
    echo ""
    echo "Test with: curl http://localhost:3000/api/v1/tracking/1Z999AA1234567890"
    echo ""
    echo "Press Ctrl+C to stop all servers"
    
    # Wait for user interrupt
    trap 'echo -e "\n${YELLOW}Stopping all servers...${NC}"; kill $(jobs -p) 2>/dev/null; exit' INT
    wait
}

# Run main function
main
