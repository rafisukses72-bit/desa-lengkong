<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data galeri untuk mendapatkan nama gambar
    $query = "SELECT judul, gambar FROM galeri WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $galeri = mysqli_fetch_assoc($result);
        $judul = $galeri['judul'];
        $gambar = $galeri['gambar'];
        
        // Hapus gambar dan thumbnail jika ada
        if ($gambar) {
            $upload_dir = '../uploads/galeri/';
            if (file_exists($upload_dir . $gambar)) {
                unlink($upload_dir . $gambar);
            }
            if (file_exists($upload_dir . 'thumbs/' . $gambar)) {
                unlink($upload_dir . 'thumbs/' . $gambar);
            }
        }
        
        // Hapus dari database
        $query = "DELETE FROM galeri WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update statistik galeri
            $stats_query = "UPDATE pengaturan SET total_galeri = GREATEST(total_galeri - 1, 0) WHERE id = 1";
            mysqli_query($conn, $stats_query);
            
            // Log activity
            $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                         VALUES (?, 'hapus_galeri', ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $description = "Menghapus foto dari galeri: " . $judul . " (ID: " . $id . ")";
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
            mysqli_stmt_execute($log_stmt);
            
            $_SESSION['message'] = 'Foto berhasil dihapus dari galeri!';
            $_SESSION['message_type'] = 'success';
            header("Location: ../admin/galeri.php");
            exit();
        } else {
            $_SESSION['message'] = 'Gagal menghapus foto: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
            header("Location: ../admin/galeri.php");
            exit();
        }
    } else {
        $_SESSION['message'] = 'Foto tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/galeri.php");
        exit();
    }
} else {
    header("Location: ../admin/galeri.php");
    exit();
}
?>