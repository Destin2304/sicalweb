<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'alumni';

// Safe web settings check
$logo = '../assets/images/LOGO_USM.png';
$nama_instansi = 'USM Indonesia';

if (isset($web_settings) && is_array($web_settings)) {
    if (!empty($web_settings['logo'])) {
        $logo = $web_settings['logo'];
    }
    if (!empty($web_settings['nama_instansi'])) {
        $nama_instansi = $web_settings['nama_instansi'];
    }
}
?>

<div class="sidebar p-3 d-flex flex-column shadow">

    <!-- Logo & Title -->
    <div class="text-center mb-4">
        <img src="<?= htmlspecialchars($logo) ?>" width="80" class="mb-3" alt="Logo" onerror="this.src='../assets/images/LOGO_USM.png'">
        <h5 class="text-white fw-bold mb-1"><?= htmlspecialchars($nama_instansi) ?></h5>
        <small class="text-light">Career Center Portal</small>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto">

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="dashboard.php"
               class="nav-link <?= in_array($current_page, ['dashboard.php', 'edit_lowongan.php', 'tambah_lowongan.php']) ? 'active' : '' ?>">
                <i class="fas fa-home me-2"></i>
                Dashboard
            </a>
        </li>

        <!-- Admin Only Menus -->
        <?php if ($role === 'admin'): ?>

        <li class="nav-item mt-2">
            <a href="kelola_alumni.php"
               class="nav-link <?= $current_page === 'kelola_alumni.php' ? 'active' : '' ?>">
                <i class="fas fa-users me-2"></i>
                Kelola Alumni
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="tambah_alumni.php"
               class="nav-link <?= $current_page === 'tambah_alumni.php' ? 'active' : '' ?>">
                <i class="fas fa-user-plus me-2"></i>
                Tambah Alumni
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="tambah_lowongan.php"
               class="nav-link <?= $current_page === 'tambah_lowongan.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle me-2"></i>
                Tambah Lowongan
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="laporan.php"
               class="nav-link <?= $current_page === 'laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar me-2"></i>
                Laporan Tracer
            </a>
        </li>

        <li class="nav-item mt-2">
            <a href="pengaturan_web.php"
               class="nav-link <?= $current_page === 'pengaturan_web.php' ? 'active' : '' ?>">
                <i class="fas fa-cog me-2"></i>
                Pengaturan Web
            </a>
        </li>

        <?php endif; ?>

        <!-- Alumni Only Menus -->
        <?php if ($role === 'alumni'): ?>

        <li class="nav-item mt-2">
            <a href="tracer_study.php"
               class="nav-link <?= $current_page === 'tracer_study.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt me-2"></i>
                Tracer Study
            </a>
        </li>

        <?php endif; ?>

    </ul>

    <!-- Divider -->
    <hr class="border-secondary">

    <!-- Bottom Links -->
    <div class="mt-auto">

        <a href="../index.php" class="nav-link text-light mb-2">
            <i class="fas fa-globe me-2"></i>
            Lihat Website
        </a>

        <a href="../auth/logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt me-2"></i>
            Keluar
        </a>

    </div>

</div>