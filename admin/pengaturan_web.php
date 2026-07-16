<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$status = $_GET['status'] ?? '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nama = $_POST['nama_instansi'] ?? '';
        
        // Update Nama Instansi
        $stmt = $pdo->prepare("UPDATE settings SET nama_instansi = ? WHERE id = 1");
        $stmt->execute([$nama]);

        // Handle Upload Logo jika ada
        if (!empty($_FILES['logo']['name'])) {
            $target_dir = "../assets/images/";
            
            // Buat folder jika tidak ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
            $allowed_ext = ['png', 'jpg', 'jpeg', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                throw new Exception("Format file tidak didukung. Gunakan: PNG, JPG, JPEG, GIF");
            }
            
            $new_filename = "LOGO_USM." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("UPDATE settings SET logo = ? WHERE id = 1");
                $stmt->execute([$target_file]);
            } else {
                throw new Exception("Gagal upload logo");
            }
        }

        header("Location: pengaturan_web.php?status=success");
        exit;
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// Get user name
$user_name = 'Administrator';
try {
    $stmt_u = $pdo->prepare("SELECT username FROM admin WHERE id_admin = ?");
    $stmt_u->execute([$_SESSION['user_id']]);
    $u_data = $stmt_u->fetch(PDO::FETCH_ASSOC);
    if ($u_data) $user_name = $u_data['username'];
} catch (Exception $e) {
    // Keep default
}

if (!isset($web_settings)) {
    $web_settings = [
        'logo' => '../assets/images/LOGO_USM.png',
        'nama_instansi' => 'USM Indonesia'
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Web - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/LOGO_USM.png">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .clock {
            font-weight: 700;
            color: white;
            font-size: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8px 18px;
            border-radius: 25px;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        .nav-profile .dropdown-toggle::after {
            display: none;
        }

        .nav-profile img {
            height: 45px;
            width: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin-top: 10px;
        }

        .container-wrapper {
            padding: 40px 20px;
            max-width: 900px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            overflow: hidden;
            background: white;
        }

        .card-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            display: block;
            font-size: 15px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9ff;
        }

        .form-control:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .logo-preview {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f1ff 100%);
            border-radius: 12px;
            margin-bottom: 20px;
            border: 2px dashed #667eea;
        }

        .logo-preview img {
            max-height: 120px;
            max-width: 100%;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }

        .form-text {
            font-size: 13px;
            color: #999;
            margin-top: 8px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            width: 100%;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .alert {
            border: none;
            border-left: 4px solid;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .alert-success {
            background: linear-gradient(90deg, #e6f7f0 0%, #f0fdf8 100%);
            border-left-color: #28a745;
            color: #0d3622;
        }

        .alert-danger {
            background: linear-gradient(90deg, #ffe6e6 0%, #fff0f0 100%);
            border-left-color: #dc3545;
            color: #721c24;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-label {
            display: block;
            padding: 12px 16px;
            background: #f8f9ff;
            border: 2px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-label:hover {
            background: #f0f1ff;
            border-color: #764ba2;
        }

        footer {
            background: white;
            border-top: 2px solid #e0e0e0;
            padding: 30px;
            text-align: center;
            color: #999;
            margin-top: 40px;
            box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .container-wrapper {
                padding: 20px 15px;
            }

            .card-body {
                padding: 25px;
            }

            .page-title {
                font-size: 24px;
                margin-bottom: 25px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content flex-grow-1">
        <!-- Topbar -->
        <nav class="topbar">
            <div class="clock" id="digitalClock">
                <i class="far fa-clock me-2"></i>00:00:00
            </div>
            <div class="d-flex align-items-center gap-3">
                <div style="width: 1px; height: 30px; background: #ddd;"></div>
                <div class="dropdown nav-profile">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="text-end">
                            <div class="text-dark fw-bold" style="font-size: 14px;">Halo, <?= htmlspecialchars($user_name) ?></div>
                            <div class="text-muted" style="font-size: 12px;">Admin</div>
                        </div>
                        <img src="../assets/images/default_profile.png" alt="Profile" onerror="this.src='../assets/images/LOGO_USM.png'">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil Saya</a></li>
                        <li><a class="dropdown-item" href="laporan.php"><i class="fas fa-chart-bar me-2"></i>Laporan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-wrapper">
            <h1 class="page-title"><i class="fas fa-sliders-h me-2"></i>Pengaturan Web</h1>

            <?php if ($status === 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Berhasil!</strong> Pengaturan web berhasil diperbarui.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error!</strong> <?= htmlspecialchars($error_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-building me-2 text-primary"></i>Nama Instansi / Universitas
                            </label>
                            <input type="text" name="nama_instansi" class="form-control" 
                                   value="<?= htmlspecialchars($web_settings['nama_instansi'] ?? '') ?>" 
                                   placeholder="Contoh: Universitas Sari Mutiara Indonesia" 
                                   required>
                            <p class="form-text"><i class="fas fa-info-circle me-1"></i>Nama ini akan muncul di seluruh aplikasi.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image me-2 text-primary"></i>Logo Instansi
                            </label>
                            
                            <div class="logo-preview">
                                <img id="logoPreview" src="<?= htmlspecialchars($web_settings['logo'] ?? '') ?>" 
                                     alt="Logo" 
                                     style="display: <?= !empty($web_settings['logo']) ? 'block' : 'none'; ?>"
                                     onerror="this.style.display='none'">
                                <p id="noLogo" class="text-muted" style="display: <?= empty($web_settings['logo']) ? 'block' : 'none'; ?>; margin: 0;">
                                    <i class="fas fa-image fa-2x mb-2" style="opacity: 0.3;"></i><br>
                                    Belum ada logo
                                </p>
                            </div>

                            <div class="file-input-wrapper">
                                <input type="file" name="logo" id="logoInput" accept="image/*">
                                <label for="logoInput" class="file-label">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>
                                    Klik untuk memilih logo atau drag & drop di sini
                                </label>
                            </div>
                            <p class="form-text"><i class="fas fa-info-circle me-1"></i>Format: PNG, JPG, JPEG, GIF. Ukuran maksimal 5MB.</p>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <p><i class="fas fa-copyright me-1"></i><?= date('Y') ?> Universitas Sari Mutiara Indonesia — Career Center Portal</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('digitalClock').innerHTML = `<i class="far fa-clock me-2"></i>${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Logo Preview
        const logoInput = document.getElementById('logoInput');
        logoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                    document.getElementById('logoPreview').style.display = 'block';
                    document.getElementById('noLogo').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop
        const fileLabel = document.querySelector('.file-label');
        fileLabel.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileLabel.style.background = '#f0f1ff';
            fileLabel.style.borderColor = '#764ba2';
        });
        fileLabel.addEventListener('dragleave', () => {
            fileLabel.style.background = '#f8f9ff';
            fileLabel.style.borderColor = '#667eea';
        });
        fileLabel.addEventListener('drop', (e) => {
            e.preventDefault();
            logoInput.files = e.dataTransfer.files;
            logoInput.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>