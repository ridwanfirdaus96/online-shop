# 🛒 Shop Online - E-Commerce PHP Native

Website toko online lengkap yang dibangun dengan **PHP Native** dan **MySQL**.

## ✨ Fitur

### Frontend
- 🏠 Homepage dengan hero section dan featured products
- 📦 Katalog produk dengan filter kategori dan pencarian
- 🔍 Detail produk lengkap
- 🛒 Shopping cart dengan update quantity
- 💳 Checkout dengan form pengiriman
- 🔐 Login & Register user

### Admin Panel
- 📊 Dashboard dengan statistik
- ➕ Tambah, edit, hapus produk
- 📋 Kelola pesanan dengan update status
- 🔒 Protected dengan authentication

## 🚀 Cara Install

### 1. Persiapan
Pastikan sudah terinstall:
- XAMPP / Laragon / WAMP
- PHP 7.4+
- MySQL / MariaDB

### 2. Clone / Download Project
```bash
# Clone atau download project ke folder htdocs
# Contoh: C:\xampp\htdocs\shop-online
```

### 3. Import Database
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru bernama `shop_online`
3. Import file `database.sql`

### 4. Konfigurasi Database
Edit file `config/database.php` jika perlu:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shop_online');
```

### 5. Jalankan Project
Buka browser dan akses:
```
http://localhost/shop-online
```

## 👤 Akun Demo

**Admin:**
- Email: `admin@shop.com`
- Password: `admin123`

## 📁 Struktur Folder

```
shop-online/
├── admin/              # Admin panel
├── assets/             # CSS & JS
├── config/             # Database config
├── includes/           # Header, footer, functions
├── uploads/            # Upload folder
├── index.php           # Homepage
├── products.php        # Product listing
├── product-detail.php  # Product detail
├── cart.php            # Shopping cart
├── checkout.php        # Checkout
├── login.php           # Login
├── register.php        # Register
└── database.sql        # Database schema
```

## 🎨 Teknologi

- **Backend:** PHP Native
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Styling:** Custom CSS dengan gradient modern

## 📝 Catatan

- Project ini menggunakan **PDO** untuk database connection
- Password di-hash menggunakan **password_hash()**
- Session-based cart (tidak perlu login untuk belanja)
- Responsive design untuk mobile & desktop

## 🔧 Troubleshooting

**Error: Database connection failed**
- Pastikan MySQL sudah running
- Cek konfigurasi di `config/database.php`
- Pastikan database `shop_online` sudah dibuat

**Error: Cannot find file**
- Pastikan project ada di folder `htdocs`
- Cek path di browser: `http://localhost/shop-online`

## 📄 License

Free to use for learning purposes.

---

**Dibuat dengan ❤️ menggunakan PHP Native**
