<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data agenda untuk logging
    $query = "SELECT judul FROM agenda WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $agenda = mysqli_fetch_assoc($result);
        $judul = $agenda['judul'];
        
        // Hapus agenda dari database
        $query = "DELETE FROM agenda WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                         VALUES (?, 'hapus_agenda', ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $description = "Menghapus agenda: " . $judul . " (ID: " . $id . ")";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
            mysqli_stmt_execute($log_stmt);
            
            $_SESSION['message'] = 'Agenda berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
            header("Location: ../admin/agenda.php");
            exit();
        } else {
            $_SESSION['message'] = 'Gagal menghapus agenda: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
            header("Location: ../admin/agenda.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Agenda tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/agenda.php");
        exit();
    }
} else {
    header("Location: ../admin/agenda.php");
    exit();
}
?>