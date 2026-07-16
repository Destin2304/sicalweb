<?php
session_start();
require_once('../config/config.php');

// Proteksi: Hanya user (Alumni/Admin) yang sudah login yang bisa melihat direktori
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM alumni";
$params = [];

if (!empty($search)) {
    $query .= " WHERE nama LIKE ? OR jurusan LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alumni_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Direktori Alumni - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/LOGO_USM.png">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
                <img src="../assets/images/LOGO_USM.png" alt="Logo" class="logo-navbar me-2">
                <span>USM Indonesia</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="lowongan.php">Lowongan Kerja</a></li>
                    <li class="nav-item"><a class="nav-link active" href="alumni.php">Direktori Alumni</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="btn btn-outline-primary ms-lg-3 py-1" href="../admin/dashboard.php"><i class="fas fa-user-circle me-1"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary ms-lg-3 py-1" href="../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="bg-light py-3 border-bottom">
        <div class="container">
            <h5 class="fw-bold text-dark mb-1">Direktori Alumni</h5>
            <p class="text-muted small mb-0">Temukan dan jalin kembali jejaring dengan lulusan USM Indonesia.</p>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex bg-white p-1 rounded-pill shadow-sm">
                        <input type="text" name="search" class="form-control form-control-sm border-0 rounded-pill px-3" placeholder="Cari nama atau jurusan..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary btn-sm rounded-pill px-4 ms-1"><i class="fas fa-search me-1"></i> Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <main class="container my-4 flex-grow-1">
        <?php if($search): ?>
            <p class="mb-4">Hasil pencarian alumni untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong></p>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach($alumni_list as $alumni): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 animate__animated animate__fadeInUp">
                <div class="card h-100 border-0 shadow-sm text-center p-4 job-card">
                    <div class="alumni-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-body p-0">
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($alumni['nama'] ?? $alumni['username']) ?></h6>
                        <p class="text-primary small fw-medium mb-1"><?= htmlspecialchars($alumni['jurusan'] ?? 'Alumni') ?></p>
                        <span class="badge bg-light text-secondary rounded-pill border fw-normal">Lulusan <?= htmlspecialchars($alumni['tahun_lulus'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($alumni_list)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                    <p class="text-muted">Data alumni tidak ditemukan. Coba gunakan kata kunci lain.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>