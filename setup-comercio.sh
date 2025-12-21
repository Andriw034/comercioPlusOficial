#!/bin/bash

echo "🚀 Iniciando setup de ComercioPlus..."

# 1️⃣ Verificar PHP
if command -v php >/dev/null 2>&1; then
    echo "✅ PHP detectado: $(php -v | head -n 1)"
else
    echo "❌ PHP no está instalado. Por favor instálalo y reinicia el IDE."
    exit 1
fi

# 2️⃣ Verificar Composer
if command -v composer >/dev/null 2>&1; then
    echo "✅ Composer detectado: $(composer --version)"
else
    echo "❌ Composer no está instalado. Instálalo para manejar dependencias de Laravel."
    exit 1
fi

# 3️⃣ Verificar Node.js
if command -v node >/dev/null 2>&1; then
    echo "✅ Node.js detectado: $(node -v)"
else
    echo "❌ Node.js no está instalado. Instálalo y reinicia el IDE."
    exit 1
fi

# 4️⃣ Verificar npm
if command -v npm >/dev/null 2>&1; then
    echo "✅ npm detectado: $(npm -v)"
else
    echo "❌ npm no está instalado. Instálalo y reinicia el IDE."
    exit 1
fi

echo "📦 Instalando dependencias de Laravel..."
composer install

echo "📦 Instalando dependencias de Node..."
npm install

echo "🛠 Ejecutando migraciones y seeders..."
php artisan migrate --seed

echo "⚡ Iniciando servidor de desarrollo..."
npm run dev

echo "✅ Setup completado. La aplicación debería estar funcionando."
