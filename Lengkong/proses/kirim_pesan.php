<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize_input($_POST['nama']);
    $email = sanitize_input($_POST['email']);
    $telepon = sanitize_input($_POST['telepon']);
    $subjek = sanitize_input($_POST['subjek']);
    $isi_pesan = sanitize_input($_POST['isi_pesan']);
    
    // Validasi input
    if (empty($nama) || empty($subjek) || empty($isi_pesan)) {
        $_SESSION['message'] = 'Harap lengkapi semua field yang wajib!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../kontak.php");
        exit();
    }
    
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = 'Format email tidak valid!';
        $_SESSION['message_type'] = 'error';
        header("Location: ../kontak.php");
        exit();
    }
    
    // Simpan ke database
    $query = "INSERT INTO pesan (nama, email, telepon, subjek, isi_pesan, status) 
              VALUES (?, ?, ?, ?, ?, 'belum_dibaca')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $telepon, $subjek, $isi_pesan);
    
    if (mysqli_stmt_execute($stmt)) {
        // Kirim email notifikasi ke admin (jika email diisi)
        if ($email) {
            $to = ADMIN_EMAIL; // Email admin dari config
            $subject = "Pesan Baru dari Website: " . $subjek;
            $message = "
            <html>
            <head>
                <title>Pesan Baru dari Website</title>
            </head>
            <body>
                <h2>Pesan Baru dari Website</h2>
                <p><strong>Nama:</strong> $nama</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Telepon:</strong> $telepon</p>
                <p><strong>Subjek:</strong> $subjek</p>
                <p><strong>Isi Pesan:</strong><br>$isi_pesan</p>
                <hr>
                <p><small>Pesan ini dikirim melalui formulir kontak website.</small></p>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
            
            @mail($to, $subject, $message, $headers);
        }
        
        $_SESSION['message'] = 'Pesan Anda telah berhasil dikirim! Terima kasih.';
        $_SESSION['message_type'] = 'success';
        header("Location: ../kontak.php");
        exit();
    } else {
        $_SESSION['message'] = 'Gagal mengirim pesan. Silakan coba lagi.';
        $_SESSION['message_type'] = 'error';
        header("Location: ../kontak.php");
        exit();
    }
} else {
    header("Location: ../kontak.php");
    exit();
}
?>