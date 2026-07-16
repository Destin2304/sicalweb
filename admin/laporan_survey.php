<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM survey_pengguna");
    $total_survey = $stmt->fetch()['total'] ?? 0;
    
    // Average ratings
    $stmt = $pdo->query("SELECT 
        AVG(integritas) as avg_integritas,
        AVG(etika) as avg_etika,
        AVG(profesionalisme) as avg_profesionalisme,
        AVG(komunikasi) as avg_komunikasi,
        AVG(kerja_tim) as avg_kerja_tim,
        AVG(kepemimpinan) as avg_kepemimpinan,
        AVG(teknologi_informasi) as avg_teknologi,
        AVG(bahasa_asing) as avg_bahasa
    FROM survey_pengguna");
    $ratings = $stmt->fetch();
    
    // Recommendation distribution
    $stmt = $pdo->query("SELECT rekomendasi, COUNT(*) as jumlah FROM survey_pengguna GROUP BY rekomendasi");
    $rekomendasi = $stmt->fetchAll();
    
    // Get all surveys
    $stmt = $pdo->query("SELECT * FROM survey_pengguna ORDER BY filled_date DESC LIMIT 50");
    $surveys = $stmt->fetchAll();
    
} catch (Exception $e) {
    $total_survey = 0;
    $ratings = [];
    $rekomendasi = [];
    $surveys = [];
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
    <title>Laporan Survey - USM Indonesia</title>
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
        .sidebar .nav-item:hover { background: rgba(255,255,255,0.1); border-left-color: #ffc107; }
        .sidebar .nav-item.active { background: rgba(255, 193, 7, 0.3); border-left-color: #ffc107; }
        .main-content { margin-left: 200px; padding: 20px; }
        .topbar { background: white; padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .topbar .clock { font-weight: 700; color: #ffc107; }
        .page-title { font-size: 28px; font-weight: 800; color: #2c3e50; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-card .number { font-size: 36px; font-weight: 800; color: #ffc107; margin-bottom: 10px; }
        .stat-card .label { font-size: 14px; font-weight: 700; color: #666; text-transform: uppercase; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 20px; border-radius: 12px 12px 0 0; font-weight: 700; }
        .card-body { padding: 20px; }
        .table { margin-bottom: 0; font-size: 14px; }
        .table thead { background: #f8f9fa; }
        .table th { font-weight: 700; color: #2c3e50; border-bottom: 2px solid #e0e0e0; }
        .btn-print { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3); color: white; }
        .chart-container { position: relative; height: 300px; margin-bottom: 20px; }
        .rating-badge { display: inline-block; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 8px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; }
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
        <div class="nav-item" onclick="location.href='laporan_tracer_study.php'"><i class="fas fa-chart-bar"></i> Laporan Tracer Study</div>
        <div class="nav-item active"><i class="fas fa-star"></i> Laporan Survey</div>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <div class="nav-item" onclick="location.href='../auth/logout.php'" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Keluar</div>
    </div>

    <div class="main-content">
        <div class="topbar no-print">
            <div class="clock" id="digitalClock"><i class="far fa-clock me-2"></i>00:00:00</div>
            <div><small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small></div>
        </div>

        <div class="page-title no-print">
            <div><i class="fas fa-star me-2" style="color: #ffc107;"></i>Laporan Survey Pengguna Alumni</div>
            <button onclick="window.print()" class="btn-print no-print"><i class="fas fa-print me-2"></i>Cetak</button>
        </div>

        <!-- Header -->
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 20px;">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" style="width: 80px; margin-bottom: 15px;">
            <h2 style="font-size: 24px; font-weight: 800; color: #2c3e50;">LAPORAN SURVEY PENGGUNA ALUMNI</h2>
            <p style="color: #666; margin-bottom: 5px;">Universitas Sari Mutiara Indonesia</p>
            <small class="text-muted">Diperbarui: <?= date('d M Y H:i:s') ?></small>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="number"><?= $total_survey ?></div>
                    <div class="label">Total Survey</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="number"><?= round(($ratings['avg_integritas'] ?? 0) * 2) / 2 ?>/5</div>
                    <div class="label">Rating Rata-rata</div>
                </div>
            </div>
        </div>

        <!-- Radar Chart -->
        <div class="card">
            <div class="card-header"><i class="fas fa-chart-radar me-2"></i>Penilaian Kompetensi Alumni (Skala 1-5)</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="radarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recommendation -->
        <div class="card">
            <div class="card-header"><i class="fas fa-thumbs-up me-2"></i>Tingkat Rekomendasi</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="rekomendasiChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Rating Details -->
        <div class="card">
            <div class="card-header"><i class="fas fa-list me-2"></i>Detail Rating Kompetensi</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Integritas & Kejujuran</small>
                        <div class="rating-badge"><?= round(($ratings['avg_integritas'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Etika Profesional</small>
                        <div class="rating-badge"><?= round(($ratings['avg_etika'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Profesionalisme</small>
                        <div class="rating-badge"><?= round(($ratings['avg_profesionalisme'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Komunikasi</small>
                        <div class="rating-badge"><?= round(($ratings['avg_komunikasi'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Kerja Sama Tim</small>
                        <div class="rating-badge"><?= round(($ratings['avg_kerja_tim'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Kepemimpinan</small>
                        <div class="rating-badge"><?= round(($ratings['avg_kepemimpinan'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Teknologi Informasi</small>
                        <div class="rating-badge"><?= round(($ratings['avg_teknologi'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Bahasa Asing</small>
                        <div class="rating-badge"><?= round(($ratings['avg_bahasa'] ?? 0) * 10) / 10 ?>/5</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conclusions -->
        <div class="card">
            <div class="card-header"><i class="fas fa-lightbulb me-2"></i>Kesimpulan & Saran Perbaikan</div>
            <div class="card-body">
                <p>Berdasarkan <strong><?= $total_survey ?></strong> survey dari pengguna alumni (instansi/perusahaan), diperoleh penilaian atas kompetensi alumni USM sebagai berikut:</p>
                <ul>
                    <li>Aspek yang paling kuat: <strong>Profesionalisme & Etika</strong> dengan rating <?= round(($ratings['avg_profesionalisme'] ?? 0) * 10) / 10 ?>/5</li>
                    <li>Aspek yang perlu dikembangkan: Penguasaan teknologi dan bahasa asing</li>
                    <li>Tingkat kepuasan pengguna terhadap alumni cukup baik</li>
                </ul>
                <h6 class="mt-4">Rekomendasi:</h6>
                <ul>
                    <li>Tingkatkan kurikulum untuk lebih focus pada soft skills</li>
                    <li>Tambahkan pelatihan teknologi dan bahasa asing</li>
                    <li>Perluas partnership dengan industri untuk magang dan mentoring</li>
                    <li>Lakukan follow-up berkala dengan alumni di tempat kerja</li>
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

        // Radar Chart
        const ctxRadar = document.getElementById('radarChart')?.getContext('2d');
        if (ctxRadar) {
            new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Integritas', 'Etika', 'Profesionalisme', 'Komunikasi', 'Kerja Tim', 'Kepemimpinan', 'Teknologi', 'Bahasa Asing'],
                    datasets: [{
                        label: 'Rating Rata-rata',
                        data: [
                            <?= $ratings['avg_integritas'] ?? 0 ?>,
                            <?= $ratings['avg_etika'] ?? 0 ?>,
                            <?= $ratings['avg_profesionalisme'] ?? 0 ?>,
                            <?= $ratings['avg_komunikasi'] ?? 0 ?>,
                            <?= $ratings['avg_kerja_tim'] ?? 0 ?>,
                            <?= $ratings['avg_kepemimpinan'] ?? 0 ?>,
                            <?= $ratings['avg_teknologi'] ?? 0 ?>,
                            <?= $ratings['avg_bahasa'] ?? 0 ?>
                        ],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { r: { beginAtZero: true, max: 5 } },
                    plugins: { legend: { display: true } }
                }
            });
        }

        // Recommendation Chart
        const ctxRek = document.getElementById('rekomendasiChart')?.getContext('2d');
        if (ctxRek && <?= count($rekomendasi) ?> > 0) {
            const rekLabels = <?= json_encode(array_map(fn($r) => $r['rekomendasi'], $rekomendasi)) ?>;
            const rekData = <?= json_encode(array_map(fn($r) => $r['jumlah'], $rekomendasi)) ?>;
            
            new Chart(ctxRek, {
                type: 'doughnut',
                data: {
                    labels: rekLabels,
                    datasets: [{
                        data: rekData,
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                        borderColor: ['#fff', '#fff', '#fff'],
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>