<?php
require_once '../config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi data
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
        header("Location: ../admin/agenda.php?action=tambah");
        exit();
    }
    
    // Format tanggal dan waktu
    $start_datetime = $tanggal_mulai . ($jam_mulai ? ' ' . $jam_mulai . ':00' : ' 00:00:00');
    
    if (!empty($tanggal_selesai)) {
        $end_datetime = $tanggal_selesai . ($jam_selesai ? ' ' . $jam_selesai . ':00' : ' 00:00:00');
    } else {
        $end_datetime = $start_datetime;
    }
    
    // Simpan ke database
    $query = "INSERT INTO agenda (judul, deskripsi, tanggal_mulai, tanggal_selesai, 
              jam_mulai, jam_selesai, lokasi, jenis, peserta, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $judul, $deskripsi, $tanggal_mulai, $tanggal_selesai, 
        $jam_mulai, $jam_selesai, $lokasi, $jenis, $peserta);
    
    if (mysqli_stmt_execute($stmt)) {
        $agenda_id = mysqli_insert_id($conn);
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'tambah_agenda', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Menambah agenda: " . $judul . " (ID: " . $agenda_id . ")";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $_SESSION['user_id'], $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Kirim notifikasi jika agenda adalah penting
        if ($jenis == 'rapat' || $jenis == 'penting') {
            // Kirim email notifikasi ke admin (jika ada email admin)
            $admin_email = "admin@desa.lengkong"; // Ganti dengan email admin yang sesuai
            
            $subject = "Agenda Baru: " . $judul . " - " . SITE_NAME;
            $message = "
            <html>
            <head>
                <title>Agenda Baru</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #3498db; color: white; padding: 15px; text-align: center; }
                    .content { padding: 20px; background: #f9f9f9; }
                    .footer { background: #2c3e50; color: white; padding: 10px; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Agenda Baru</h2>
                    </div>
                    <div class='content'>
                        <h3>" . $judul . "</h3>
                        <p><strong>Deskripsi:</strong> " . nl2br($deskripsi) . "</p>
                        <p><strong>Tanggal:</strong> " . format_tanggal($tanggal_mulai) . 
                           ($tanggal_selesai ? " s/d " . format_tanggal($tanggal_selesai) : "") . "</p>
                        <p><strong>Waktu:</strong> " . ($jam_mulai ? $jam_mulai : '00:00') . 
                           " - " . ($jam_selesai ? $jam_selesai : '00:00') . "</p>
                        <p><strong>Lokasi:</strong> " . $lokasi . "</p>
                        <p><strong>Jenis:</strong> " . ucfirst($jenis) . "</p>
                        <p><strong>Peserta:</strong> " . $peserta . "</p>
                    </div>
                    <div class='footer'>
                        <p>" . SITE_NAME . " - Sistem Informasi Desa</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
            
            @mail($admin_email, $subject, $message, $headers);
        }
        
        $_SESSION['message'] = 'Agenda berhasil ditambahkan!';
        $_SESSION['message_type'] = 'success';
        header("Location: ../admin/agenda.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal menambahkan agenda: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../admin/agenda.php?action=tambah");
        exit();
    }
} else {
    header("Location: ../admin/agenda.php");
    exit();
}
?>