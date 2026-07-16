# 🎓 SICALWEB - Career Center & Tracer Study Alumni

SICALWEB adalah Sistem Informasi Career Center dan Tracer Study Alumni Universitas Sari Mutiara Indonesia yang dibangun menggunakan PHP Native dan MySQL. Sistem ini bertujuan membantu pengelolaan data alumni, tracer study, lowongan kerja, serta penyajian laporan secara terintegrasi.

---

## 📌 Fitur Utama

### 👨‍💼 Admin
- Dashboard Admin
- Kelola Data Alumni
- Kelola Lowongan Kerja
- Kelola Pengaturan Website
- Laporan Pra Tracer
- Laporan Tracer Study
- Laporan Survey Pengguna

### 🎓 Alumni
- Melihat Lowongan Kerja
- Mencari Direktori Alumni
- Mengisi Pra Tracer Study
- Mengisi Tracer Study
- Mengisi Survey Pengguna

### 📊 Laporan
- Statistik Alumni
- Statistik Tracer Study
- Statistik Survey Pengguna
- Grafik Laporan

---

## 🛠️ Teknologi yang Digunakan

- PHP Native
- MySQL
- Bootstrap 5
- HTML5
- CSS3
- JavaScript
- Chart.js
- Font Awesome
- Cloudinary (Video Profil)
- Git & GitHub

---

## 📂 Struktur Project

```
sicalweb/
│
├── admin/
├── api/
├── assets/
│   ├── css/
│   └── images/
├── auth/
├── config/
├── includes/
├── pages/
│
├── index.php
├── style.css
└── README.md
```

---

## 💻 Cara Menjalankan Project

### 1. Clone Repository

```bash
git clone https://github.com/Destin2304/sicalweb.git
```

### 2. Pindahkan Project

Letakkan folder ke dalam

```
xampp/htdocs/
```

### 3. Import Database

Import file

```
db_career_alumni.sql
```

menggunakan phpMyAdmin.

### 4. Konfigurasi Database

Edit file

```
config/config.php
```

Sesuaikan:

```php
$host = "localhost";
$dbname = "db_career_alumni";
$username = "root";
$password = "";
```

### 5. Jalankan XAMPP

Aktifkan:

- Apache
- MySQL

Kemudian buka:

```
http://localhost/sicalweb
```

---

## 👨‍🎓 Dikembangkan Oleh

**Destinawati Hura**

Program Studi Sistem Informasi

Universitas Sari Mutiara Indonesia

---

## 📄 Lisensi

Project ini dibuat sebagai tugas akademik pada Program Studi Sistem Informasi Universitas Sari Mutiara Indonesia.

---

## 📷 Tampilan Sistem

Halaman Utama
- Hero Section
- Video Profil USM Indonesia
- Lowongan Kerja
- Direktori Alumni

Dashboard Admin
- Statistik Alumni
- Statistik Tracer Study
- Statistik Survey Pengguna
- Kelola Data

---

## 🚀 Versi

Versi : **1.0**

Status : ✅ Selesai