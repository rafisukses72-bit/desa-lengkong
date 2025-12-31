<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data potensi untuk mendapatkan nama gambar dan logging
    $query = "SELECT nama, gambar, status FROM potensi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $potensi = mysqli_fetch_assoc($result);
        $nama = $potensi['nama'];
        $gambar = $potensi['gambar'];
        $status = $potensi['status'];
        
        // Hapus gambar jika ada
        if ($gambar) {
            $file_path = '../uploads/potensi/' . $gambar;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus potensi dari database
        $query = "DELETE FROM potensi WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update statistik jika potensi aktif
            if ($status == 'aktif') {
                $stats_query = "UPDATE pengaturan SET potensi_aktif = GREATEST(potensi_aktif - 1, 0) WHERE id = 1";
                mysqli_query($conn, $stats_query);
            }
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                         VALUES (?, 'hapus_potensi', ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $description = "Menghapus potensi: " . $nama . " (ID: " . $id . ")";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
            mysqli_stmt_execute($log_stmt);
            
            $_SESSION['message'] = 'Potensi berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
            header("Location: ../admin/potensi.php");
            exit();
        } else {
            $_SESSION['message'] = 'Gagal menghapus potensi: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
            header("Location: ../admin/potensi.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Potensi tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/potensi.php");
        exit();
    }
} else {
    header("Location: ../admin/potensi.php");
    exit();
}
?>