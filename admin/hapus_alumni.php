<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Akses ditolak");
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM alumni WHERE id_alumni = ?");
    if ($stmt->execute([$_GET['id']])) {
        header("Location: kelola_alumni.php?status=deleted");
        exit;
    }
}

header("Location: kelola_alumni.php");
exit;