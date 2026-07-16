<?php
session_start();
require_once '../config/config.php';

// Proteksi halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM lowongan WHERE id_lowongan = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    header("Location: ../admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $posisi = $_POST['posisi'];
    $perusahaan = $_POST['perusahaan'];
    $tipe = $_POST['tipe'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $pdo->prepare("UPDATE lowongan SET posisi = ?, perusahaan = ?, tipe = ?, deskripsi = ? WHERE id_lowongan = ?");
    if ($stmt->execute([$posisi, $perusahaan, $tipe, $deskripsi, $id])) {
        header("Location: dashboard.php?status=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Lowongan - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/LOGO_USM.png">
</head>
<body class="bg-light d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container my-4 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0 fw-bold">Edit Lowongan Kerja</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Posisi Pekerjaan</label>
                                <input type="text" name="posisi" class="form-control" value="<?= htmlspecialchars($job['posisi']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Perusahaan</label>
                                <input type="text" name="perusahaan" class="form-control" value="<?= htmlspecialchars($job['perusahaan']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipe Lowongan</label>
                                <select name="tipe" class="form-select" required>
                                    <option value="Kerja" <?= $job['tipe'] == 'Kerja' ? 'selected' : '' ?>>Lowongan Kerja</option>
                                    <option value="Magang" <?= $job['tipe'] == 'Magang' ? 'selected' : '' ?>>Program Magang</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Deskripsi Pekerjaan</label>
                                <textarea name="deskripsi" class="form-control" rows="5" required><?= htmlspecialchars($job['deskripsi']) ?></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                                <a href="admin/dashboard.php" class="btn btn-light border px-4">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php  include '../includes/sidebar.php'; ?>
    </div>
</body>
</html>