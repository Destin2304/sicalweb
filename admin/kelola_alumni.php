<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alumni");
    $result = $stmt->fetch();
    $total_alumni = $result['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pra_tracer_study");
    $result = $stmt->fetch();
    $pra_tracer_count = $result['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tracer_study");
    $result = $stmt->fetch();
    $tracer_count = $result['total'] ?? 0;
    
    $active_percentage = $total_alumni > 0 ? round(($tracer_count / $total_alumni) * 100) : 0;
} catch (Exception $e) {
    $total_alumni = 0;
    $pra_tracer_count = 0;
    $tracer_count = 0;
    $active_percentage = 0;
}

// Get user info
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
    <title>Kelola Alumni - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
        }
        
        .topbar .clock {
            font-weight: 700;
            color: #667eea;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .hero-section h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .hero-section p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            font-size: 14px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .action-card-header {
            padding: 20px;
            font-weight: 700;
            color: white;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-card-header.pra-tracer {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .action-card-header.tracer {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .action-card-header.survey {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        }
        
        .action-card-body {
            padding: 20px;
        }
        
        .action-card-body p {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-form {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-form.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-form.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-form.warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
        }
        
        .btn-form:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table-responsive {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        }
        
        .table tbody tr:hover {
            background: #f9f9f9;
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
        <div class="nav-item active" onclick="location.href='kelola_alumni.php'">
            <i class="fas fa-users"></i> Kelola Alumni
        </div>
        <div class="nav-item" onclick="location.href='laporan_pra_tracer.php'">
            <i class="fas fa-file-alt"></i> Laporan Pra Tracer
        </div>
        <div class="nav-item" onclick="location.href='laporan_tracer_study.php'">
            <i class="fas fa-chart-bar"></i> Laporan Tracer Study
        </div>
        <div class="nav-item" onclick="location.href='laporan_survey.php'">
            <i class="fas fa-star"></i> Laporan Survey
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
        <div class="topbar">
            <div class="clock" id="digitalClock">
                <i class="far fa-clock me-2"></i>00:00:00
            </div>
            <div>
                <small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="hero-section">
            <h2><i class="fas fa-users me-2"></i>KELOLA DATA ALUMNI</h2>
            <p>Mengelola data alumni, pra tracer study, tracer study, dan survey pengguna alumni</p>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="number"><?= $total_alumni ?></div>
                    <div class="label">Total Alumni</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="number"><?= $pra_tracer_count ?></div>
                    <div class="label">Pra Tracer Study</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="number"><?= $active_percentage ?>%</div>
                    <div class="label">Data Tracer Study</div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row">
            <!-- Pra Tracer Study Card -->
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-card-header pra-tracer">
                        <i class="fas fa-clipboard-list"></i>
                        PRA TRACER STUDY
                    </div>
                    <div class="action-card-body">
                        <p>Form pengisian data alumni sebelum memasuki dunia kerja. Berisi informasi tentang kesiapan alumni menghadapi dunia kerja.</p>
                        <div class="action-buttons">
                            <a href="pra_tracer_form.php" class="btn-form primary">
                                <i class="fas fa-plus me-1"></i>Buka Form
                            </a>
                            <a href="laporan_pra_tracer.php" class="btn-form primary">
                                <i class="fas fa-chart-pie me-1"></i>Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracer Study Card -->
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-card-header tracer">
                        <i class="fas fa-briefcase"></i>
                        TRACER STUDY ALUMNI
                    </div>
                    <div class="action-card-body">
                        <p>Form tracking data alumni setelah lulus dan bekerja. Memantau karir dan keselaian bidang pekerjaan alumni dari universitas kami.</p>
                        <div class="action-buttons">
                            <a href="tracer_study_form.php" class="btn-form success">
                                <i class="fas fa-plus me-1"></i>Buka Form
                            </a>
                            <a href="laporan_tracer_study.php" class="btn-form success">
                                <i class="fas fa-chart-bar me-1"></i>Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Survey Card -->
            <div class="col-md-4 mb-4">
                <div class="action-card">
                    <div class="action-card-header survey">
                        <i class="fas fa-star"></i>
                        SURVEY PENGGUNA ALUMNI
                    </div>
                    <div class="action-card-body">
                        <p>Survei kepuasan pengguna (atasan, HRD) terhadap alumni kami di dunia kerja. Berguna untuk evaluasi kualitas pendidikan dan relevansi kurikulum.</p>
                        <div class="action-buttons">
                            <a href="survey_form.php" class="btn-form warning">
                                <i class="fas fa-plus me-1"></i>Buka Form
                            </a>
                            <a href="laporan_survey.php" class="btn-form warning">
                                <i class="fas fa-chart-line me-1"></i>Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Alumni Table -->
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3"><i class="fas fa-table me-2"></i>Data Alumni</h5>
                <div class="table-responsive">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#alumniModal">
                        <i class="fas fa-eye me-1"></i>Lihat Semua Alumni
                    </button>
                    
                    <table class="table" id="alumniTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Alumni</th>
                                <th>NIM</th>
                                <th>Jurusan</th>
                                <th>Tahun Lulus</th>
                                <th>Provinsi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="alumniTableBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alumni -->
    <div class="modal fade" id="alumniModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-list me-2"></i>Data Semua Alumni</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Jurusan</th>
                                    <th>Tahun Lulus</th>
                                </tr>
                            </thead>
                            <tbody id="allAlumniTable">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Load Alumni Data
        function loadAlumniData() {
            fetch('../api/get_alumni.php')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.slice(0, 5).forEach((alm, i) => {
                            html += `<tr>
                                <td>${i+1}</td>
                                <td>${alm.nama_alumni}</td>
                                <td>${alm.nim}</td>
                                <td>${alm.jurusan}</td>
                                <td>${alm.provinsi}</td>
                                <td><span class="badge bg-success">Aktif</span></td>
                            </tr>`;
                        });
                        // Load all alumni for modal
                        let allHtml = '';
                        data.forEach((alm, i) => {
                            allHtml += `<tr>
                                <td>${i+1}</td>
                                <td>${alm.nama_alumni}</td>
                                <td>${alm.nim}</td>
                                <td>${alm.jurusan}</td>
                                <td>${alm.tahun_lulus}</td>
                            </tr>`;
                        });
                        document.getElementById('allAlumniTable').innerHTML = allHtml;
                    } else {
                        html = '<tr><td colspan="6" class="text-center">Belum ada data alumni</td></tr>';
                    }
                    document.getElementById('alumniTableBody').innerHTML = html;
                })
                .catch(e => console.error('Error:', e));
        }
        loadAlumniData();
    </script>
</body>
</html>