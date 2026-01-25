#!/bin/bash

# Quick Start Frontend Script
# Run this to start your frontend development server

set -e

cd "$(dirname "$0")/../frontend"

echo "╔════════════════════════════════════════╗"
echo "║   Starting Frontend Dev Server        ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
    echo ""
fi

echo "🚀 Starting Vite dev server..."
echo ""
echo "Frontend will be available at: http://localhost:5173"
echo "Backend API endpoint: http://localhost:8080"
echo ""
echo "Press Ctrl+C to stop"
echo ""

npm run dev

