#!/bin/bash
DIR="$(cd "$(dirname "$0")" && pwd)"
echo "=============================="
echo "  SmartUjenzi - PHP Edition"
echo "=============================="
echo ""
echo "Server: http://localhost:8000"
echo "  admin@example.com / admin123"
echo ""

cd "$DIR" && php -d "extension=pdo_mysql" -S localhost:8000
