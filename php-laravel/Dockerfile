FROM php:8.2-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY index.php .

# Create uploads and storage directories with proper permissions
RUN mkdir -p uploads/evidence && chmod 777 uploads/evidence
RUN mkdir -p storage && chmod 777 storage

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:8080/health || exit 1

# Start the application
CMD ["php", "-S", "0.0.0.0:8080", "index.php"]
