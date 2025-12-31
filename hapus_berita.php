<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data berita untuk mendapatkan nama gambar
    $query = "SELECT judul, gambar FROM berita WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $berita = mysqli_fetch_assoc($result);
        $judul = $berita['judul'];
        $gambar = $berita['gambar'];
        
        // Hapus gambar jika ada
        if ($gambar) {
            $file_path = '../uploads/berita/' . $gambar;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Hapus tags terlebih dahulu
        $delete_tags = "DELETE FROM berita_tags WHERE berita_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_tags);
        mysqli_stmt_bind_param($delete_stmt, "i", $id);
        mysqli_stmt_execute($delete_stmt);
        
        // Hapus berita dari database
        $query = "DELETE FROM berita WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                         VALUES (?, 'hapus_berita', ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $description = "Menghapus berita: " . $judul . " (ID: " . $id . ")";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
            mysqli_stmt_execute($log_stmt);
            
            $_SESSION['message'] = 'Berita berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
            header("Location: ../admin/berita.php");
            exit();
        } else {
            $_SESSION['message'] = 'Gagal menghapus berita: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
            header("Location: ../admin/berita.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Berita tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/berita.php");
        exit();
    }
} else {
    header("Location: ../admin/berita.php");
    exit();
}
?>