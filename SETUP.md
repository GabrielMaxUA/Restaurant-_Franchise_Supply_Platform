# Restaurant Franchise Supply Platform - Setup Guide

This comprehensive guide will walk you through setting up the Restaurant Franchise Supply Platform on your local development environment.

## Quick Start (TL;DR)

For experienced developers who want to get started quickly:

```bash
# 1. Clone and navigate
git clone [your-repository-url]
cd Restaurant-_Franchise_Supply_Platform/franchise-supply-platform

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
# Edit .env with your database credentials
php artisan key:generate

# 4. Setup database
mysql -u root -p -e "CREATE DATABASE franchise_supply_platform"
php artisan migrate
php artisan db:seed --class=ComprehensiveSeeder
php artisan storage:link

# 5. Run application
npm run build
php artisan serve
```

**Default Login:** admin@example.com / password123

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [System Requirements](#system-requirements)
3. [Installation Steps](#installation-steps)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [Third-Party Services Configuration](#third-party-services-configuration)
7. [Running the Application](#running-the-application)
8. [Default Login Credentials](#default-login-credentials)
9. [Troubleshooting](#troubleshooting)

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP 8.2 or higher**
- **Composer** (PHP dependency manager)
- **Node.js 18.x or higher** and npm
- **MySQL 8.0 or higher** (or MariaDB 10.3+)
- **XAMPP/MAMP/WAMP** (recommended for local development)
- **Git**

## System Requirements

### Server Requirements
- PHP >= 8.2
- MySQL >= 8.0
- Apache/Nginx web server
- Minimum 2GB RAM
- 500MB free disk space

### PHP Extensions Required
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- GD PHP Extension (for image processing)

## Installation Steps

### 1. Clone the Repository

```bash
git clone [your-repository-url]
cd Restaurant-_Franchise_Supply_Platform
```

### 2. Navigate to the Laravel Project Directory (placed to htdocs is xampp)

```bash
cd franchise-supply-platform
```

### 3. Install PHP Dependencies

```bash
composer install
```

### 4. Install Node.js Dependencies

```bash
npm install
```

### 5. Set File Permissions (Linux/Mac)

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Environment Configuration

### 1. Create Environment File

Copy the provided environment configuration:

```bash
cp .env.example .env
```

### 2. Environment Variables Configuration

Replace the contents of your `.env` file with the following configuration:

```env
# Application Configuration
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:WbuOdJeo5HouhwsUY2H3PXooKKfmvIWW7QuIkapd7fQ=
APP_DEBUG=true
APP_URL=http://localhost/Restaurant-_Franchise_Supply_Platform/franchise-supply-platform/public

# Localization
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Maintenance
APP_MAINTENANCE_DRIVER=file

# Performance
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=franchise_supply_platform
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=15
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Cache and Queue
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_DRIVER=file
CACHE_STORE=file

# Redis (Optional)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Email Configuration (SendGrid)
# MAIL_MAILER=log 
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="maxim.don.mg@gmail.com" 
MAIL_FROM_NAME="Restaurant Franchise Supply"

# SendGrid API Key (Alternative method)
SENDGRID_API_KEY=your_sendgrid_api_key

# JWT Configuration
JWT_SECRET=n6OSPwgkKQmTVOKfKO30zyeCUAfL0caTb6AGxXglv51jOw5mGnMslEg3LvKpv9HB
JWT_TTL=30
JWT_REFRESH_TTL=1440
JWT_BLACKLIST_GRACE_PERIOD=30

# Firebase Configuration
FIREBASE_PROJECT_ID=franchiseemobile-a2ea6
FIREBASE_CREDENTIALS_PATH=/path/to/your/project/storage/app/firebase/service-account.json

# Twilio Configuration (SMS and WhatsApp)
TWILIO_ENABLED=false
TWILIO_ACCOUNT_SID=your_twilio_account_sid_here
TWILIO_AUTH_TOKEN=your_twilio_auth_token_here
TWILIO_SMS_FROM=+14168560684
TWILIO_WHATSAPP_FROM=+14168560684
TWILIO_WHATSAPP_ENABLED=false

# Deep Linking Configuration
APP_DEEP_LINK_SCHEME=restaurantfranchise
APP_IOS_STORE_URL=#
APP_ANDROID_STORE_URL=#

# AWS Configuration (if using S3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Vite Configuration
VITE_APP_NAME="${APP_NAME}"
```

### 3. Update Environment Variables

**IMPORTANT:** Update the following variables with your actual values:

- `APP_URL`: Update to match your local server URL
- `DB_USERNAME`: Your MySQL username (typically 'root' for XAMPP)
- `DB_PASSWORD`: Your MySQL password
- `FIREBASE_CREDENTIALS_PATH`: Update the path to match your system

## Database Setup

You have two options for setting up the database:

### Option 1: Using Laravel Migrations (Recommended for Development)

#### 1. Create Database

Using phpMyAdmin or MySQL command line:

```sql
CREATE DATABASE franchise_supply_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 2. Generate Application Key

```bash
php artisan key:generate
```

#### 3. Run Migrations

This will create all tables with proper relationships and indexes:

```bash
php artisan migrate
```

#### 4. Seed Database with Sample Data

This creates sample users, categories, and products:

```bash
php artisan db:seed --class=ComprehensiveSeeder
```

#### 5. Create Storage Links

```bash
php artisan storage:link
```

### Option 2: Import Existing Database (For Production/Full Data)

If you want to use the complete database with existing data:

#### 1. Create Database

```sql
CREATE DATABASE franchise_supply_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 2. Import SQL File

```bash
mysql -u your_username -p franchise_supply_platform < franchise_supply_platform.sql
```

#### 3. Generate Application Key

```bash
php artisan key:generate
```

#### 4. Create Storage Links

```bash
php artisan storage:link
```

#### 5. Run Additional Migrations (if any)

Check for any newer migrations:

```bash
php artisan migrate
```

### Database Structure Overview

The database includes the following main tables:

- **users** - User accounts (admin, warehouse, franchisee)
- **roles** - User role definitions
- **admin_details** - Admin profile information
- **franchisee_details** - Franchisee profile information
- **categories** - Product categories
- **products** - Product catalog
- **product_variants** - Product variations (sizes, types)
- **product_images** - Product image storage
- **variant_images** - Product variant image storage
- **carts** - Shopping carts
- **cart_items** - Cart contents
- **orders** - Order records
- **order_items** - Order line items
- **order_notifications** - Notification tracking
- **product_favorites** - User favorite products
- **personal_access_tokens** - API authentication tokens

## Third-Party Services Configuration

### 1. Firebase Setup

**File Location:** `storage/app/firebase/service-account.json`

Create the Firebase service account file with the following content:

```json
{
  "type": "service_account",
  "project_id": "your-firebase-project-id",
  "private_key_id": "your-private-key-id",
  "private_key": "-----BEGIN PRIVATE KEY-----\n[your-private-key-content]\n-----END PRIVATE KEY-----\n",
  "client_email": "your-service-account@your-project.iam.gserviceaccount.com",
  "client_id": "your-client-id",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/your-service-account-email%40your-project.iam.gserviceaccount.com",
  "universe_domain": "googleapis.com"
}
```

**Note:** Replace all placeholder values with your actual Firebase service account credentials. Get these from your Firebase Console > Project Settings > Service Accounts.

### 2. SendGrid Email Service

The application is configured to use SendGrid for email notifications. You need to configure your own SendGrid API key:

- **API Key**: Get from your SendGrid dashboard
- **From Email**: Your verified sender email
- **From Name**: `Restaurant Franchise Supply`

**To get your SendGrid API key:**
1. Sign up at [sendgrid.com](https://sendgrid.com)
2. Go to Settings > API Keys
3. Create a new API key with "Mail Send" permissions
4. Replace `your_sendgrid_api_key_here` in your `.env` file

### 3. Twilio Configuration (Optional)

If you want to enable SMS/WhatsApp notifications, update these variables in `.env`:

```env
TWILIO_ENABLED=true
TWILIO_ACCOUNT_SID=your_actual_twilio_account_sid
TWILIO_AUTH_TOKEN=your_actual_twilio_auth_token
TWILIO_WHATSAPP_ENABLED=true
```

## Running the Application

### 1. Build Frontend Assets

```bash
npm run build
```

For development with hot reloading:

```bash
npm run dev
```

### 2. Start the Laravel Development Server

```bash
php artisan serve
```

### 3. Start Queue Worker (for background jobs)

In a separate terminal:

```bash
php artisan queue:work
```

### 4. Access the Application

- **Web Application**: `http://localhost:8000` (or your configured URL)
- **API Base URL**: `http://localhost:8000/api`

## Default Login Credentials

### If Using Laravel Migrations with Seeder (Option 1):

After running the `ComprehensiveSeeder`, you can use these credentials:

**Admin Account:**
- Email: `admin@example.com`
- Password: `password123`
- Role: Administrator

**Warehouse Manager Account:**
- Email: `warehouse@example.com`
- Password: `warehouse123`
- Role: Warehouse Staff

**Franchisee Account:**
- Email: `franchisee@example.com`
- Password: `franchisee123`
- Role: Franchisee

### If Using SQL Import (Option 2):

**Admin Account:**
- Username: `admin`
- Email: `admin@example.com`
- Password: `password` (default Laravel hash)
- Role: Administrator

**Warehouse Account:**
- Username: `maximUSCan`
- Email: `maxim.don.mg@gmail.com2`
- Role: Warehouse Staff

**Franchisee Account:**
- Username: `gabriel max`
- Email: `user@franchisee.com`
- Role: Franchisee

### Creating Additional Users

You can create additional users via the admin panel or using Artisan Tinker:

```bash
php artisan tinker
```

Then execute:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = new User();
$user->username = 'new_user';
$user->password_hash = Hash::make('your_password');
$user->email = 'user@example.com';
$user->role_id = 3; // 1=admin, 2=warehouse, 3=franchisee
$user->status = 1;
$user->save();
```

## User Roles

The system supports three main user types:

1. **Admin** (role_id: 1) - Full system access
2. **Warehouse Staff** (role_id: 2) - Order processing and inventory management
3. **Franchisee** (role_id: 3) - Order placement and tracking

## Directory Structure

### Important Directories

- **Storage**: `storage/app/` - File uploads and private files
  - `storage/app/firebase/` - Firebase service account credentials
  - `storage/app/public/` - Public file uploads (product images, logos)
  - `storage/app/invoices/` - Generated invoice PDFs

- **Public**: `public/` - Web accessible files
  - `public/storage/` - Symlinked storage directory
  - `public/images/` - Static application images

- **Configuration**: `config/` - Application configuration files
  - `config/database.php` - Database configuration
  - `config/mail.php` - Email configuration
  - `config/jwt.php` - JWT authentication configuration

## API Endpoints

The application provides RESTful API endpoints for mobile app integration:

- **Authentication**: `/api/auth/*`
- **Products**: `/api/products/*`
- **Orders**: `/api/orders/*`
- **Users**: `/api/users/*`

API documentation can be generated using tools like Swagger/OpenAPI.

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify MySQL is running
   - Check database credentials in `.env`
   - Ensure database exists

2. **Storage Permission Issues** (Linux/Mac)
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

3. **Composer/NPM Issues**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install --production
   ```

4. **Key Generation Error**
   ```bash
   php artisan key:generate --force
   ```

5. **Migration Issues**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Database Migration Verification**
   ```bash
   # Check migration status
   php artisan migrate:status
   
   # Verify database tables exist
   php artisan db:show
   
   # Test with Tinker
   php artisan tinker
   # Then run: User::count(); Product::count(); exit
   ```

7. **User Model Password Field Issues**
   If authentication fails, ensure the User model uses `password_hash` field:
   ```bash
   # Check app/Models/User.php
   # Update $fillable to include 'password_hash' instead of 'password'
   ```

### Log Files

Check these log files for debugging:

- Laravel Logs: `storage/logs/laravel.log`
- Web Server Logs: Check your XAMPP/Apache logs
- MySQL Logs: Check MySQL error logs

### Performance Optimization

For production deployment:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Development Commands

### Useful Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Generate IDE helper files
php artisan ide-helper:generate

# Check application status
php artisan about
```

### Development Workflow

```bash
# Start all development services
composer run dev
```

This command starts:
- Laravel development server
- Queue worker
- Log viewer
- Vite development server with hot reloading

## Security Notes

1. **Change Default Credentials**: Update all default passwords and API keys for production
2. **Environment Variables**: Never commit `.env` files to version control
3. **File Permissions**: Ensure proper file permissions on storage directories
4. **HTTPS**: Use HTTPS in production environments
5. **Database Security**: Use strong database passwords and limit access

## Support

For technical support or questions:

1. Check the application logs
2. Review Laravel documentation: https://laravel.com/docs
3. Check the project's issue tracker
4. Contact the development team

---

**Note**: This setup guide assumes a local development environment. Production deployment may require additional configuration for web servers, SSL certificates, and security hardening.