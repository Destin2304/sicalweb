<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$success = '';
$error = '';

// Get provinsi list
try {
    $stmt = $pdo->query("SELECT * FROM provinsi ORDER BY nama_provinsi ASC");
    $provinsi_list = $stmt->fetchAll();
} catch (Exception $e) {
    $provinsi_list = [];
}

// Get alumni list
try {
    $stmt = $pdo->query("SELECT id_alumni, nama_alumni, nim FROM alumni ORDER BY nama_alumni ASC");
    $alumni_list = $stmt->fetchAll();
} catch (Exception $e) {
    $alumni_list = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_alumni = $_POST['id_alumni'] ?? null;
    $nim = $_POST['nim'] ?? '';
    $nama_alumni = $_POST['nama_alumni'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    $provinsi = $_POST['provinsi'] ?? '';
    $kota = $_POST['kota'] ?? '';
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'] ?? '';
    $rencana_karir = $_POST['rencana_karir'] ?? '';
    $keahlian_utama = $_POST['keahlian_utama'] ?? '';
    $sertifikasi = $_POST['sertifikasi'] ?? '';
    $status_kontak = $_POST['status_kontak'] ?? 'aktif';
    $catatan = $_POST['catatan'] ?? '';

    // Validasi
    if (empty($nim) || empty($nama_alumni) || empty($email)) {
        $error = "NIM, Nama Alumni, dan Email harus diisi!";
    } else {
        try {
            // Check if already exists
            $stmt = $pdo->prepare("SELECT id_pra_tracer FROM pra_tracer_study WHERE nim = ?");
            $stmt->execute([$nim]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update
                $stmt = $pdo->prepare("UPDATE pra_tracer_study SET nama_alumni=?, email=?, no_hp=?, provinsi=?, kota=?, pendidikan_terakhir=?, rencana_karir=?, keahlian_utama=?, sertifikasi=?, status_kontak=?, catatan=? WHERE nim=?");
                $stmt->execute([$nama_alumni, $email, $no_hp, $provinsi, $kota, $pendidikan_terakhir, $rencana_karir, $keahlian_utama, $sertifikasi, $status_kontak, $catatan, $nim]);
                $success = "Data Pra Tracer Study berhasil diupdate!";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO pra_tracer_study (id_alumni, nim, nama_alumni, email, no_hp, provinsi, kota, pendidikan_terakhir, rencana_karir, keahlian_utama, sertifikasi, status_kontak, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_alumni, $nim, $nama_alumni, $email, $no_hp, $provinsi, $kota, $pendidikan_terakhir, $rencana_karir, $keahlian_utama, $sertifikasi, $status_kontak, $catatan]);
                $success = "Data Pra Tracer Study berhasil disimpan!";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
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
    <title>Form Pra Tracer Study - USM Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-title { font-size: 24px; font-weight: 800; color: #2c3e50; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 700; margin-bottom: 8px; color: #2c3e50; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px 15px; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15); }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); color: white; }
        .btn-back { color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .btn-back:hover { color: #764ba2; }
        .alert { border-radius: 8px; border: none; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-section">
            <img src="../assets/images/LOGO_USM.png" alt="Logo" onerror="this.src='data:image/svg+xml,<svg></svg>'">
            <h5>UNIVERSITAS SARI MUTIARA INDONESIA</h5>
            <small>Career Center Portal</small>
        </div>
        <div class="nav-item" onclick="location.href='dashboard.php'"><i class="fas fa-home"></i> Dashboard</div>
        <div class="nav-item active" onclick="location.href='kelola_alumni.php'"><i class="fas fa-users"></i> Kelola Alumni</div>
        <div class="nav-item" onclick="location.href='tambah_alumni.php'"><i class="fas fa-user-plus"></i> Tambah Alumni</div>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
        <div class="nav-item" onclick="location.href='../auth/logout.php'" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Keluar</div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="clock" id="digitalClock"><i class="far fa-clock me-2"></i>00:00:00</div>
            <div><small class="text-muted">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></small></div>
        </div>

        <a href="kelola_alumni.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>

        <div class="form-container">
            <div class="form-title">
                <i class="fas fa-clipboard-list" style="color: #667eea;"></i>
                Form Pra Tracer Study Alumni
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>NIM Alumni *</label>
                            <input type="text" name="nim" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Alumni *</label>
                            <input type="text" name="nama_alumni" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No. HP</label>
                            <input type="text" name="no_hp" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Provinsi</label>
                            <select name="provinsi" class="form-select">
                                <option value="">-- Pilih Provinsi --</option>
                                <?php foreach ($provinsi_list as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov['nama_provinsi']) ?>">
                                        <?= htmlspecialchars($prov['nama_provinsi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kota</label>
                            <input type="text" name="kota" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir" class="form-select">
                        <option value="">-- Pilih Pendidikan --</option>
                        <option value="SMA/SMK">SMA/SMK</option>
                        <option value="D3">D3</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Rencana Karir</label>
                    <textarea name="rencana_karir" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Keahlian Utama</label>
                    <textarea name="keahlian_utama" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Sertifikasi/Pelatihan</label>
                    <textarea name="sertifikasi" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Status Kontak</label>
                    <select name="status_kontak" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non-Aktif</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Catatan Admin</label>
                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                    <a href="kelola_alumni.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                </div>
            </form>
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>