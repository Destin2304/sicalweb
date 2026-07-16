<?php
session_start();
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id_alumni, nama_alumni, nim, jurusan, tahun_lulus, email, no_hp, provinsi, kota, status FROM alumni WHERE status = 'aktif' ORDER BY nama_alumni ASC");
    $alumni = $stmt->fetchAll();
    
    echo json_encode($alumni);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    http_response_code(500);
}
?>