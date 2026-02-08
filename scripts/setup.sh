#!/bin/bash
## Kaify Quick Start Script
## Usage: curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/setup.sh | bash

set -e
set -o pipefail

echo ""
echo "=========================================="
echo "   Kaify Quick Start"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run as root."
    echo ""
    echo "Please run:"
    echo "  curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/setup.sh | sudo bash"
    exit 1
fi

# Check minimum requirements
echo "Checking prerequisites..."

if ! command -v curl >/dev/null 2>&1; then
    echo "ERROR: curl is required but not installed."
    echo "Please install curl and try again."
    exit 1
fi

echo " - curl: OK"
echo ""

# Run the full installer
echo "Starting Kaify installation..."
echo ""

curl -fsSL https://raw.githubusercontent.com/pranesh-rpo/kaify/main/scripts/install.sh | bash

echo ""
echo "=========================================="
echo "   Kaify is ready!"
echo "=========================================="
echo ""
echo "Access your dashboard at: http://$(curl -4s --max-time 5 https://ifconfig.io 2>/dev/null || echo 'your-server-ip'):8000"
echo ""
