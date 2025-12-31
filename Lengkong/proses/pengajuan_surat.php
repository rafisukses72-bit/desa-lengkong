<?php
require_once '../config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data user
    $user_id = $_SESSION['user_id'];
    
    // Ambil data pengajuan
    $jenis_layanan = sanitize_input($_POST['jenis_layanan']);
    $keperluan = sanitize_input($_POST['keperluan']);
    $catatan_tambahan = sanitize_input($_POST['catatan_tambahan']);
    
    // Ambil data penduduk berdasarkan user_id
    $query_penduduk = "SELECT nik, nama, alamat FROM penduduk WHERE user_id = ?";
    $stmt_penduduk = mysqli_prepare($conn, $query_penduduk);
    mysqli_stmt_bind_param($stmt_penduduk, "i", $user_id);
    mysqli_stmt_execute($stmt_penduduk);
    $result_penduduk = mysqli_stmt_get_result($stmt_penduduk);
    
    if (mysqli_num_rows($result_penduduk) == 0) {
        $_SESSION['message'] = 'Data penduduk tidak ditemukan. Silakan lengkapi profil Anda terlebih dahulu.';
        $_SESSION['message_type'] = 'error';
        header("Location: ../pengajuan.php");
        exit();
    }
    
    $penduduk = mysqli_fetch_assoc($result_penduduk);
    $nik = $penduduk['nik'];
    $nama_pemohon = $penduduk['nama'];
    $alamat = $penduduk['alamat'];
    
    // Validasi data
    $errors = [];
    
    if (empty($jenis_layanan)) {
        $errors[] = "Jenis layanan harus dipilih.";
    }
    
    if (empty($keperluan)) {
        $errors[] = "Keperluan pengajuan harus diisi.";
    }
    
    // Handle upload berkas pendukung (opsional)
    $berkas = null;
    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == 0) {
        $allowed_types = [
            'application/pdf',
            'image/jpeg', 'image/jpg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['berkas']['type'], $allowed_types)) {
            $errors[] = "Hanya file PDF, JPG, PNG, DOC, dan DOCX yang diizinkan.";
        } elseif ($_FILES['berkas']['size'] > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            $upload_dir = '../uploads/pengajuan/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION);
            $filename = 'berkas_' . $nik . '_' . date('YmdHis') . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['berkas']['tmp_name'], $target_file)) {
                $berkas = $filename;
            } else {
                $errors[] = "Gagal mengupload berkas.";
            }
        }
    }
    
    // Jika ada error, tampilkan pesan error
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../pengajuan.php");
        exit();
    }
    
    // Generate kode pengajuan
    $kode_pengajuan = 'PEN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    
    // Simpan ke database
    $query = "INSERT INTO pengajuan_surat (kode_pengajuan, user_id, nama_pemohon, nik, alamat, 
              jenis_layanan, keperluan, berkas, status, catatan_tambahan, tanggal_pengajuan) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sisssssss", 
        $kode_pengajuan, $user_id, $nama_pemohon, $nik, $alamat, 
        $jenis_layanan, $keperluan, $berkas, $catatan_tambahan);
    
    if (mysqli_stmt_execute($stmt)) {
        $pengajuan_id = mysqli_insert_id($conn);
        
        // Log activity
        $log_query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (?, 'pengajuan_surat', ?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_query);
        $description = "Mengajukan surat: " . $jenis_layanan . " (Kode: " . $kode_pengajuan . ")";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($log_stmt, "isss", $user_id, $description, $ip_address, $user_agent);
        mysqli_stmt_execute($log_stmt);
        
        // Kirim notifikasi email ke admin
        $admin_email = "admin@desa.lengkong"; // Ganti dengan email admin
        
        $subject = "Pengajuan Surat Baru - " . SITE_NAME . " [" . $kode_pengajuan . "]";
        $message = "
        <html>
        <head>
            <title>Pengajuan Surat Baru</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f39c12; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .footer { background: #2c3e50; color: white; padding: 15px; text-align: center; }
                .info-box { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 20px 0; }
                .status { display: inline-block; padding: 5px 15px; background: #f39c12; color: white; border-radius: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Pengajuan Surat Baru</h2>
                </div>
                <div class='content'>
                    <div class='info-box'>
                        <h3>Detail Pengajuan</h3>
                        <p><strong>Kode Pengajuan:</strong> " . $kode_pengajuan . "</p>
                        <p><strong>Jenis Layanan:</strong> " . $jenis_layanan . "</p>
                        <p><strong>Tanggal Pengajuan:</strong> " . date('d F Y H:i') . "</p>
                        <p><strong>Status:</strong> <span class='status'>Menunggu Verifikasi</span></p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Data Pemohon</h3>
                        <p><strong>Nama:</strong> " . $nama_pemohon . "</p>
                        <p><strong>NIK:</strong> " . $nik . "</p>
                        <p><strong>Alamat:</strong> " . $alamat . "</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Detail Pengajuan</h3>
                        <p><strong>Keperluan:</strong> " . nl2br($keperluan) . "</p>
                        " . ($catatan_tambahan ? "<p><strong>Catatan Tambahan:</strong> " . nl2br($catatan_tambahan) . "</p>" : "") . "
                    </div>
                    
                    <p>Silakan login ke panel admin untuk memproses pengajuan ini.</p>
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
        
        // Kirim konfirmasi email ke pemohon
        if ($_SESSION['email']) {
            $user_subject = "Konfirmasi Pengajuan Surat - " . SITE_NAME . " [" . $kode_pengajuan . "]";
            $user_message = "
            <html>
            <head>
                <title>Konfirmasi Pengajuan Surat</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                    .content { padding: 30px; background: #f9f9f9; }
                    .footer { background: #2c3e50; color: white; padding: 15px; text-align: center; }
                    .info-box { background: #fff; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 20px 0; }
                    .status { display: inline-block; padding: 5px 15px; background: #f39c12; color: white; border-radius: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Konfirmasi Pengajuan Surat</h2>
                    </div>
                    <div class='content'>
                        <p>Halo <strong>" . $nama_pemohon . "</strong>,</p>
                        <p>Pengajuan surat Anda telah berhasil diterima. Berikut detail pengajuan:</p>
                        
                        <div class='info-box'>
                            <h3>Detail Pengajuan</h3>
                            <p><strong>Kode Pengajuan:</strong> " . $kode_pengajuan . "</p>
                            <p><strong>Jenis Layanan:</strong> " . $jenis_layanan . "</p>
                            <p><strong>Tanggal Pengajuan:</strong> " . date('d F Y H:i') . "</p>
                            <p><strong>Status:</strong> <span class='status'>Menunggu Verifikasi</span></p>
                        </div>
                        
                        <p><strong>Proses Selanjutnya:</strong></p>
                        <ol>
                            <li>Admin akan memverifikasi pengajuan Anda</li>
                            <li>Anda akan mendapatkan notifikasi ketika status berubah</li>
                            <li>Anda dapat melacak status pengajuan di akun Anda</li>
                        </ol>
                        
                        <p>Terima kasih telah menggunakan layanan online desa kami.</p>
                    </div>
                    <div class='footer'>
                        <p>" . SITE_NAME . " - Sistem Informasi Desa</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $user_headers = "MIME-Version: 1.0" . "\r\n";
            $user_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $user_headers .= "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
            
            @mail($_SESSION['email'], $user_subject, $user_message, $user_headers);
        }
        
        // Set session success message
        $_SESSION['message'] = 'Pengajuan surat berhasil! Kode pengajuan: ' . $kode_pengajuan . '. Silakan cek email untuk konfirmasi.';
        $_SESSION['message_type'] = 'success';
        
        // Redirect ke halaman status pengajuan
        header("Location: ../status-pengajuan.php");
        exit();
        
    } else {
        $_SESSION['message'] = 'Gagal mengajukan surat: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
        header("Location: ../pengajuan.php");
        exit();
    }
} else {
    header("Location: ../pengajuan.php");
    exit();
}
?>