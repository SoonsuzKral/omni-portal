"""
Token Generator Script
Use this to generate an API token from your Laravel application
Run on server: php artisan tinker -> echo \App\Models\User::first()->createToken('orchestra')->plainTextToken;
"""

import os

def create_env_file():
    env_content = """# API Configuration
API_BASE_URL=http://localhost:8000
API_TOKEN=YOUR_TOKEN_HERE
"""
    with open(".env", "w") as f:
        f.write(env_content)
    print("✓ Created .env file")
    print("  Edit it and add your API token")

if __name__ == "__main__":
    create_env_file()