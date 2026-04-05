#!/bin/bash

# Synapse Browser Installer
# Run this script to install and launch Synapse Browser

set -e

BROWSER_URL="${BROWSER_URL:-https://synapse-browser.onrender.com}"
SEARX_URL="${SEARX_URL:-https://synapse-searx.onrender.com}"

echo "🔍 Synapse Browser Installer"
echo "=============================="

# Check if Docker is installed
check_docker() {
    if command -v docker &> /dev/null; then
        echo "✅ Docker is installed"
        return 0
    else
        echo "❌ Docker is not installed"
        return 1
    fi
}

# Install Docker (macOS)
install_docker_mac() {
    echo "📦 Installing Docker for macOS..."
    if command -v brew &> /dev/null; then
        brew install --cask docker
    else
        echo "Please install Docker Desktop from https://docker.com"
        return 1
    fi
}

# Install Docker (Linux)
install_docker_linux() {
    echo "📦 Installing Docker for Linux..."
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker $USER
    echo "Please log out and back in, or run: newgrp docker"
}

# Start services (optional - for local SearXNG)
start_local_searx() {
    echo "🚀 Starting local SearXNG..."
    docker run -d --name synapse-searx \
        -p 8080:8080 \
        -e BASE_URL=http://localhost:8080 \
        -e SEARXNG_LIMITER=false \
        searxng/searxng:latest
}

# Check services and open browser
launch_browser() {
    echo "🌐 Checking services..."
    
    # Check if browser URL is accessible
    if curl -fsSL -o /dev/null -s -w "%{http_code}" "$BROWSER_URL" | grep -q "200"; then
        echo "✅ Browser service is running at: $BROWSER_URL"
        BROWSER_READY=true
    else
        echo "⚠️ Browser service not accessible: $BROWSER_URL"
        BROWSER_READY=false
    fi
    
    # Check SearXNG
    if curl -fsSL -o /dev/null -s -w "%{http_code}" "$SEARX_URL/search?q=test&format=json" | grep -q "200"; then
        echo "✅ Search backend is running at: $SEARX_URL"
        SEARX_READY=true
    else
        echo "⚠️ Search backend not accessible: $SEARX_URL"
        SEARX_READY=false
    fi
    
    echo ""
    if [ "$BROWSER_READY" = true ]; then
        echo "🚀 Opening Synapse Browser..."
        if command -v open &> /dev/null; then
            open "$BROWSER_URL"
        elif command -v xdg-open &> /dev/null; then
            xdg-open "$BROWSER_URL"
        elif command -v start &> /dev/null; then
            start "$BROWSER_URL"
        else
            echo "Please open your browser at: $BROWSER_URL"
        fi
        echo "✅ Done! Synapse Browser should now be open in your browser."
    else
        echo "❌ Services not ready. Please check your connection."
        exit 1
    fi
}

# Main
main() {
    # Check for --skip-docker flag
    SKIP_DOCKER=false
    for arg in "$@"; do
        if [ "$arg" = "--skip-docker" ]; then
            SKIP_DOCKER=true
        fi
    done
    
    if [ "$SKIP_DOCKER" = false ]; then
        if ! check_docker; then
            echo ""
            read -p "Install Docker now? (y/n): " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                case "$(uname -s)" in
                    Darwin*) install_docker_mac ;;
                    Linux*) install_docker_linux ;;
                    *) echo "Unsupported OS"; exit 1 ;;
                esac
            else
                echo "Running without local Docker..."
            fi
        fi
    fi
    
    launch_browser
}

main "$@"
