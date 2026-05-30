#!/bin/bash
# Remote Server Setup Script
# Run this on your local machine to connect to the production server

echo "======================================"
echo "Keyword Orchestra - Remote Setup"
echo "======================================"

echo ""
echo "Step 1: Install Python dependencies"
pip install -r requirements.txt

echo ""
echo "Step 2: Configure API URL and Token"
echo "Enter your server domain (e.g., https://yourdomain.com):"
read SERVER_URL

echo "Enter your API token:"
read API_TOKEN

echo ""
echo "Creating configuration..."

# Create config for remote
cat > config_remote.py << EOF
API_BASE_URL = "$SERVER_URL"
API_TOKEN = "$API_TOKEN"
EOF

echo "✓ Configuration created!"
echo ""
echo "To run the orchestrator with remote server:"
echo "  python main.py --url $SERVER_URL --token $API_TOKEN run"
echo ""
echo "Or edit .env file and run:"
echo "  python main.py run"