<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_instansi = $_POST['nama_instansi'] ?? '';
    $industri = $_POST['industri'] ?? '';
    $alamat_instansi = $_POST['alamat_instansi'] ?? '';
    $provinsi_instansi = $_POST['provinsi_instansi'] ?? '';
    $email_instansi = $_POST['email_instansi'] ?? '';
    $no_telp_instansi = $_POST['no_telp_instansi'] ?? '';
    $nama_responden = $_POST['nama_responden'] ?? '';
    $posisi_responden = $_POST['posisi_responden'] ?? '';
    $nama_alumni = $_POST['nama_alumni'] ?? '';
    $posisi_alumni = $_POST['posisi_alumni'] ?? '';
    $lama_bekerja_bulan = (int)($_POST['lama_bekerja_bulan'] ?? 0);
    
    $integritas = (int)($_POST['integritas'] ?? 3);
    $etika = (int)($_POST['etika'] ?? 3);
    $profesionalisme = (int)($_POST['profesionalisme'] ?? 3);
    $komunikasi = (int)($_POST['komunikasi'] ?? 3);
    $kerja_tim = (int)($_POST['kerja_tim'] ?? 3);
    $kepemimpinan = (int)($_POST['kepemimpinan'] ?? 3);
    $teknologi_informasi = (int)($_POST['teknologi_informasi'] ?? 3);
    $bahasa_asing = (int)($_POST['bahasa_asing'] ?? 3);
    
    $kepuasan_umum = (int)($_POST['kepuasan_umum'] ?? 3);
    $rekomendasi = $_POST['rekomendasi'] ?? 'Ya';
    $kekuatan_alumni = $_POST['kekuatan_alumni'] ?? '';
    $kelemahan_alumni = $_POST['kelemahan_alumni'] ?? '';
    $saran_perbaikan = $_POST['saran_perbaikan'] ?? '';

    if (empty($nama_instansi) || empty($nama_responden)) {
        $error = "Nama Instansi dan Nama Responden harus diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO survey_pengguna (nama_instansi, industri, alamat_instansi, provinsi_instansi, email_instansi, no_telp_instansi, nama_responden, posisi_responden, nama_alumni, posisi_alumni, lama_bekerja_bulan, integritas, etika, profesionalisme, komunikasi, kerja_tim, kepemimpinan, teknologi_informasi, bahasa_asing, kepuasan_umum, rekomendasi, kekuatan_alumni, kelemahan_alumni, saran_perbaikan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $nama_instansi, $industri, $alamat_instansi, $provinsi_instansi, $email_instansi, $no_telp_instansi,
                $nama_responden, $posisi_responden, $nama_alumni, $posisi_alumni, $lama_bekerja_bulan,
                $integritas, $etika, $profesionalisme, $komunikasi, $kerja_tim, $kepemimpinan, $teknologi_informasi, $bahasa_asing,
                $kepuasan_umum, $rekomendasi, $kekuatan_alumni, $kelemahan_alumni, $saran_perbaikan
            ]);
            
            $success = "Survey berhasil disimpan! Terima kasih atas masukan berharga Anda.";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
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
    <title>Form Survey Pengguna - USM Indonesia</title>
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
        .btn-submit { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3); color: white; }
        .btn-back { color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .btn-back:hover { color: #764ba2; }
        .rating-group { display: flex; gap: 10px; margin-top: 10px; }
        .rating-btn { width: 50px; height: 50px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; cursor: pointer; font-weight: 700; transition: all 0.3s; }
        .rating-btn:hover { border-color: #ffc107; }
        .rating-btn.selected { background: #ffc107; color: white; border-color: #ffc107; }
        .section-title { font-size: 18px; font-weight: 800; color: #2c3e50; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ffc107; }
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
                <i class="fas fa-star" style="color: #ffc107;"></i>
                Form Survey Pengguna Alumni
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- Identitas Instansi -->
                <div class="section-title"><i class="fas fa-building me-2"></i>Identitas Instansi</div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Instansi/Perusahaan *</label>
                            <input type="text" name="nama_instansi" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Industri</label>
                            <input type="text" name="industri" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Instansi</label>
                    <textarea name="alamat_instansi" class="form-control" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Provinsi</label>
                            <select name="provinsi_instansi" class="form-select">
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
                            <label>Email Instansi</label>
                            <input type="email" name="email_instansi" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>No. Telepon Instansi</label>
                    <input type="text" name="no_telp_instansi" class="form-control">
                </div>

                <!-- Identitas Responden -->
                <div class="section-title"><i class="fas fa-user me-2"></i>Identitas Responden</div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Responden *</label>
                            <input type="text" name="nama_responden" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Posisi Responden</label>
                            <input type="text" name="posisi_responden" class="form-control" placeholder="Contoh: HRD Manager, Atasan Langsung">
                        </div>
                    </div>
                </div>

                <!-- Data Alumni -->
                <div class="section-title"><i class="fas fa-id-badge me-2"></i>Data Alumni yang Dinilai</div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Alumni</label>
                            <input type="text" name="nama_alumni" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Posisi Alumni di Perusahaan</label>
                            <input type="text" name="posisi_alumni" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Lama Bekerja (Bulan)</label>
                    <input type="number" name="lama_bekerja_bulan" class="form-control" value="0" min="0">
                </div>

                <!-- Penilaian Kompetensi -->
                <div class="section-title"><i class="fas fa-chart-star me-2"></i>Penilaian Kompetensi Alumni (Skala 1-5)</div>
                
                <p class="text-muted small mb-3">Keterangan: 1 = Sangat Kurang, 2 = Kurang, 3 = Cukup, 4 = Baik, 5 = Sangat Baik</p>

                <?php 
                $kompetensi = [
                    'integritas' => 'Integritas & Kejujuran',
                    'etika' => 'Etika Profesional',
                    'profesionalisme' => 'Profesionalisme',
                    'komunikasi' => 'Kemampuan Komunikasi',
                    'kerja_tim' => 'Kerja Sama Tim',
                    'kepemimpinan' => 'Kepemimpinan',
                    'teknologi_informasi' => 'Penguasaan Teknologi Informasi',
                    'bahasa_asing' => 'Kemampuan Bahasa Asing'
                ];
                foreach ($kompetensi as $key => $label):
                ?>
                <div class="form-group">
                    <label><?= $label ?></label>
                    <div class="rating-group">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="rating-btn" onclick="setRating('<?= $key ?>', <?= $i ?>)" data-name="<?= $key ?>" data-value="<?= $i ?>">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="<?= $key ?>" value="3">
                </div>
                <?php endforeach; ?>

                <!-- Kepuasan Umum -->
                <div class="section-title"><i class="fas fa-thumbs-up me-2"></i>Tingkat Kepuasan</div>
                
                <div class="form-group">
                    <label>Tingkat Kepuasan Terhadap Alumni (Skala 1-5)</label>
                    <div class="rating-group">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="rating-btn" onclick="setRating('kepuasan_umum', <?= $i ?>)" data-name="kepuasan_umum" data-value="<?= $i ?>">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="kepuasan_umum" value="3">
                </div>

                <div class="form-group">
                    <label>Apakah Anda merekomendasikan alumni kami untuk posisi sejenis?</label>
                    <select name="rekomendasi" class="form-select">
                        <option value="Ya">Ya, sangat merekomendasikan</option>
                        <option value="Mungkin">Mungkin, tergantung kebutuhan</option>
                        <option value="Tidak">Tidak, tidak merekomendasikan</option>
                    </select>
                </div>

                <!-- Saran & Masukan -->
                <div class="section-title"><i class="fas fa-comments me-2"></i>Saran & Masukan</div>
                
                <div class="form-group">
                    <label>Kekuatan Alumni</label>
                    <textarea name="kekuatan_alumni" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Kelemahan Alumni</label>
                    <textarea name="kelemahan_alumni" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Saran Perbaikan untuk Program Pendidikan</label>
                    <textarea name="saran_perbaikan" class="form-control" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-send me-2"></i>Kirim Survey
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

        function setRating(name, value) {
            document.querySelector(`input[name="${name}"]`).value = value;
            document.querySelectorAll(`button[data-name="${name}"]`).forEach(btn => {
                btn.classList.remove('selected');
                if (parseInt(btn.dataset.value) <= value) {
                    btn.classList.add('selected');
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>