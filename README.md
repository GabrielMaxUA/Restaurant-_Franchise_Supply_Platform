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
•⁠  ⁠PHP-based dashboard (Laravel preferred)  - DONE 
•⁠  ⁠User management, product inventory, order approval  - DONE 
•⁠  ⁠API layer for mobile app

---

### 4. Technical Requirements
•⁠  ⁠*Backend:* PHP (Laravel)  - DONE 
•⁠  ⁠*Frontend Mobile:* React Native
•⁠  ⁠*Database:* MySQL  - DONE 
•⁠  ⁠*Notifications:* Firebase Push + SMTP for email
•⁠  ⁠*Authentication:* JWT or Firebase Auth
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
