<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Akses ditolak");
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM lowongan WHERE id_lowongan = ?");
    if ($stmt->execute([$_GET['id']])) {
        header("Location: dashboard.php?status=deleted");
        exit;
    }
}

header("Location: ../admin/dashboard.php");
exit;