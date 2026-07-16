<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$success = '';
$error = '';
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$nim_validated = false;
$alumni_data = null;

// STEP 1: Validasi NIM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $step == 1) {
    $nim = trim($_POST['nim'] ?? '');
    
    if (empty($nim)) {
        $error = "NIM harus diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM alumni WHERE nim = ?");
            $stmt->execute([$nim]);
            $alumni_data = $stmt->fetch();
            
            if ($alumni_data) {
                $nim_validated = true;
                $step = 2;
            } else {
                $error = "NIM tidak ditemukan dalam database! Silakan cek kembali.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// STEP 2: Submit Tracer Study Data
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $step == 2) {
    $nim = trim($_POST['nim'] ?? '');
    $status_alumni = $_POST['status_alumni'] ?? 'Bekerja';
    $nama_perusahaan = $_POST['nama_perusahaan'] ?? '';
    $posisi_kerja = $_POST['posisi_kerja'] ?? '';
    $jenis_instansi = $_POST['jenis_instansi'] ?? 'Swasta';
    $bulan_tunggu_kerja = (int)($_POST['bulan_tunggu_kerja'] ?? 0);
    $cara_memperoleh_kerja = $_POST['cara_memperoleh_kerja'] ?? 'Referral Teman';
    $lokasi_kerja = $_POST['lokasi_kerja'] ?? '';
    $provinsi_kerja = $_POST['provinsi_kerja'] ?? '';
    $gaji_awal = (int)($_POST['gaji_awal'] ?? 0);
    $tipe_pekerjaan = $_POST['tipe_pekerjaan'] ?? 'Penuh Waktu';
    $kesesuaian_bidang = $_POST['kesesuaian_bidang'] ?? 'Sesuai';
    $alasan_ketidaksesuaian = $_POST['alasan_ketidaksesuaian'] ?? '';
    $saran_masukan = $_POST['saran_masukan'] ?? '';

    try {
        // Get alumni data
        $stmt = $pdo->prepare("SELECT * FROM alumni WHERE nim = ?");
        $stmt->execute([$nim]);
        $alumni_data = $stmt->fetch();

        if ($alumni_data) {
            // Check if already exists
            $stmt = $pdo->prepare("SELECT id_tracer FROM tracer_study WHERE nim = ?");
            $stmt->execute([$nim]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update
                $stmt = $pdo->prepare("UPDATE tracer_study SET status_alumni=?, nama_perusahaan=?, posisi_kerja=?, jenis_instansi=?, bulan_tunggu_kerja=?, cara_memperoleh_kerja=?, lokasi_kerja=?, provinsi_kerja=?, gaji_awal=?, tipe_pekerjaan=?, kesesuaian_bidang=?, alasan_ketidaksesuaian=?, saran_masukan=? WHERE nim=?");
                $stmt->execute([$status_alumni, $nama_perusahaan, $posisi_kerja, $jenis_instansi, $bulan_tunggu_kerja, $cara_memperoleh_kerja, $lokasi_kerja, $provinsi_kerja, $gaji_awal, $tipe_pekerjaan, $kesesuaian_bidang, $alasan_ketidaksesuaian, $saran_masukan, $nim]);
                $success = "Data Tracer Study berhasil diupdate!";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO tracer_study (id_alumni, nim, nama_alumni, jurusan, status_alumni, nama_perusahaan, posisi_kerja, jenis_instansi, bulan_tunggu_kerja, cara_memperoleh_kerja, lokasi_kerja, provinsi_kerja, gaji_awal, tipe_pekerjaan, kesesuaian_bidang, alasan_ketidaksesuaian, saran_masukan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $alumni_data['id_alumni'], $nim, $alumni_data['nama_alumni'], $alumni_data['jurusan'],
                    $status_alumni, $nama_perusahaan, $posisi_kerja, $jenis_instansi, $bulan_tunggu_kerja,
                    $cara_memperoleh_kerja, $lokasi_kerja, $provinsi_kerja, $gaji_awal, $tipe_pekerjaan,
                    $kesesuaian_bidang, $alasan_ketidaksesuaian, $saran_masukan
                ]);
                $success = "Data Tracer Study berhasil disimpan!";
            }
            $step = 1; // Reset ke step 1
            $nim_validated = false;
        } else {
            $error = "Data alumni tidak ditemukan!";
            $step = 2;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        $step = 2;
    }
}

