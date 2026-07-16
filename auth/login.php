<?php
session_start();
require_once('../config/config.php');

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Cek di tabel admin
    $stmtAdmin = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmtAdmin->execute([$user]);
    $adminData = $stmtAdmin->fetch();

    if ($adminData && password_verify($pass, $adminData['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $adminData['id_admin'];
        $_SESSION['role'] = 'admin';
       header("Location: ../admin/dashboard.php");
        exit;
        }

    // Cek di tabel alumni
    $stmtAlumni = $pdo->prepare("SELECT * FROM alumni WHERE username = ?");
    $stmtAlumni->execute([$user]);
    $alumniData = $stmtAlumni->fetch();

    if ($alumniData && password_verify($pass, $alumniData['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $alumniData['id_alumni'];
        $_SESSION['role'] = 'alumni';
         header("Location: ../admin/dashboard.php");
        exit;
    }

    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/LOGO_USM.png">
</head>
<body class="login-page">
    <div class="card border-0 shadow-lg rounded-4 p-2 text-dark animate__animated animate__zoomIn" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 text-center">
            <img src="../assets/images/LOGO_USM.png" alt="Logo USM" class="logo-login mb-3">
            <h4 class="fw-bold mb-1 text-primary">Masuk ke Portal</h4>
            <p class="text-muted small mb-4">Gunakan akun Admin atau Alumni Anda</p>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger border-0 small py-2 mb-3 animate__animated animate__shakeX"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <input type="text" name="username" class="form-control bg-light border-0 py-2" placeholder="Masukkan username" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Masukkan password" required autocomplete="current-password">
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">MASUK SEKARANG</button>
            </form>
            
            <div class="mt-4">
                <a href="../index.php" class="text-decoration-none small text-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>