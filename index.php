<?php
session_start();
require_once 'config/config.php';

// Get recent jobs
try {
    $stmt = $pdo->query("SELECT * FROM lowongan ORDER BY tanggal_post DESC LIMIT 6");
    $recent_jobs = $stmt->fetchAll();
} catch (Exception $e) {
    $recent_jobs = [];
}

// Get alumni count
try {
    $total_alumni = $pdo->query("SELECT COUNT(*) as total FROM alumni")->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $total_alumni = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USM Indonesia - Career Center Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 900;
            font-size: 18px;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand img {
            height: 45px;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 600;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .navbar-nav .nav-link:hover {
            color: #ffd700 !important;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            font-weight: 700;
            border-radius: 8px;
            padding: 8px 25px !important;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
       /* Hero Section */
            .hero{
                position: relative;
                overflow: hidden;
                min-height: 650px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                background: #1e3c72;
            }
            /* Video Background */
            .hero-video{
                position:absolute;
                top:0;
                left:0;
                width:100%;
                height:100%;
                object-fit:cover;
                z-index:1;
            }
            /* Lapisan biru transparan */
            .hero-overlay{
                position:absolute;
                inset:0;
                background:rgba(20,40,90,.60);
                z-index:2;
            }

            /* Konten */
            .hero-content{
                position:relative;
                z-index:3;
                text-align:center;
                width:100%;
                max-width:900px;
                padding:80px 20px;
            }

            /* HAPUS efek gelombang */
            .hero::before{
                display:none;
            }
            .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            padding: 10px 15px;
        }

        .search-box button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-box button:hover {
            transform: translateY(-2px);
        }

        .badge-info {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 20px;
        }

        .badge-info i {
            font-size: 16px;
        }

        /* Stats Section */
        .stats {
            background: white;
            padding: 60px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 36px;
            font-weight: 900;
            color: #667eea;
            margin-bottom: 8px;
        }

        .stat-item p {
            color: #666;
            font-weight: 600;
            font-size: 15px;
        }

        /* Jobs Section */
        .jobs-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .section-title {
            font-size: 36px;
            font-weight: 900;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            padding-bottom: 20px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        .job-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .job-title {
            font-size: 18px;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .job-company {
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
        }

        .job-type {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .job-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 15px 0;
            font-size: 14px;
            color: #666;
        }

        .job-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-info-item i {
            color: #667eea;
            width: 16px;
        }

        .btn-detail {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px;
            border-radius: 12px;
            text-align: center;
            margin: 60px 0;
        }

        .cta-section h2 {
            font-size: 32px;
            font-weight: 900;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-cta-primary {
            background: white;
            color: #667eea;
        }

        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-cta-secondary {
            background: transparent;
            color: white;
        }

        .btn-cta-secondary:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Footer */
        .footer {
            background: #1e3c72;
            color: white;
            padding: 40px 0 20px;
        }

        .footer h5 {
            font-weight: 800;
            margin-bottom: 20px;
        }

        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer a:hover {
            color: white;
            padding-left: 5px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 16px;
            }

            .section-title {
                font-size: 28px;
            }

            .search-box {
                flex-direction: column;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .btn-cta {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/LOGO_USM.png" alt="Logo" onerror="this.src='data:image/svg+xml,<svg></svg>'">
                USM Indonesia
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/lowongan.php">Lowongan Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/search_alumni.php">Direktori Alumni</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-login ms-2" href="auth/login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="beranda">
    <video class="hero-video"
        autoplay
        muted
        loop
        playsinline
        preload="auto">

        <source src="https://res.cloudinary.com/yqi4mp07/video/upload/v1783869041/PROFIL_USM_wdyh11.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-logo">
            <img src="assets/images/LOGO_USM.png">
        </div>
        <h1>Jelajahi Karier & Terhubung Kembali</h1>
        <p>
            Portal resmi pengembangan karier dan jaringan alumni USM Indonesia.
            Temukan peluang kerja terbaik dan terhubung dengan sesama alumni.
        </p>
        <form class="search-box" action="pages/lowongan.php" method="GET">
            <input type="text" name="search" placeholder="Cari posisi atau perusahaan...">
            <button type="submit">
                <i class="fas fa-search me-2"></i>Cari
            </button>
        </form>
        <div class="badge-info">
            <i class="fas fa-briefcase"></i>
            Bergabung bersama <?= $total_alumni ?> alumni lainnya
        </div>
    </div>
</section>

    <!-- Stats Section -->
    <section class="stats container-fluid">
        <div class="stat-item">
            <h3><?= $total_alumni ?>+</h3>
            <p>Alumni Terdaftar</p>
        </div>
        <div class="stat-item">
            <h3><?= count($recent_jobs) ?></h3>
            <p>Lowongan Aktif</p>
        </div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Terverifikasi</p>
        </div>
        <div class="stat-item">
            <h3>24/7</h3>
            <p>Dukungan Online</p>
        </div>
    </section>

    <!-- Jobs Section -->
    <section class="jobs-section">
        <div class="container">
            <h2 class="section-title">Lowongan Kerja Terbaru</h2>
            
            <?php if (!empty($recent_jobs)): ?>
                <div class="row">
                    <?php foreach ($recent_jobs as $job): ?>
                        <div class="col-md-6">
                            <div class="job-card">
                                <div class="job-header">
                                    <div>
                                        <h5 class="job-title"><?= htmlspecialchars($job['posisi'] ?? '-') ?></h5>
                                        <p class="job-company">
                                            <i class="fas fa-building me-1"></i>
                                            <?= htmlspecialchars($job['perusahaan'] ?? '-') ?>
                                        </p>
                                    </div>
                                    <span class="job-type">Full Time</span>
                                </div>
                                
                                <div class="job-info">
                                    <div class="job-info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($job['lokasi'] ?? '-') ?>
                                    </div>
                                    <div class="job-info-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <?= htmlspecialchars($job['gaji'] ?? 'Kompetitif') ?>
                                    </div>
                                    <div class="job-info-item">
                                        <i class="fas fa-calendar"></i>
                                        <?= isset($job['tanggal_post']) ? date('d M Y', strtotime($job['tanggal_post'])) : 'Baru' ?>
                                    </div>
                                </div>

                                <a href="pages/detail_lowongan.php?id=<?= $job['id_lowongan'] ?>" class="btn-detail">
                                    <i class="fas fa-arrow-right"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <a href="pages/lowongan.php" class="btn-detail" style="padding: 12px 35px;">
                        <i class="fas fa-list"></i>Lihat Semua Lowongan
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <p><strong>Belum ada lowongan kerja</strong></p>
                    <p>Silakan kembali lagi untuk melihat penawaran terbaru</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="container">
        <div class="cta-section">
            <h2>Bergabunglah dengan Ribuan Alumni USM</h2>
            <p>Dapatkan akses penuh ke laporan tracer study, survei, dan data alumni eksklusif lainnya</p>
            <div class="cta-buttons">
                <a href="auth/login.php" class="btn-cta btn-cta-primary">
                    <i class="fas fa-sign-in-alt"></i>Login Dashboard
                </a>
                <a href="pages/lowongan.php" class="btn-cta btn-cta-secondary">
                    <i class="fas fa-search"></i>Jelajahi Lowongan
                </a>
                <a href="pages/search_alumni.php" class="btn-cta btn-cta-secondary">
                    <i class="fas fa-users"></i>Cari Alumni
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Tentang USM</h5>
                    <p style="font-size: 14px; color: rgba(255,255,255,0.7);">
                        Universitas Sari Mutiara Indonesia adalah institusi pendidikan terkemuka yang berkomitmen menghasilkan lulusan berkualitas tinggi.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Tautan Penting</h5>
                    <a href="index.php">Beranda</a>
                    <a href="pages/lowongan.php">Lowongan Kerja</a>
                    <a href="pages/search_alumni.php">Direktori Alumni</a>
                    <a href="auth/login.php">Login Admin</a>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Hubungi Kami</h5>
                    <p style="font-size: 14px; color: rgba(255,255,255,0.7);">
                        <i class="fas fa-envelope me-2"></i>career@usm.ac.id<br>
                        <i class="fas fa-phone me-2"></i>+62 (081396421198)<br>
                        <i class="fas fa-map-marker-alt me-2"></i>Medan, Sumatera Utara
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Universitas Sari Mutiara Indonesia - Career Center Portal. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>