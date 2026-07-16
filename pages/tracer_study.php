<?php
session_start();
require_once('../config/config.php');

// Proteksi halaman Alumni
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}

$id_alumni = $_SESSION['user_id'];

// Ambil pesan sukses dari session (jika ada)
$success = $_SESSION['success_msg'] ?? "";
unset($_SESSION['success_msg']);

// Ambil data lama jika ada
$stmt_fetch = $pdo->prepare("SELECT * FROM tracer_study WHERE id_alumni = ?");
$stmt_fetch->execute([$id_alumni]);
$existing_data = $stmt_fetch->fetch();

// Default values
$pekerjaan = $existing_data['pekerjaan'] ?? '';
$perusahaan = $existing_data['nama_perusahaan'] ?? '';
$gaji = $existing_data['gaji'] ?? '';
$kesesuaian = $existing_data['kesesuaian'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pekerjaan = $_POST['pekerjaan'];
    $perusahaan = $_POST['nama_perusahaan'];
    $gaji = $_POST['gaji'];
    $kesesuaian = $_POST['kesesuaian'];

    // Cek apakah sudah pernah mengisi
    $check = $pdo->prepare("SELECT id_tracer FROM tracer_study WHERE id_alumni = ?");
    $check->execute([$id_alumni]);
    
    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE tracer_study SET pekerjaan = ?, nama_perusahaan = ?, gaji = ?, kesesuaian = ? WHERE id_alumni = ?");
        $stmt->execute([$pekerjaan, $perusahaan, $gaji, $kesesuaian, $id_alumni]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO tracer_study (id_alumni, pekerjaan, nama_perusahaan, gaji, kesesuaian) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_alumni, $pekerjaan, $perusahaan, $gaji, $kesesuaian]);
    }
    
    $_SESSION['success_msg'] = "Data Tracer Study berhasil diperbarui!";
    header("Location: tracer_study.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracer Study - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="LOGO_USM.png">
</head>
<body class="bg-light d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container my-4 flex-grow-1">
            <!-- Judul di Paling Atas (Style Laporan) -->
            <div class="report-header text-center mb-4">
                <img src="LOGO_USM.png" alt="Logo USM" style="height: 40px; margin-bottom: 5px;">
                <h2>LAPORAN DATA TRACER STUDY ALUMNI</h2>
                <p class="fw-bold">Universitas Sari Mutiara Indonesia</p>
                <div class="mt-1" style="font-size: 0.65rem; color: #666;">
                    Formulir Pemutakhiran Data Lulusan
                </div>
            </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-4 border-bottom pb-2"><i class="fas fa-edit me-2"></i>Lengkapi Formulir</h6>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Pekerjaan Saat Ini</label>
                                <input type="text" name="pekerjaan" class="form-control" placeholder="Contoh: Senior Programmer" value="<?= htmlspecialchars($pekerjaan) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Instansi/Perusahaan</label>
                                <input type="text" name="nama_perusahaan" class="form-control" value="<?= htmlspecialchars($perusahaan) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Rentang Pendapatan</label>
                                <select name="gaji" class="form-select" required>
                                    <option value="< 3 Juta" <?= $gaji == '< 3 Juta' ? 'selected' : '' ?>>< 3 Juta</option>
                                    <option value="3 - 7 Juta" <?= $gaji == '3 - 7 Juta' ? 'selected' : '' ?>>3 - 7 Juta</option>
                                    <option value="> 7 Juta" <?= $gaji == '> 7 Juta' ? 'selected' : '' ?>>> 7 Juta</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Kesesuaian dengan Jurusan</label>
                                <select name="kesesuaian" class="form-select" required>
                                    <option value="Sangat Sesuai" <?= $kesesuaian == 'Sangat Sesuai' ? 'selected' : '' ?>>Sangat Sesuai</option>
                                    <option value="Sesuai" <?= $kesesuaian == 'Sesuai' ? 'selected' : '' ?>>Sesuai</option>
                                    <option value="Tidak Sesuai" <?= $kesesuaian == 'Tidak Sesuai' ? 'selected' : '' ?>>Tidak Sesuai</option>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold">Simpan Data Tracer</button>
                                <a href="dashboard.php" class="btn btn-light border">Kembali ke Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    </div>
</body>
</html>