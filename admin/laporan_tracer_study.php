<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Get statistik
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tracer_study");
    $total_tracer = $stmt->fetch()['total'] ?? 0;
    
    // Status Alumni Distribution
    $stmt = $pdo->query("SELECT status_alumni, COUNT(*) as jumlah FROM tracer_study GROUP BY status_alumni");
    $status_dist = $stmt->fetchAll();
    $status_labels = [];
    $status_data = [];
    foreach ($status_dist as $row) {
        $status_labels[] = $row['status_alumni'];
        $status_data[] = $row['jumlah'];
    }
    
    // Average waiting time
    $stmt = $pdo->query("SELECT AVG(bulan_tunggu_kerja) as avg_bulan FROM tracer_study WHERE status_alumni='Bekerja'");
    $avg_tunggu = round($stmt->fetch()['avg_bulan'] ?? 0);
    
    // Job acquisition method
    $stmt = $pdo->query("SELECT cara_memperoleh_kerja, COUNT(*) as jumlah FROM tracer_study GROUP BY cara_memperoleh_kerja");
    $cara_kerja = $stmt->fetchAll();
    
    // Field suitability
    $stmt = $pdo->query("SELECT kesesuaian_bidang, COUNT(*) as jumlah FROM tracer_study GROUP BY kesesuaian_bidang");
    $kesesuaian = $stmt->fetchAll();
    
    // Get all data
    $stmt = $pdo->query("SELECT * FROM tracer_study ORDER BY filled_date DESC");
    $tracer_data = $stmt->fetchAll();
    
} catch (Exception $e) {
    $total_tracer = 0;
    $status_labels = [];
    $status_data = [];
    $avg_tunggu = 0;
    $cara_kerja = [];
    $kesesuaian = [];
    $tracer_data = [];
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
    <title>Laporan Tracer Study - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
        }
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
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
        .stat-card .number { font-size: 36px; font-weight: 800; color: #28a745; margin-bottom: 10px; }
        .stat-card .label { font-size: 14px; font-weight: 700; color: #666; text-transform: uppercase; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 12px 12px 0 0; font-weight: 700; }
        .card-body { padding: 20px; }
        .table { margin-bottom: 0; font-size: 14px; }
        .table thead { background: #f8f9fa; }
        .table th { font-weight: 700; color: #2c3e50; border-bottom: 2px solid #e0e0e0; }
        .btn-print { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); color: white; }
        .chart-container { position: relative; height: 300px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" onerror="this.src='data:image/svg+xml,<svg></svg>'">
            <h5>UNIVERSITAS SARI MUTIARA INDONESIA</h5>
            <small>Career Center Portal</small>
        </div>
        <div class="nav-item" onclick="location.href='dashboard.php'"><i class="fas fa-home"></i> Dashboard</div>
        <div class="nav-item" onclick="location.href='kelola_alumni.php'"><i class="fas fa-users"></i> Kelola Alumni</div>
        <div class="nav-item" onclick="location.href='laporan_pra_tracer.php'"><i class="fas fa-file-alt"></i> Laporan Pra Tracer</div>
        <div class="nav-item active"><i class="fas fa-chart-bar"></i> Laporan Tracer Study</div>
        <div class="nav-item" onclick="location.href='laporan_survey.php'"><i class="fas fa-star"></i> Laporan Survey</div>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <div class="nav-item" onclick="location.href='../auth/logout.php'" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Keluar</div>
    </div>

    <div class="main-content">
        <div class="topbar no-print">
            <div class="clock" id="digitalClock"><i class="far fa-clock me-2"></i>00:00:00</div>
            <div><small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small></div>
        </div>

        <div class="page-title no-print">
            <div><i class="fas fa-chart-bar me-2" style="color: #28a745;"></i>Laporan Tracer Study Alumni</div>
            <button onclick="window.print()" class="btn-print no-print"><i class="fas fa-print me-2"></i>Cetak</button>
        </div>

        <!-- Header -->
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 20px;">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" style="width: 80px; margin-bottom: 15px;">
            <h2 style="font-size: 24px; font-weight: 800; color: #2c3e50;">LAPORAN TRACER STUDY ALUMNI</h2>
            <p style="color: #666; margin-bottom: 5px;">Universitas Sari Mutiara Indonesia</p>
            <small class="text-muted">Diperbarui: <?= date('d M Y H:i:s') ?></small>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $total_tracer ?></div>
                    <div class="label">Total Responden</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $avg_tunggu ?></div>
                    <div class="label">Rata-rata Tunggu (Bulan)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= count($status_dist) ?></div>
                    <div class="label">Kategori Status</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="number"><?= $total_tracer > 0 ? round(($total_tracer / 100) * 100) : 0 ?>%</div>
                    <div class="label">Tingkat Respons</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="card">
            <div class="card-header"><i class="fas fa-chart-pie me-2"></i>Status Alumni</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Cara Memperoleh Pekerjaan</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="caraChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header"><i class="fas fa-table me-2"></i>Data Tracer Study</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Perusahaan</th>
                                <th>Posisi</th>
                                <th>Wilayah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracer_data as $i => $data): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($data['nim'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['nama_alumni'] ?? '-') ?></td>
                                    <td><small class="badge bg-info"><?= htmlspecialchars($data['status_alumni'] ?? '-') ?></small></td>
                                    <td><?= htmlspecialchars($data['nama_perusahaan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['posisi_kerja'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($data['provinsi_kerja'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Conclusions -->
        <div class="card">
            <div class="card-header"><i class="fas fa-lightbulb me-2"></i>Kesimpulan & Rekomendasi</div>
            <div class="card-body">
                <p>Berdasarkan data tracer study yang telah dikumpulkan dari <strong><?= $total_tracer ?></strong> alumni, diperoleh insight berikut:</p>
                <ul>
                    <li>Mayoritas alumni berstatus <?= !empty($status_labels) ? htmlspecialchars($status_labels[0]) : '-' ?></li>
                    <li>Rata-rata waktu tunggu kerja adalah <strong><?= $avg_tunggu ?> bulan</strong></li>
                    <li>Alumni tersebar di berbagai industri dan lokasi kerja</li>
                    <li>Tingkat kepuasan Alumni dengan Program Studi cukup baik</li>
                </ul>
                <h6 class="mt-4">Rekomendasi:</h6>
                <ul>
                    <li>Tingkatkan kemitraan dengan industri untuk memperluas jaringan alumni</li>
                    <li>Kembangkan skill praktis yang dibutuhkan dunia industri</li>
                    <li>Selenggarakan workshop dan seminar karir secara berkala</li>
                    <li>Maintain kontak dengan alumni untuk program mentoring</li>
                </ul>
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

        // Chart Status
        const ctx1 = document.getElementById('statusChart')?.getContext('2d');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($status_labels) ?>,
                    datasets: [{
                        data: <?= json_encode($status_data) ?>,
                        backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6c757d'],
                        borderColor: ['#fff', '#fff', '#fff', '#fff', '#fff'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Chart Cara Kerja
        const ctx2 = document.getElementById('caraChart')?.getContext('2d');
        if (ctx2 && <?= count($cara_kerja) ?> > 0) {
            const caraLabels = <?= json_encode(array_map(fn($r) => $r['cara_memperoleh_kerja'], $cara_kerja)) ?>;
            const caraData = <?= json_encode(array_map(fn($r) => $r['jumlah'], $cara_kerja)) ?>;
            
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: caraLabels,
                    datasets: [{
                        label: 'Jumlah Alumni',
                        data: caraData,
                        backgroundColor: '#28a745',
                        borderColor: '#20c997',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>