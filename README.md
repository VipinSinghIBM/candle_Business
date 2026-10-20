# 🕯️ SHIV VANI ENTERPRISES - Artisanal & Spiritual Candle Boutique

An iconic, luxury candle-selling e-commerce web platform crafted for **SHIV VANI ENTERPRISES** using pure **PHP** and **MySQL** (with automated zero-setup SQLite fallback).

---

## ✨ Features & User Requirements Included

### 1. Brand Identity Everywhere
* **Brand Name:** **SHIV VANI ENTERPRISES** is prominently featured across all pages (Navbar, Hero, Footer, Product Cards, Cart, Checkout, Order Invoice, and Admin Dashboard).
* **Brand Logo:** Authentic brand emblem (Trishul & Sacred Eye) located at `assets/images/logo.jpg` is seamlessly embedded with gold accents in the header, hero, footer, and invoice receipts.

### 2. Dynamic Categories in Navbar & Admin Control
* **Admin Category Module (`/admin/categories.php`):**
  * Create, edit, and delete candle categories.
  * Define category name, URL slug, icon, and display order.
* **Instant Dynamic Navbar Integration:** Any new category created in the admin panel instantly reflects in the **Public Navbar Dropdown**, the homepage category filter pills, the shop catalog filters, and the Product Add/Edit forms.

### 3. Two Prices & Discount Rate on Every Product
* **Clear 2 Prices Display:** Every candle card and product page clearly presents:
  * **Regular Price (M.R.P.):** Struck-through with muted text (e.g., `M.R.P: ₹ 899.00`).
  * **Discounted Price:** Prominent bold font (e.g., `₹ 674.25`).
  * **Discount Rate Badge:** Highlights exact percentage savings (e.g., `25% OFF`, `Save ₹ 224.75`).
* **Admin Rate Controls (`/admin/product-add.php` & `/admin/product-edit.php`):**
  * Admin can add, modify, or remove rates and discount percentages anytime.
  * Real-time JavaScript preview automatically computes final customer price.

### 4. Direct Image File Upload
* **Direct File Upload:** Admin can upload image files (JPG, PNG, WEBP) directly from their computer or mobile device.
* Images are stored in the `uploads/` directory and their path is saved directly in the database.
* Admin can also supply external image URLs or pick from high-res studio presets.

### 5. Customer Reviews & Satisfaction with YouTube Video Frames
* **Section on Homepage (`#reviews`):**
  * Overall satisfaction widget (4.9 ★ / 5.0 Rating based on verified patrons).
  * Interactive category tabs: **All Reviews**, **Written Testimonials**, **YouTube Video Reviews**.
  * **Uniform Card Dimensions:** Both text testimonials and YouTube video frames share the exact same card dimensions, borders, and rounded styling.
  * Video cards embed YouTube players (`<iframe>`) with customer unboxings, flame crackle audio, and burn experience alongside star rating marks.
* **Admin Review Management (`/admin/reviews.php`):**
  * Admin can publish new text reviews or YouTube video reviews, assign 1–5 stars, and delete reviews.

### 6. Admin Payment API & Gateway Settings (`/admin/payment-settings.php`)
* **UPI / QR Code Scan to Pay:**
  * Admin can toggle Scan on/off.
  * Set business UPI ID (e.g., `shivvani.enterprises@okaxis`).
  * Automatic high-resolution dynamic QR code generation embedded on checkout (`checkout.php`).
  * Upload custom merchant QR code image if preferred.
* **Credit / Debit Card Payment API:**
  * Admin can toggle Card payment on/off.
  * Enter Razorpay / Stripe API Key (`rzp_live_...`), Secret Key, and Merchant ID.
* **Cash on Delivery (COD) & Bank Transfer:**
  * Toggle COD or Bank Wire transfer and customize instructions.
* **Live Reflection on Checkout:** Whatever methods admin enables or disables immediately reflect on the checkout page.

---

## 🚀 How to Run the Website

### Option 1: Instant 1-Click Launch (Built-in PHP Server)
```powershell
cd C:\Users\hp\.gemini\antigravity\scratch\candle-boutique
php -S localhost:8000
```
Open your browser and visit:
* **Storefront (Customer View):** [http://localhost:8000](http://localhost:8000)
* **All Collections Catalog:** [http://localhost:8000/shop.php](http://localhost:8000/shop.php)
* **Admin Control Panel:** [http://localhost:8000/admin](http://localhost:8000/admin)

> **Admin Login Credentials:**
> * **Username:** `admin`
> * **Password:** `admin123`

---

### Option 2: Running with XAMPP / WampServer (MySQL)
1. Start **Apache** and **MySQL** in XAMPP.
2. In **phpMyAdmin** (`http://localhost/phpmyadmin`), import `database.sql`.
3. Copy folder to `C:\xampp\htdocs\candle-boutique`.
4. Open [http://localhost/candle-boutique](http://localhost/candle-boutique).

---

## 🏷️ Test Coupons Included
* `SHIVVANI15` — 15% off orders over ₹500
* `AURA20` — 20% off orders over ₹1000
* `WELCOME10` — 10% off any order
