#!/bin/bash
set -e

echo "=== PRAXXYS Demo Platform — Server Setup ==="
echo "Server: demo.praxxystech.com"
echo ""

# 1. Install dependencies
apt-get update -q
apt-get install -y nginx php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-tokenizer \
    mysql-server certbot python3-certbot-nginx git unzip curl

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# 2. Clone application
echo "Enter the Git repository URL:"
read REPO_URL
git clone "$REPO_URL" /var/www/demo
cd /var/www/demo

# 3. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 4. Install and build JS
npm ci
npm run build

# 5. Configure environment
cp .env.example .env
php artisan key:generate

echo ""
echo ">>> Please edit /var/www/demo/.env:"
echo "    APP_URL=https://demo.praxxystech.com"
echo "    DB_DATABASE=demo_platform"
echo "    DB_USERNAME=demouser"
echo "    DB_PASSWORD=<strong password>"
echo ""
read -p "Press Enter when .env is configured..."

# 6. Set up MySQL
DB_PASS=$(grep ^DB_PASSWORD /var/www/demo/.env | head -1 | cut -d= -f2)
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS demo_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'demouser'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON demo_platform.* TO 'demouser'@'localhost';
FLUSH PRIVILEGES;
SQL

# 7. Run migrations and seed
php artisan migrate --seed --force

# 8. Create directories and set permissions
mkdir -p /var/www/demo/public/projects
mkdir -p /var/www/demo/storage/htpasswd
chown -R www-data:www-data /var/www/demo
chmod -R 755 /var/www/demo/storage /var/www/demo/public/projects

# 9. Configure NGINX
cp /var/www/demo/deploy/nginx.conf /etc/nginx/sites-available/demo.praxxystech.com
ln -sf /etc/nginx/sites-available/demo.praxxystech.com /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# 10. Obtain SSL certificate
certbot --nginx -d demo.praxxystech.com --non-interactive --agree-tos -m devops@praxxys.ph

echo ""
echo "=== Setup Complete ==="
echo "Admin login: admin@praxxys.ph / admin1234"
echo "IMPORTANT: Change the admin password immediately at https://demo.praxxystech.com/profile"
