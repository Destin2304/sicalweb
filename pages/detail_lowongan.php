<?php
session_start();
require_once '../config/config.php';

// Get lowongan ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: lowongan.php");
    exit;
}

$lowongan = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM lowongan WHERE id_lowongan = ?");
    $stmt->execute([$id]);
    $lowongan = $stmt->fetch();
    
    if (!$lowongan) {
        header("Location: lowongan.php");
        exit;
    }
} catch (Exception $e) {
    header("Location: lowongan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lowongan['posisi'] ?? 'Lowongan Kerja') ?> - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; font-size: 18px; }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        .detail-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 40px; margin-bottom: 30px; }
        .header-section { border-bottom: 3px solid #667eea; padding-bottom: 30px; margin-bottom: 30px; }
        .posisi-title { font-size: 32px; font-weight: 800; color: #2c3e50; margin-bottom: 10px; }
        .perusahaan-name { font-size: 18px; color: #667eea; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .info-row { display: flex; align-items: center; margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .info-icon { width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 15px; font-size: 18px; }
        .info-content h6 { font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
        .info-content p { color: #666; margin: 0; }
        .badge-status { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .section-title { font-size: 20px; font-weight: 800; color: #2c3e50; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #667eea; }
        .section-content { line-height: 1.8; color: #444; font-size: 15px; white-space: pre-wrap; }
        .btn-back a { color: white; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 20px; border-radius: 8px; transition: all 0.3s; margin-bottom: 30px; }
        .btn-back a:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); color: white; }
        .action-buttons { display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap; }
        .btn-apply { flex: 1; min-width: 200px; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px; transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-apply:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3); color: white; text-decoration: none; }
        .btn-share { padding: 15px 30px; background: #f0f0f0; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-share:hover { background: #e0e0e0; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <img src="../assets/images/LOGO_USM.png" alt="Logo" onerror="this.src='data:image/svg+xml,<svg></svg>'">
                USM Indonesia
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link active" href="lowongan.php">Lowongan Kerja</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Direktori Alumni</a></li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-lg-3" href="../admin/dashboard.php">
                            <i class="fas fa-user-circle me-1"></i>Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="btn-back">
            <a href="lowongan.php"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Lowongan</a>
        </div>

        <div class="detail-card">
            <div class="header-section">
                <h1 class="posisi-title"><?= htmlspecialchars($lowongan['posisi'] ?? '-') ?></h1>
                <p class="perusahaan-name">
                    <i class="fas fa-building"></i><?= htmlspecialchars($lowongan['perusahaan'] ?? '-') ?>
                </p>
                <div class="mt-3">
                    <span class="badge-status">
                        <i class="fas fa-check-circle me-1"></i>Dibuka
                    </span>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-content">
                            <h6>Lokasi</h6>
                            <p><?= htmlspecialchars($lowongan['lokasi'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="info-content">
                            <h6>Gaji</h6>
                            <p><?= htmlspecialchars($lowongan['gaji'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <h3 class="section-title">Deskripsi Posisi</h3>
            <div class="section-content">
                <?= htmlspecialchars($lowongan['deskripsi'] ?? '-') ?>
            </div>

            <!-- Kualifikasi -->
            <h3 class="section-title">Kualifikasi yang Diinginkan</h3>
            <div class="section-content">
                <?= htmlspecialchars($lowongan['kualifikasi'] ?? '-') ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="https://wa.me/6281234567890?text=Saya%20tertarik%20dengan%20posisi%20<?= urlencode($lowongan['posisi'] ?? '') ?>%20di%20<?= urlencode($lowongan['perusahaan'] ?? '') ?>" target="_blank" class="btn-apply">
                    <i class="fas fa-paper-plane"></i>Daftar via WhatsApp
                </a>
                <button class="btn-share" onclick="copyLink()">
                    <i class="fas fa-share-alt me-2"></i>Share Link
                </button>
            </div>

            <hr class="my-4">

            <!-- Info Tambahan -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-2"></i>
                        Dibuka pada: <?= isset($lowongan['created_at']) ? date('d M Y', strtotime($lowongan['created_at'])) : date('d M Y') ?>
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-id-card me-2"></i>
                        ID: <?= htmlspecialchars($lowongan['id_lowongan'] ?? '') ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link berhasil di-copy!');
            }).catch(() => {
                alert('Gagal copy link');
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>