<?php
session_start();
require_once '../config/config.php';

// Proteksi halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Ambil data tracer study dengan JOIN ke alumni
    $query = "SELECT t.*, a.nama_alumni, a.jurusan
              FROM tracer_study t
              LEFT JOIN alumni a ON t.id_alumni = a.id_alumni
              ORDER BY a.nama_alumni ASC";
    
    $stmt = $pdo->query($query);
    $reports = $stmt->fetchAll();
} catch (Exception $e) {
    $reports = [];
    $error_msg = "Error mengambil data: " . $e->getMessage();
}

// Ambil data user untuk topbar
$user_name = 'Admin';
if ($_SESSION['role'] === 'admin') {
    try {
        $stmt_u = $pdo->prepare("SELECT username FROM admin WHERE id_admin = ?");
        $stmt_u->execute([$_SESSION['user_id']]);
        $u_data = $stmt_u->fetch();
        if ($u_data) $user_name = $u_data['username'];
    } catch (Exception $e) {
        // Keep default
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tracer Study - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .sidebar, .topbar, .btn, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .card { border: none !important; box-shadow: none !important; }
            body { background: white !important; }
        }
        
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 200px;
            overflow-y: auto;
        }
        
        .sidebar .logo-section {
            text-align: center;
            padding: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .logo-section img {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
        }
        
        .sidebar .logo-section h5 {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .sidebar .nav-item {
            padding: 12px 20px;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }
        
        .sidebar .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #667eea;
        }
        
        .sidebar .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            border-left-color: #667eea;
        }
        
        .main-content {
            margin-left: 200px;
            padding: 20px;
        }
        
        .topbar {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            no-print: true;
        }
        
        .topbar .clock {
            font-weight: 700;
            color: #667eea;
        }
        
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .report-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .report-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        
        .report-header h2 {
            font-size: 24px;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .report-header p {
            color: #666;
            margin: 0;
        }
        
        .report-table {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .report-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .report-table table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .report-table table th {
            padding: 15px;
            font-weight: 700;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        
        .report-table table td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        
        .report-table table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .report-table table tbody tr:hover {
            background: #f0f0f0;
        }
        
        .badge-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .badge-sesuai {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-kurang {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-tidak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" onerror="this.src='data:image/svg+xml,<svg></svg>'">
            <h5>UNIVERSITAS SARI MUTIARA INDONESIA</h5>
            <small>Career Center Portal</small>
        </div>
        
        <div class="nav-item" onclick="location.href='dashboard.php'">
            <i class="fas fa-home"></i> Dashboard
        </div>
        <div class="nav-item" onclick="location.href='kelola_alumni.php'">
            <i class="fas fa-users"></i> Kelola Alumni
        </div>
        <div class="nav-item" onclick="location.href='tambah_alumni.php'">
            <i class="fas fa-user-plus"></i> Tambah Alumni
        </div>
        <div class="nav-item active" onclick="location.href='laporan.php'">
            <i class="fas fa-file-alt"></i> Laporan Tracer
        </div>
        <div class="nav-item" onclick="location.href='pengaturan_web.php'">
            <i class="fas fa-cogs"></i> Pengaturan Web
        </div>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <div class="nav-item" onclick="location.href='../auth/logout.php'" style="color: #ff6b6b;">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar no-print">
            <div class="clock" id="digitalClock">
                <i class="far fa-clock me-2"></i>00:00:00
            </div>
            <div class="user-info">
                <small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small>
            </div>
        </div>

        <!-- Page Title -->
        <div class="page-title">
            <div>
                <i class="fas fa-file-alt me-2" style="color: #667eea;"></i>Laporan Tracer Study
            </div>
            <button onclick="window.print()" class="btn-print no-print">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
        </div>

        <!-- Report Header -->
        <div class="report-header">
            <img src="../assets/images/LOGO_USM.png" alt="Logo USM" onerror="this.style.display='none'">
            <h2>LAPORAN DATA TRACER STUDY ALUMNI</h2>
            <p>Universitas Sari Mutiara Indonesia</p>
            <hr>
            <small class="text-muted">Diperbarui: <?= date('d M Y H:i:s') ?></small>
        </div>

        <!-- Report Table -->
        <div class="report-table">
            <?php if (!empty($reports)): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th style="width: 15%;">NAMA ALUMNI</th>
                            <th style="width: 15%;">JURUSAN</th>
                            <th style="width: 12%;">STATUS LULUSAN</th>
                            <th style="width: 12%;">WAKTU TUNGGU</th>
                            <th style="width: 15%;">METODE MENDAPAT KERJA</th>
                            <th style="width: 15%;">KESESUAIAN BIDANG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($reports as $row): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($row['nama_alumni'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($row['jurusan'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['status_bekerja'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['waktu_tunggu'] ?? '-') ?> bulan</td>
                            <td><?= htmlspecialchars($row['metode_kerja'] ?? '-') ?></td>
                            <td>
                                <?php 
                                $kesesuaian = $row['kesesuaian_bidang'] ?? 'N/A';
                                $badge_class = 'badge-kurang';
                                if (strpos(strtolower($kesesuaian), 'sesuai') !== false && strpos(strtolower($kesesuaian), 'kurang') === false) {
                                    $badge_class = 'badge-sesuai';
                                } elseif (strpos(strtolower($kesesuaian), 'tidak') !== false) {
                                    $badge_class = 'badge-tidak';
                                }
                                ?>
                                <span class="badge-status <?= $badge_class ?>">
                                    <?= htmlspecialchars($kesesuaian) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Belum ada data tracer study. Silakan tambahkan data alumni terlebih dahulu.
                </div>
            <?php endif; ?>
        </div>

        <!-- Print Footer -->
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <small class="text-muted">
                <i class="fas fa-print me-1"></i>
                Dokumen ini dapat dicetak untuk keperluan administratif.
                <br>
                Generated: <?= date('d M Y H:i:s') ?>
            </small>
        </div>
    </div>

    <script>
        // Digital Clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('digitalClock').innerHTML = `<i class="far fa-clock me-2"></i>${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>