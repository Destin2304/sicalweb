<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Get statistik
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pra_tracer_study");
    $total_pra_tracer = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alumni WHERE status = 'aktif'");
    $total_alumni = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pra_tracer_study WHERE status_kontak = 'aktif'");
    $total_aktif = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pra_tracer_study WHERE status_kontak = 'nonaktif'");
    $total_nonaktif = $stmt->fetch()['total'] ?? 0;
    
    // Get data pra tracer
    $stmt = $pdo->query("SELECT * FROM pra_tracer_study ORDER BY filled_date DESC");
    $pra_tracer_data = $stmt->fetchAll();
    
    // Get status kontak distribution
    $stmt = $pdo->query("SELECT status_kontak, COUNT(*) as jumlah FROM pra_tracer_study GROUP BY status_kontak");
    $status_distribution = $stmt->fetchAll();
    
    $persentase = $total_alumni > 0 ? round(($total_pra_tracer / $total_alumni) * 100) : 0;
} catch (Exception $e) {
    $total_pra_tracer = 0;
    $total_alumni = 0;
    $total_aktif = 0;
    $total_nonaktif = 0;
    $pra_tracer_data = [];
    $status_distribution = [];
    $persentase = 0;
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
    <title>Laporan Pra Tracer Study - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .card { border: none !important; }
            body { background: white !important; }
        }
        
        body { background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
        .sidebar .logo-section { text-align: center; padding: 20px; border-bottom: 2px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .logo-section img { width: 60px; height: 60px; margin-bottom: 10px; }
        .sidebar .logo-section h5 { font-size: 12px; font-weight: 700; line-height: 1.2; }
        .sidebar .nav-item { padding: 12px 20px; border-left: 3px solid transparent; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 10px; margin: 5px 0; }
        .sidebar .nav-item:hover { background: rgba(255,255,255,0.1); border-left-color: #667eea; }
        .sidebar .nav-item.active { background: rgba(102, 126, 234, 0.3); border-left-color: #667eea; }
        .main-content { margin-left: 200px; padding: 20px; }
        .topbar { background: white; padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .topbar .clock { font-weight: 700; color: #667eea; }
        .page-title { font-size: 28px; font-weight: 800; color: #2c3e50; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-card .number { font-size: 36px; font-weight: 800; color: #667eea; margin-bottom: 10px; }
        .stat-card .label { font-size: 14px; font-weight: 700; color: #666; text-transform: uppercase; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px 12px 0 0; font-weight: 700; }
        .card-body { padding: 20px; }
        .table { margin-bottom: 0; font-size: 14px; }
        .table thead { background: #f8f9fa; }
        .table th { font-weight: 700; color: #2c3e50; border-bottom: 2px solid #e0e0e0; }
        .btn-print { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); color: white; }
        .chart-container { position: relative; height: 300px; margin-bottom: 20px; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-aktif { background: #d4edda; color: #155724; }
        .badge-nonaktif { background: #f8d7da; color: #721c24; }
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
        <div class="nav-item" onclick="location.href='dashboard.php'"><i class="fas fa-home"></i> Dashboard</div>
        <div class="nav-item" onclick="location.href='kelola_alumni.php'"><i class="fas fa-users"></i> Kelola Alumni</div>
        <div class="nav-item active"><i class="fas fa-file-alt"></i> Laporan Pra Tracer</div>
        <div class="nav-item" onclick="location.href='laporan_tracer_study.php'"><i class="fas fa-chart-bar"></i> Laporan Tracer Study</div>
        <div class="nav-item" onclick="location.href='laporan_survey.php'"><i class="fas fa-star"></i> Laporan Survey</div>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <div class="nav-item" onclick="location.href='../auth/logout.php'" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Keluar</div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar no-print">
            <div class="clock" id="digitalClock"><i class="far fa-clock me-2"></i>00:00:00</div>
            <div><small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small></div>
        </div>

        <div class="page-title no-print">
            <div>
                <i class="fas fa-file-alt me-2" style="color: #667eea;"></i>Laporan Pra Tracer Study
            </div>
            <button onclick="window.print()" class="btn-print no-print">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
        </div>

        <!-- Header -->
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 20px;">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" style="width: 80px; margin-bottom: 15px;">
            <h2 style="font-size: 24px; font-weight: 800; color: #2c3e50;">LAPORAN PRA TRACER STUDY ALUMNI</h2>
            <p style="color: #666; margin-bottom: 5px;">Universitas Sari Mutiara Indonesia</p>
            <small class="text-muted">Diperbarui: <?= date('d M Y H:i:s') ?></small>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $total_pra_tracer ?></div>
                    <div class="label">Total Responden</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $total_aktif ?></div>
                    <div class="label">Kontak Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $total_nonaktif ?></div>
                    <div class="label">Kontak Non-Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $persentase ?>%</div>
                    <div class="label">Tingkat Pengisian</div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Distribusi Status Kontak
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-2"></i>Data Pra Tracer Study
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Alumni</th>
                                <th>Email</th>
                                <th>Provinsi</th>
                                <th>Status Kontak</th>
                                <th>Tanggal Pengisian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pra_tracer_data)): ?>
                                <?php foreach ($pra_tracer_data as $i => $data): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($data['nim'] ?? '-') ?></strong></td>
                                        <td><?= htmlspecialchars($data['nama_alumni'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['provinsi'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge <?= ($data['status_kontak'] == 'aktif' ? 'badge-aktif' : 'badge-nonaktif') ?>">
                                                <?= ucfirst($data['status_kontak']) ?>
                                            </span>
                                        </td>
                                        <td><?= isset($data['filled_date']) ? date('d M Y', strtotime($data['filled_date'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data pra tracer study</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kesimpulan -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-conclusion me-2"></i>Kesimpulan & Rekomendasi
            </div>
            <div class="card-body">
                <h6>Kesimpulan:</h6>
                <p>
                    Berdasarkan data pra tracer study yang telah dikumpulkan, terdapat <strong><?= $total_pra_tracer ?></strong> alumni yang telah mengisi formulir pra tracer study. 
                    Dari jumlah tersebut, <strong><?= $total_aktif ?></strong> alumni memiliki data kontak yang masih aktif dan dapat dihubungi untuk tahap tracer study selanjutnya.
                    Tingkat pengisian pra tracer mencapai <strong><?= $persentase ?>%</strong> dari total alumni.
                </p>

                <h6 class="mt-3">Rekomendasi:</h6>
                <ul>
                    <li>Lakukan follow-up terhadap alumni dengan kontak non-aktif untuk memperbarui data kontak mereka</li>
                    <li>Mulai pelaksanaan tracer study alumni kepada alumni yang telah mengisi pra tracer study</li>
                    <li>Siapkan panduan tracer study dan instrumen pengumpulan data</li>
                    <li>Tetapkan jadwal dan timeline pelaksanaan tracer study</li>
                    <li>Persiapkan tim untuk melakukan validasi dan follow-up data</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
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
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('digitalClock').innerHTML = `<i class="far fa-clock me-2"></i>${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Chart
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Kontak Aktif', 'Kontak Non-Aktif'],
                datasets: [{
                    data: [<?= $total_aktif ?>, <?= $total_nonaktif ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderColor: ['#fff', '#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>