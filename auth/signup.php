<?php
session_start();
require_once '../config/config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $tahun_lulus = trim($_POST['tahun_lulus'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi
    if (empty($nama) || empty($jurusan) || empty($tahun_lulus) || empty($username) || empty($password) || empty($password_confirm)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $password_confirm) {
        $error = "Password tidak cocok!";
    } else {
        try {
            // Check apakah username sudah terdaftar
            $stmtCheck = $pdo->prepare("SELECT * FROM alumni WHERE username = ?");
            $stmtCheck->execute([$username]);
            $existing = $stmtCheck->fetch();

            if ($existing) {
                $error = "Username sudah terdaftar! Gunakan username lain.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert ke database
                $stmtInsert = $pdo->prepare("
                    INSERT INTO alumni (nama, jurusan, tahun_lulus, username, password) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtInsert->execute([$nama, $jurusan, $tahun_lulus, $username, $hashed_password]);

                $success = "Registrasi berhasil! Silakan login dengan username dan password Anda.";
                
                // Clear form
                $nama = '';
                $jurusan = '';
                $tahun_lulus = '';
                $username = '';
                $password = '';
                $password_confirm = '';
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Career Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .signup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .logo-img {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
        }
        .signup-title {
            font-size: 24px;
            font-weight: 800;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 10px;
        }
        .signup-subtitle {
            text-align: center;
            color: #999;
            font-size: 13px;
            margin-bottom: 25px;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .btn-signup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            margin-top: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .alert-danger {
            border-radius: 8px;
            border: none;
            margin-bottom: 15px;
        }
        .alert-success {
            border-radius: 8px;
            border: none;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <img src="../assets/images/LOGO_USM.png" alt="Logo" class="logo-img">
        <h2 class="signup-title">Daftar Sekarang</h2>
        <p class="signup-subtitle">Buat akun untuk melacak karir Anda</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required value="<?= htmlspecialchars($nama ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Jurusan *</label>
                <select name="jurusan" class="form-control" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <option value="Kimia" <?= ($jurusan ?? '') === 'Kimia' ? 'selected' : '' ?>>Kimia</option>
                    <option value="Sistem Informasi" <?= ($jurusan ?? '') === 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                    <option value="Manajemen" <?= ($jurusan ?? '') === 'Manajemen' ? 'selected' : '' ?>>Manajemen</option>
                    <option value="Akuntansi" <?= ($jurusan ?? '') === 'Akuntansi' ? 'selected' : '' ?>>Akuntansi</option>
                    <option value="Hukum" <?= ($jurusan ?? '') === 'Hukum' ? 'selected' : '' ?>>Hukum</option>
                    <option value="Keperawatan" <?= ($jurusan ?? '') === 'Keperawatan' ? 'selected' : '' ?>>Keperawatan</option>
                    <option value="Kesehatan Masyarakat" <?= ($jurusan ?? '') === 'Kesehatan Masyarakat' ? 'selected' : '' ?>>Kesehatan Masyarakat</option>
                    <option value="Psikologi" <?= ($jurusan ?? '') === 'Psikologi' ? 'selected' : '' ?>>Psikologi</option>
                    <option value="Ilmu Komunikasi" <?= ($jurusan ?? '') === 'Ilmu Komunikasi' ? 'selected' : '' ?>>Ilmu Komunikasi</option>
                    <option value="Perpustakaan & Sains Informasi" <?= ($jurusan ?? '') === 'Perpustakaan & Sains Informasi' ? 'selected' : '' ?>>Perpustakaan & Sains Informasi</option>
                    <option value="PGSD" <?= ($jurusan ?? '') === 'PGSD' ? 'selected' : '' ?>>PGSD</option>
                    <option value="PGPAUD" <?= ($jurusan ?? '') === 'PGPAUD' ? 'selected' : '' ?>>PGPAUD</option>
                    <option value="Farmasi" <?= ($jurusan ?? '') === 'Farmasi' ? 'selected' : '' ?>>Farmasi</option>
                    <option value="Bidan" <?= ($jurusan ?? '') === 'Bidan' ? 'selected' : '' ?>>Bidan</option>
                    <option value="TLM" <?= ($jurusan ?? '') === 'TLM' ? 'selected' : '' ?>>TLM</option>
                    <option value="TEM" <?= ($jurusan ?? '') === 'TEM' ? 'selected' : '' ?>>TEM</option>
                    <option value="Profesi Apoteker" <?= ($jurusan ?? '') === 'Profesi Apoteker' ? 'selected' : '' ?>>Profesi Apoteker</option>
                    <option value="Profesi Ners" <?= ($jurusan ?? '') === 'Profesi Ners' ? 'selected' : '' ?>>Profesi Ners</option>
                    <option value="Profesi Bidan" <?= ($jurusan ?? '') === 'Profesi Bidan' ? 'selected' : '' ?>>Profesi Bidan</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tahun Lulus *</label>
                <input type="number" name="tahun_lulus" class="form-control" placeholder="Contoh: 2024" min="2000" max="2099" required value="<?= htmlspecialchars($tahun_lulus ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username unik" required value="<?= htmlspecialchars($username ?? '') ?>">
                <small class="text-muted">Username hanya boleh huruf, angka, dan underscore</small>
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password" required minlength="6">
            </div>

            <button type="submit" class="btn btn-signup w-100">
                <i class="fas fa-user-plus me-2"></i>DAFTAR SEKARANG
            </button>
        </form>

        <div class="login-link">
            <p class="mb-0">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>