# Restaurant-_Franchise_Supply_Platform
*Product Requirements Document (PRD)*

*Project Title:* Restaurant Franchise Supply Ordering Platform

*Prepared For:* Internal Product/Development Team

*Prepared By:* Manu Mayank

*Last Updated:* May 1, 2025

---

### 1. Overview
This platform will enable restaurant franchisors to manage their warehouse inventory and allow their franchises to place supply orders through a mobile application. The system will include a web backend for the franchisor (admin and warehouse staff) and a mobile app for the franchisees.

### 2. Objectives
•⁠  ⁠Streamline ordering of supplies between franchisors and franchisees.
•⁠  ⁠Ensure only approved users access the platform with limited roles.
•⁠  ⁠Provide an efficient order approval and fulfillment process.
•⁠  ⁠Integrate with QuickBooks for automated invoice and customer syncing.

---

### 3. Key Features

#### 3.1 User Roles & Permissions
•⁠  ⁠*Admin (Web):*
  - Manage franchisee accounts (name, phone, email, login access)  - DONE 
  - Add/edit/delete products (with images, descriptions, variants) - DONE 
  - Approve or reject orders - DONE  
  - Manage inventory - DONE 
  - Trigger invoice and customer sync to QuickBooks

•⁠  ⁠*Warehouse Staff (Web):*
  - View approved orders 
  - Update order status (Packed/Shipped)

•⁠  ⁠*Franchisee (Mobile App):*
  - Login via email/password
  - Browse and search products
  - View product variants and details
  - Place supply orders
  - View order history (own orders only)
  - Get status updates via push/email

#### 3.2 Functional Modules
•⁠  ⁠*Product Catalog:*
  - Images, variants (e.g., size/packaging), cost, stock status - DONE  
•⁠  ⁠*Order Workflow:*
  - Order → Admin Approval → Warehouse Processing → Shipping
•⁠  ⁠*QuickBooks Integration:*
  - Push invoice and customer data post-approval
•⁠  ⁠*Notifications:*
  - Order submitted, approved, packed, shipped (push/email)
•⁠  ⁠*Inventory Tracking:*
  - Stock management per item  - DONE 

#### 3.3 Mobile App
•⁠  ⁠React Native-based, iOS + Android
•⁠  ⁠Secure login and access control
•⁠  ⁠Smooth product browsing and cart
•⁠  ⁠Status tracking for all orders

#### 3.4 Web Backend
•⁠  ⁠PHP-based dashboard (Laravel)  - DONE 
•⁠  ⁠User management, product inventory, order approval  - DONE 
•⁠  ⁠API layer for mobile app

---

### 4. Technical Requirements
•⁠  ⁠*Backend:* PHP (Laravel)  - DONE 
•⁠  ⁠*Frontend Mobile:* React Native

•⁠  ⁠*Database:* MySQL  - DONE 
•⁠  ⁠*Notifications:* Firebase Push + SMTP for email
•⁠  ⁠*Authentication:* JWT - DONE JWT
•⁠  ⁠*QuickBooks API:* Invoice and customer sync

---

### 5. Non-Functional Requirements
•⁠  ⁠Mobile responsiveness for web admin
•⁠  ⁠Secure data access (SSL, auth tokens)  - DONE 
•⁠  ⁠Cloud backups of database
•⁠  ⁠Error tracking and logging

---

### 6. Future Scope (V2)
•⁠  ⁠Payment gateway integration
•⁠  ⁠Analytics dashboard
•⁠  ⁠Franchise communication module

---

### 7. Timeline
*MVP Target Completion:* 6–8 weeks after development kickoff

---

### 8. Dependencies
•⁠  ⁠Access to QuickBooks API credentials
•⁠  ⁠Push/email notification setup (Firebase & SMTP)
•⁠  ⁠App store developer accounts

---

### 9. Stakeholders
•⁠  ⁠Product Owner: Manu Mayank
•⁠  ⁠Backend Developer: TBD
•⁠  ⁠Frontend Developer: TBD
•⁠  ⁠UI/UX Designer: TBD
•⁠  ⁠QA Tester: TBD



.env file code
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:xWrOAD+S6ENpyR3kfGXe/xGz+eMipT1oZ0sLZGUz/Kw=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
# DB_PORT=3306
DB_DATABASE=franchise_supply_platform
DB_USERNAME=YOUR_DB_NAME 
DB_PASSWORD=YOUR_DB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_DRIVER=file
CACHE_STORE=file


MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=3K6nr8VbuMDGsjva2ZW09quhjdRF9ZHxxZYdUilmbdqWH7KTNEduFrkw0EQWVSeO
