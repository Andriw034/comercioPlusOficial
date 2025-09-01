#!/bin/bash

# Build and start the containers
docker-compose up -d --build

# Wait for the app container to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Generate application key if not set
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate --force

# Run the tests
echo "Running Laravel tests..."
docker-compose exec app php artisan test

# Stop the containers
docker-compose down
