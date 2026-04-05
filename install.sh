#!/bin/bash

set -e

BROWSER_URL="http://localhost:3000"
INSTALL_DIR="${HOME}/Synapse-browser"
REPO_URL="https://github.com/sneakyturtle270508/Synapse_browser.git"

check_docker() {
    if command -v docker &> /dev/null && docker info &> /dev/null; then
        return 0
    fi
    return 1
}

install_docker() {
    echo "Docker is required. Installing..."
    case "$(uname -s)" in
        Darwin*)
            if command -v brew &> /dev/null; then
                echo "Installing Docker Desktop via Homebrew..."
                brew install --cask docker
            else
                echo "Please install Docker Desktop from https://docs.docker.com/desktop/install/mac-install/"
                return 1
            fi
            echo "Docker Desktop installed. Please start it from Applications and press Enter when ready."
            read -p ""
            ;;
        Linux*)
            echo "Installing Docker via official script..."
            curl -fsSL https://get.docker.com | sh
            sudo usermod -aG docker $USER
            echo "Docker installed. You may need to log out and back in, or run: newgrp docker"
            ;;
        *)
            echo "Unsupported OS. Please install Docker manually."
            return 1
            ;;
    esac
}

clone_or_pull() {
    if [ -d "$INSTALL_DIR/.git" ]; then
        echo "Updating Synapse Browser..."
        cd "$INSTALL_DIR" && git pull
    else
        echo "Cloning Synapse Browser..."
        git clone "$REPO_URL" "$INSTALL_DIR"
    fi
}

start_services() {
    cd "$INSTALL_DIR"
    
    if docker compose ps &> /dev/null || docker-compose ps &> /dev/null; then
        echo "Restarting containers..."
        docker compose restart 2>/dev/null || docker-compose restart
    else
        echo "Starting containers..."
        docker compose up -d 2>/dev/null || docker-compose up -d
    fi
    
    echo "Waiting for services to be ready..."
    for i in {1..30}; do
        if curl -fsSL -o /dev/null http://localhost:3000 2>/dev/null; then
            echo "Services are ready!"
            return 0
        fi
        echo "Waiting... ($i/30)"
        sleep 2
    done
    
    echo "Services may still be starting. Check with: docker compose logs"
    return 0
}

open_browser() {
    echo "Opening Synapse Browser..."
    if command -v open &> /dev/null; then
        open "$BROWSER_URL"
    elif command -v xdg-open &> /dev/null; then
        xdg-open "$BROWSER_URL"
    elif command -v start &> /dev/null; then
        start "$BROWSER_URL"
    else
        echo "Please open your browser at: $BROWSER_URL"
    fi
}

main() {
    echo "Synapse Browser Installer"
    echo "=========================="
    echo ""
    
    if ! check_docker; then
        echo "Docker is not installed or not running."
        read -p "Install Docker now? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            install_docker
        else
            echo "Cannot continue without Docker."
            exit 1
        fi
    fi
    
    if ! command -v git &> /dev/null; then
        echo "Git is required. Installing..."
        case "$(uname -s)" in
            Darwin*) brew install git ;;
            Linux*) sudo apt-get update && sudo apt-get install -y git ;;
        esac
    fi
    
    clone_or_pull
    start_services
    open_browser
    
    echo ""
    echo "Done! Synapse Browser is running at $BROWSER_URL"
    echo "To stop: cd $INSTALL_DIR && docker compose down"
}

main "$@"
