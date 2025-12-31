<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Ambil data lama untuk perbandingan
    $query_old = "SELECT * FROM agenda WHERE id = ?";
    $stmt_old = mysqli_prepare($conn, $query_old);
    mysqli_stmt_bind_param($stmt_old, "i", $id);
    mysqli_stmt_execute($stmt_old);
    $result_old = mysqli_stmt_get_result($stmt_old);
    
    if (mysqli_num_rows($result_old) == 0) {
        $_SESSION['message'] = 'Agenda tidak ditemukan!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/agenda.php");
        exit();
    }
    
    $old_agenda = mysqli_fetch_assoc($result_old);
    
    // Ambil dan sanitasi data baru
    $judul = sanitize_input($_POST['judul']);
    $deskripsi = $_POST['deskripsi'];
    $tanggal_mulai = sanitize_input($_POST['tanggal_mulai']);
    $tanggal_selesai = sanitize_input($_POST['tanggal_selesai']);
    $jam_mulai = sanitize_input($_POST['jam_mulai']);
    $jam_selesai = sanitize_input($_POST['jam_selesai']);
    $lokasi = sanitize_input($_POST['lokasi']);
    $jenis = sanitize_input($_POST['jenis']);
    $peserta = sanitize_input($_POST['peserta']);
    
    // Validasi data
    $errors = [];
    
    if (empty($judul)) {
        $errors[] = "Judul agenda harus diisi.";
    }
    
    if (empty($tanggal_mulai)) {
        $errors[] = "Tanggal mulai harus diisi.";
    } elseif (!strtotime($tanggal_mulai)) {
        $errors[] = "Format tanggal mulai tidak valid.";
    }
    
    if (!empty($tanggal_selesai) && !strtotime($tanggal_selesai)) {
        $errors[] = "Format tanggal selesai tidak valid.";
    }
    
    if (!empty($jam_mulai) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam_mulai)) {
        $errors[] = "Format jam mulai tidak valid (HH:MM).";
    }
    
    if (!empty($jam_selesai) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam_selesai)) {
        $errors[] = "Format jam selesai tidak valid (HH:MM).";
    }
    
    // Jika ada error, tampilkan pesan error
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/agenda.php?action=edit&id=" . $id);
        exit();
    }
    
    // Update database
    $query = "UPDATE agenda SET judul = ?, deskripsi = ?, tanggal_mulai = ?, tanggal_selesai = ?, 
              jam_mulai = ?, jam_selesai = ?, lokasi = ?, jenis = ?, peserta = ?, updated_at = NOW() 
              WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssssi", 
        $judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, 
        $jam_mulai, $jam_selesai, $lokasi, $jenis, $peserta, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity dengan detail perubahan
        $changes = [];
        
        if ($old_agenda['judul'] != $judul) {
            $changes[] = "Judul: '" . $old_agenda['judul'] . "' → '" . $judul . "'";
        }
        if ($old_agenda['tanggal_mulai'] != $tanggal_mulai) {
            $changes[] = "Tanggal mulai: " . $old_agenda['tanggal_mulai'] . " → " . $tanggal_mulai;
        }
        if ($old_agenda['lokasi'] != $lokasi) {
            $changes[] = "Lokasi: '" . $old_agenda['lokasi'] . "' → '" . $lokasi . "'";
        }
        if ($old_agenda['jenis'] != $jenis) {
            $changes[] = "Jenis: " . $old_agenda['jenis'] . " → " . $jenis;
        }
        
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'edit_agenda', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Mengedit agenda: " . $judul . " (ID: " . $id . ")";
        if (!empty($changes)) {
            $description .= " - Perubahan: " . implode(", ", $changes);
        }
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        $_SESSION['message'] = 'Agenda berhasil diperbarui!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/agenda.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal memperbarui agenda: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/agenda.php?action=edit&id=" . $id);
        exit();
    }
} else {
    header("Location: ../admin/agenda.php");
    exit();
}
?>