// Get provinsi list
try {
    $stmt = $pdo->query("SELECT * FROM provinsi ORDER BY nama_provinsi ASC");
    $provinsi_list = $stmt->fetchAll();
} catch (Exception $e) {
    $provinsi_list = [];
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
    <title>Form Tracer Study - USM Indonesia</title>
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
        .btn-submit { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); color: white; }
        .btn-back { color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .btn-back:hover { color: #764ba2; }
        .alert { border-radius: 8px; border: none; }
        .alumni-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .step-indicator { display: flex; gap: 20px; margin-bottom: 30px; }
        .step { text-align: center; }
        .step-number { width: 40px; height: 40px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 10px; }
        .step.active .step-number { background: #28a745; color: white; }
        .step.completed .step-number { background: #667eea; color: white; }
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
                <i class="fas fa-briefcase" style="color: #28a745;"></i>
                Form Tracer Study Alumni
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <!-- STEP 1: Validasi NIM -->
            <?php if ($step == 1 && !$nim_validated): ?>
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <small>Validasi NIM</small>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <small>Input Data</small>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <div class="form-group">
                        <label><i class="fas fa-id-card me-2"></i>NIM Alumni *</label>
                        <input type="text" name="nim" class="form-control form-control-lg" placeholder="Masukkan NIM alumni (cth: 2020001001)" required autofocus>
                        <small class="text-muted">Contoh format: 2020001001</small>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check me-2"></i>Validasi NIM
                    </button>
                </form>
            <?php endif; ?>

            <!-- STEP 2: Input Data Tracer Study -->
            <?php if ($step == 2 && $alumni_data): ?>
                <div class="step-indicator">
                    <div class="step completed">
                        <div class="step-number"><i class="fas fa-check"></i></div>
                        <small>Validasi NIM</small>
                    </div>
                    <div class="step active">
                        <div class="step-number">2</div>
                        <small>Input Data</small>
                    </div>
                </div>

                <div class="alumni-info">
                    <h6><i class="fas fa-user-check me-2"></i>Data Alumni Tervalidasi</h6>
                    <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($alumni_data['nama_alumni']) ?></p>
                    <p class="mb-1"><strong>NIM:</strong> <?= htmlspecialchars($alumni_data['nim']) ?></p>
                    <p class="mb-0"><strong>Jurusan:</strong> <?= htmlspecialchars($alumni_data['jurusan']) ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="nim" value="<?= htmlspecialchars($alumni_data['nim']) ?>">

                    <h5 class="mt-4 mb-3">Status Pekerjaan</h5>
                    <div class="form-group">
                        <label>Status Alumni *</label>
                        <select name="status_alumni" class="form-select" required>
                            <option value="Bekerja">Bekerja</option>
                            <option value="Wirausaha">Wirausaha</option>
                            <option value="Studi Lanjut">Studi Lanjut</option>
                            <option value="Belum Bekerja">Belum Bekerja</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Perusahaan/Organisasi</label>
                                <input type="text" name="nama_perusahaan" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Posisi/Jabatan</label>
                                <input type="text" name="posisi_kerja" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Instansi</label>
                                <select name="jenis_instansi" class="form-select">
                                    <option value="BUMN">BUMN</option>
                                    <option value="Swasta" selected>Swasta</option>
                                    <option value="Pemerintah">Pemerintah</option>
                                    <option value="NGO">NGO</option>
                                    <option value="Startup">Startup</option>
                                    <option value="Wirausaha Mandiri">Wirausaha Mandiri</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Pekerjaan</label>
                                <select name="tipe_pekerjaan" class="form-select">
                                    <option value="Penuh Waktu" selected>Penuh Waktu</option>
                                    <option value="Paruh Waktu">Paruh Waktu</option>
                                    <option value="Kontrak">Kontrak</option>
                                    <option value="Magang">Magang</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3">Informasi Pekerjaan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bulan Tunggu Kerja (sejak lulus)</label>
                                <input type="number" name="bulan_tunggu_kerja" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cara Memperoleh Pekerjaan</label>
                                <select name="cara_memperoleh_kerja" class="form-select">
                                    <option value="On-campus Recruitment">On-campus Recruitment</option>
                                    <option value="Website Lowongan">Website Lowongan</option>
                                    <option value="Referral Teman" selected>Referral Teman</option>
                                    <option value="Media Sosial">Media Sosial</option>
                                    <option value="Agen Kerja">Agen Kerja</option>
                                    <option value="Melamar Langsung">Melamar Langsung</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lokasi Kerja</label>
                                <input type="text" name="lokasi_kerja" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provinsi</label>
                                <select name="provinsi_kerja" class="form-select">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <?php foreach ($provinsi_list as $prov): ?>
                                        <option value="<?= htmlspecialchars($prov['nama_provinsi']) ?>">
                                            <?= htmlspecialchars($prov['nama_provinsi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gaji Awal (Rp)</label>
                        <input type="number" name="gaji_awal" class="form-control" value="0" min="0">
                    </div>

                    <h5 class="mt-4 mb-3">Kesesuaian & Saran</h5>
                    <div class="form-group">
                        <label>Kesesuaian Bidang dengan Program Studi</label>
                        <select name="kesesuaian_bidang" class="form-select">
                            <option value="Sangat Sesuai">Sangat Sesuai</option>
                            <option value="Sesuai" selected>Sesuai</option>
                            <option value="Kurang Sesuai">Kurang Sesuai</option>
                            <option value="Tidak Sesuai">Tidak Sesuai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Alasan Ketidaksesuaian (jika ada)</label>
                        <textarea name="alasan_ketidaksesuaian" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Saran & Masukan untuk Universitas</label>
                        <textarea name="saran_masukan" class="form-control" rows="3"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save me-2"></i>Simpan Data Tracer Study
                        </button>
                        <a href="tracer_study_form.php" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i>Validasi Alumni Lain
                        </a>
                    </div>
                </form>
            <?php endif; ?>
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