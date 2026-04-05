#!/bin/bash

set -e

echo "🔍 Synapse Browser Installer"
echo "=============================="

BROWSER_URL="https://im24wil27051.imporsgrunn.no/Synapse_browser/"
SEARX_BACKEND="https://synapse-searx.onrender.com"

echo ""
echo "📋 Checking prerequisites..."

if command -v docker &> /dev/null; then
    echo "✅ Docker is installed"
    DOCKER_AVAILABLE=true
else
    echo "❌ Docker is not installed"
    DOCKER_AVAILABLE=false
    echo ""
    echo "To install Docker, visit: https://www.docker.com/get-started"
    echo ""
fi

echo ""
echo "🌐 Opening Synapse Browser..."
echo "   URL: $BROWSER_URL"
echo ""

if command -v xdg-open &> /dev/null; then
    xdg-open "$BROWSER_URL"
elif command -v open &> /dev/null; then
    open "$BROWSER_URL"
elif command -v start &> /dev/null; then
    start "$BROWSER_URL"
else
    echo "Please open your browser and navigate to:"
    echo "$BROWSER_URL"
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "📝 Note: If search isn't working, ensure your PHP server has:"
echo "   - api.php with SEARX_URL pointing to: $SEARX_BACKEND"
echo ""
echo "To test the backend directly:"
echo "   curl \"$SEARX_BACKEND/search?q=test&format=json\""
echo ""
