<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Get statistik
    $total_alumni = $pdo->query("SELECT COUNT(*) as total FROM alumni")->fetch()['total'] ?? 0;
    $total_lowongan = $pdo->query("SELECT COUNT(*) as total FROM lowongan")->fetch()['total'] ?? 0;
    $total_pra_tracer = $pdo->query("SELECT COUNT(*) as total FROM pra_tracer_study")->fetch()['total'] ?? 0;
    $total_tracer = $pdo->query("SELECT COUNT(*) as total FROM tracer_study")->fetch()['total'] ?? 0;
    $total_survey = $pdo->query("SELECT COUNT(*) as total FROM survey_pengguna")->fetch()['total'] ?? 0;
    
    // Get recent jobs
    $stmt = $pdo->query("SELECT * FROM lowongan ORDER BY tanggal_post DESC LIMIT 5");
    $recent_jobs = $stmt->fetchAll();
} catch (Exception $e) {
    $total_alumni = 0;
    $total_lowongan = 0;
    $total_pra_tracer = 0;
    $total_tracer = 0;
    $total_survey = 0;
    $recent_jobs = [];
}

$user_name = 'Admin';
try {
    $stmt_u = $pdo->prepare("SELECT username FROM admin WHERE id_admin = ?");
    $stmt_u->execute([$_SESSION['user_id']]);
    $u_data = $stmt_u->fetch();
    if ($u_data) $user_name = $u_data['username'];
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        
        .sidebar {
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .logo-section {
            text-align: center;
            padding: 25px 20px;
            border-bottom: 2px solid rgba(255,255,255,0.15);
            margin-bottom: 30px;
        }
        
        .sidebar .logo-section img {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .sidebar .logo-section h5 {
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 5px;
        }
        
        .sidebar .logo-section small {
            font-size: 11px;
            opacity: 0.8;
        }
        
        .nav-section {
            padding: 0 15px;
            margin-bottom: 20px;
        }
        
        .nav-section-title {
            font-size: 11px;
            font-weight: 800;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        
        .nav-item {
            padding: 12px 15px;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 5px;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #667eea;
        }
        
        .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            border-left-color: #667eea;
            color: #ffd700;
        }
        
        .nav-item i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 240px;
            padding: 30px;
        }
        
        .topbar {
            background: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .topbar .clock {
            font-weight: 800;
            color: #667eea;
            font-size: 16px;
        }
        
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .topbar .user-info small {
            color: #666;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header p {
            color: #999;
            margin-top: 8px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-top: 4px solid #667eea;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .stat-card.blue { border-top-color: #667eea; }
        .stat-card.green { border-top-color: #28a745; }
        .stat-card.orange { border-top-color: #ffc107; }
        .stat-card.red { border-top-color: #dc3545; }
        .stat-card.purple { border-top-color: #764ba2; }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            position: absolute;
            right: 30px;
            top: 30px;
        }
        
        .stat-card.green .stat-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
        .stat-card.red .stat-icon { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        
        .quick-access {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-btn {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .quick-btn:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-3px);
            color: #667eea;
        }
        
        .quick-btn i {
            font-size: 28px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            font-weight: 700;
            border: none;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .table {
            margin-bottom: 0;
            font-size: 14px;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table th {
            font-weight: 700;
            color: #2c3e50;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .btn-logout {
            color: #ff6b6b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-logout:hover {
            background: rgba(255,107,107,0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                padding: 15px;
            }
            .page-header h1 {
                font-size: 24px;
            }
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
        
        <div class="nav-section">
            <div class="nav-section-title">Menu Utama</div>
            <div class="nav-item active" onclick="location.href='dashboard.php'">
                <i class="fas fa-home"></i> Dashboard
            </div>
            <div class="nav-item" onclick="location.href='kelola_alumni.php'">
                <i class="fas fa-users"></i> Kelola Alumni
            </div>
            <div class="nav-item" onclick="location.href='dashboard.php#lowongan'">
                <i class="fas fa-briefcase"></i> Kelola Lowongan
            </div>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Laporan</div>
            <div class="nav-item" onclick="location.href='laporan_pra_tracer.php'">
                <i class="fas fa-file-alt"></i> Pra Tracer
            </div>
            <div class="nav-item" onclick="location.href='laporan_tracer_study.php'">
                <i class="fas fa-chart-bar"></i> Tracer Study
            </div>
            <div class="nav-item" onclick="location.href='laporan_survey.php'">
                <i class="fas fa-star"></i> Survey
            </div>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Lainnya</div>
            <div class="nav-item" onclick="location.href='../pages/search_alumni.php'">
                <i class="fas fa-search"></i> Cari Alumni
            </div>
            <div class="nav-item" onclick="location.href='../index.php'">
                <i class="fas fa-globe"></i> Lihat Website
            </div>
        </div>
        
        <div class="nav-section" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <div class="nav-item btn-logout" onclick="location.href='../auth/logout.php'">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="clock" id="digitalClock">
                <i class="far fa-clock me-2"></i>00:00:00
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle" style="font-size: 24px; color: #667eea;"></i>
                <div>
                    <small>Selamat datang</small><br>
                    <strong><?= htmlspecialchars($user_name) ?></strong>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="fas fa-chart-line" style="color: #667eea;"></i>
                Dashboard Admin
            </h1>
            <p>Kelola data alumni, tracer study, dan survey pengguna dengan mudah</p>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-4 col-lg-2.4">
                <div class="stat-card blue" style="position: relative;">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= $total_alumni ?></div>
                    <div class="stat-label">Total Alumni</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4">
                <div class="stat-card green" style="position: relative;">
                    <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
                    <div class="stat-number"><?= $total_lowongan ?></div>
                    <div class="stat-label">Lowongan</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4">
                <div class="stat-card orange" style="position: relative;">
                    <div class="stat-icon"><i class="fas fa-clipboard"></i></div>
                    <div class="stat-number"><?= $total_pra_tracer ?></div>
                    <div class="stat-label">Pra Tracer</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4">
                <div class="stat-card purple" style="position: relative;">
                    <div class="stat-icon"><i class="fas fa-chart-area"></i></div>
                    <div class="stat-number"><?= $total_tracer ?></div>
                    <div class="stat-label">Tracer Study</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2.4">
                <div class="stat-card red" style="position: relative;">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div class="stat-number"><?= $total_survey ?></div>
                    <div class="stat-label">Survey</div>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div style="margin-top: 40px; margin-bottom: 40px;">
            <h5 style="font-weight: 800; color: #2c3e50; margin-bottom: 20px;">Akses Cepat</h5>
            <div class="quick-access">
                <a href="kelola_alumni.php" class="quick-btn">
                    <i class="fas fa-plus-circle"></i>
                    Kelola Alumni
                </a>
                <a href="pra_tracer_form.php" class="quick-btn">
                    <i class="fas fa-file-invoice"></i>
                    Pra Tracer
                </a>
                <a href="tracer_study_form.php" class="quick-btn">
                    <i class="fas fa-search-plus"></i>
                    Tracer Study
                </a>
                <a href="survey_form.php" class="quick-btn">
                    <i class="fas fa-poll"></i>
                    Buat Survey
                </a>
                <a href="laporan_pra_tracer.php" class="quick-btn">
                    <i class="fas fa-file-pdf"></i>
                    Laporan 1
                </a>
                <a href="laporan_tracer_study.php" class="quick-btn">
                    <i class="fas fa-chart-pie"></i>
                    Laporan 2
                </a>
                <a href="laporan_survey.php" class="quick-btn">
                    <i class="fas fa-list-check"></i>
                    Laporan 3
                </a>
                <a href="../pages/search_alumni.php" class="quick-btn">
                    <i class="fas fa-search"></i>
                    Cari Alumni
                </a>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card" id="lowongan">
            <div class="card-header">
                <i class="fas fa-briefcase me-2"></i>Lowongan Kerja Terbaru
            </div>
            <div class="card-body">
                <?php if (!empty($recent_jobs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Posisi</th>
                                    <th>Perusahaan</th>
                                    <th>Lokasi</th>
                                    <th>Tanggal Post</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_jobs as $job): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($job['posisi'] ?? '-') ?></strong></td>
                                        <td><?= htmlspecialchars($job['perusahaan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($job['lokasi'] ?? '-') ?></td>
                                        <td><?= isset($job['tanggal_post']) ? date('d M Y', strtotime($job['tanggal_post'])) : '-' ?></td>
                                        <td>
                                            <a href="../pages/detail_lowongan.php?id=<?= $job['id_lowongan'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">Belum ada lowongan kerja</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
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