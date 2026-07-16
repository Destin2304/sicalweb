<?php
session_start();
require_once '../config/config.php';

$search_results = [];
$search_query = '';
$search_type = 'provinsi'; // provinsi atau nim

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['q'])) {
    $search_query = trim($_GET['q'] ?? $_POST['q'] ?? '');
    $search_type = $_GET['type'] ?? $_POST['type'] ?? 'provinsi';
    
    if (!empty($search_query)) {
        try {
            if ($search_type == 'nim') {
                $stmt = $pdo->prepare("SELECT * FROM alumni WHERE nim LIKE ? ORDER BY nama_alumni ASC");
                $stmt->execute(['%' . $search_query . '%']);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM alumni WHERE provinsi LIKE ? OR kota LIKE ? ORDER BY nama_alumni ASC");
                $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
            }
            $search_results = $stmt->fetchAll();
        } catch (Exception $e) {
            $search_results = [];
        }
    }
}

// Get provinsi list
try {
    $stmt = $pdo->query("SELECT DISTINCT provinsi FROM alumni WHERE provinsi IS NOT NULL ORDER BY provinsi ASC");
    $provinsi_list = $stmt->fetchAll();
} catch (Exception $e) {
    $provinsi_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Alumni - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; padding: 40px 20px; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; font-size: 18px; }
        .navbar-brand img { height: 40px; margin-right: 10px; }
        .search-container { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 40px; margin-bottom: 40px; }
        .search-title { font-size: 28px; font-weight: 800; color: #2c3e50; margin-bottom: 30px; text-align: center; }
        .search-form { margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 700; color: #2c3e50; margin-bottom: 10px; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 15px; font-size: 15px; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15); }
        .btn-search { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-size: 16px; transition: all 0.3s; }
        .btn-search:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); color: white; }
        .alumni-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; transition: all 0.3s; }
        .alumni-card:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .alumni-name { font-size: 18px; font-weight: 800; color: #2c3e50; margin-bottom: 10px; }
        .alumni-info { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px; }
        .info-item { display: flex; align-items: center; gap: 8px; color: #666; font-size: 14px; }
        .info-item i { color: #667eea; width: 20px; }
        .no-results { text-align: center; padding: 40px; color: #999; }
        .no-results i { font-size: 48px; margin-bottom: 20px; }
        .results-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 700; color: #2c3e50; }
        .btn-back { color: white; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 20px; border-radius: 8px; transition: all 0.3s; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); color: white; text-decoration: none; }
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
            <div class="ms-auto">
                <a href="../admin/dashboard.php" class="btn btn-light btn-sm">
                    <i class="fas fa-user-circle me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>

        <div class="search-container">
            <h1 class="search-title">
                <i class="fas fa-search me-2" style="color: #667eea;"></i>Cari Alumni USM
            </h1>

            <form method="GET" class="search-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipe Pencarian</label>
                            <select name="type" class="form-select" onchange="updatePlaceholder()">
                                <option value="provinsi" <?= $search_type == 'provinsi' ? 'selected' : '' ?>>Cari Berdasarkan Provinsi</option>
                                <option value="nim" <?= $search_type == 'nim' ? 'selected' : '' ?>>Cari Berdasarkan NIM</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Masukkan Pencarian</label>
                            <input type="text" name="q" class="form-control" placeholder="Contoh: Medan atau 2020001001" value="<?= htmlspecialchars($search_query) ?>" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search me-2"></i>Cari Alumni
                </button>
            </form>

            <!-- Quick Select Provinsi -->
            <?php if ($search_type == 'provinsi' && !empty($provinsi_list)): ?>
                <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e0e0e0;">
                    <h6 style="color: #2c3e50; font-weight: 700; margin-bottom: 15px;">Atau Pilih Provinsi:</h6>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach (array_slice($provinsi_list, 0, 12) as $prov): ?>
                            <a href="?type=provinsi&q=<?= urlencode($prov['provinsi'] ?? '') ?>" class="btn btn-outline-primary btn-sm">
                                <?= htmlspecialchars($prov['provinsi'] ?? '') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Results -->
        <?php if (!empty($search_query)): ?>
            <div class="results-info">
                <i class="fas fa-info-circle me-2"></i>
                Ditemukan <?= count($search_results) ?> alumni
                <?php if ($search_type == 'nim'): ?>
                    dengan NIM: <strong><?= htmlspecialchars($search_query) ?></strong>
                <?php else: ?>
                    di Provinsi: <strong><?= htmlspecialchars($search_query) ?></strong>
                <?php endif; ?>
            </div>

            <?php if (!empty($search_results)): ?>
                <div>
                    <?php foreach ($search_results as $alumni): ?>
                        <div class="alumni-card">
                            <div class="alumni-name">
                                <i class="fas fa-user-circle" style="color: #667eea; margin-right: 10px;"></i>
                                <?= htmlspecialchars($alumni['nama_alumni'] ?? '-') ?>
                            </div>
                            <div class="alumni-info">
                                <div class="info-item">
                                    <i class="fas fa-id-card"></i>
                                    <strong>NIM:</strong> <?= htmlspecialchars($alumni['nim'] ?? '-') ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <strong>Jurusan:</strong> <?= htmlspecialchars($alumni['jurusan'] ?? '-') ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Tahun Lulus:</strong> <?= htmlspecialchars($alumni['tahun_lulus'] ?? '-') ?>
                                </div>
                            </div>
                            <div class="alumni-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Provinsi:</strong> <?= htmlspecialchars($alumni['provinsi'] ?? '-') ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-city"></i>
                                    <strong>Kota:</strong> <?= htmlspecialchars($alumni['kota'] ?? '-') ?>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <strong>Email:</strong> <?= htmlspecialchars($alumni['email'] ?? '-') ?>
                                </div>
                            </div>
                            <?php if ($alumni['no_hp'] ?? false): ?>
                                <div class="alumni-info">
                                    <div class="info-item">
                                        <i class="fas fa-phone"></i>
                                        <strong>HP:</strong> <?= htmlspecialchars($alumni['no_hp'] ?? '-') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="color: #ccc;"></i>
                    <p><strong>Tidak ada hasil</strong></p>
                    <p>Silakan coba pencarian dengan data yang berbeda</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function updatePlaceholder() {
            const type = document.querySelector('select[name="type"]').value;
            const input = document.querySelector('input[name="q"]');
            if (type === 'nim') {
                input.placeholder = 'Contoh: 2020001001';
            } else {
                input.placeholder = 'Contoh: Medan';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>