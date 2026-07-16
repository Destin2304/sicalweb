<?php
// Footer untuk semua halaman admin
?>
<footer class="bg-white border-top py-4 mt-auto">
    <div class="container-fluid px-4">
        <div class="row">
            <div class="col-md-8">
                <p class="text-muted small mb-0">
                    &copy; <?= date('Y') ?> 
                    <?= htmlspecialchars($web_settings['nama_instansi'] ?? 'Universitas Sari Mutiara Indonesia') ?>
                    — Career Center Portal
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="text-muted small mb-0">
                    <i class="fas fa-code me-1"></i>Powered by Career Alumni System v1.0
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    footer {
        box-shadow: 0 -2px 4px rgba(0,0,0,0.05);
    }

    footer p {
        margin: 0;
        font-size: 13px;
    }

    @media print {
        footer {
            display: none !important;
        }
    }
</style>