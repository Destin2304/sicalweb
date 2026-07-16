<?php
session_start();
require_once('../config/config.php');

// Proteksi: Hanya user yang sudah login yang bisa melihat daftar lowongan
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM lowongan";
$params = [];

if (!empty($search)) {
    $query .= " WHERE posisi LIKE ? OR perusahaan LIKE ? OR deskripsi LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$query .= " ORDER BY tanggal_post DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lowongan Kerja - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/LOGO_USM.png">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
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
                    <li class="nav-item"><a class="nav-link active" href="lowongan.php">Lowongan Kerja</a></li>
                    <li class="nav-item"><a class="nav-link" href="alumni.php">Direktori Alumni</a></li>
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-1">Peluang Karir</h5>
                    <p class="text-muted small mb-0">Temukan pekerjaan yang sesuai dengan keahlian Anda.</p>
                </div>
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex shadow-sm rounded">
                        <input type="text" name="search" class="form-control form-control-sm border-0" placeholder="Cari posisi atau perusahaan..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary px-4">Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <main class="container my-4 flex-grow-1">
        <?php if($search): ?>
            <p class="mb-4">Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search) ?>"</strong></p>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach($jobs as $job): ?>
            <div class="col-md-4">
                <div class="card h-100 job-card">
                    <div class="card-body">
                        <span class="badge bg-soft-primary text-primary mb-2">Full Time</span>
                        <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($job['posisi']) ?></h5>
                        <h6 class="card-subtitle mb-3 text-muted"><?= htmlspecialchars($job['perusahaan']) ?></h6>
                        <hr class="text-black-50">
                        <p class="card-text small text-secondary">
                            <i class="fas fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($job['tanggal_post'])) ?>
                        </p>
                        <a href="detail_lowongan.php?id=<?= $job['id_lowongan'] ?>" class="btn btn-outline-primary w-100 mt-2">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($jobs)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-light mb-3"></i>
                    <p class="text-muted">Tidak ada lowongan yang ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>