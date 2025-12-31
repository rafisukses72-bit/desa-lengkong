<?php
session_start();
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk validasi URL yang lebih fleksibel
function validate_url($url) {
    if (empty($url)) return '';
    
    // Tambahkan https:// jika tidak ada protokol
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
    
    return $url;
}

// Fungsi untuk menampilkan pesan
function showMessage($message, $type = 'success') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
}

// Fungsi untuk menampilkan alert
function displayAlert() {
    if (isset($_SESSION['alert_message'])) {
        $type = $_SESSION['alert_type'];
        $message = $_SESSION['alert_message'];
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
    }
}

// Pastikan tabel ada
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'pengaturan'");
if (mysqli_num_rows($check_table) == 0) {
    // Buat tabel
    $create_table = "CREATE TABLE pengaturan (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_desa VARCHAR(100) NOT NULL DEFAULT 'Desa Lengkong',
        motto_desa VARCHAR(200),
        alamat_kantor TEXT,
        telepon VARCHAR(20),
        email VARCHAR(100),
        website VARCHAR(200),
        facebook VARCHAR(200),
        instagram VARCHAR(200),
        youtube VARCHAR(200),
        twitter VARCHAR(200),
        whatsapp VARCHAR(20),
        latitude VARCHAR(20),
        longitude VARCHAR(20),
        tentang_desa TEXT,
        sejarah_desa TEXT,
        visi TEXT,
        misi TEXT,
        logo VARCHAR(100),
        favicon VARCHAR(100),
        theme_color VARCHAR(7) DEFAULT '#2c3e50'
    )";
    
    mysqli_query($conn, $create_table);
    
    // Insert default data
    mysqli_query($conn, "INSERT INTO pengaturan (id, nama_desa) VALUES (1, 'Desa Lengkong')");
}

// Ambil data saat ini
$result = mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1");
$data = mysqli_fetch_assoc($result);

// PROSES SIMPAN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: log data POST
    error_log("POST Data diterima: " . print_r($_POST, true));
    
    // Ambil data dari form
    $nama_desa = mysqli_real_escape_string($conn, trim($_POST['nama_desa'] ?? ''));
    
    // Validasi field required
    if (empty($nama_desa)) {
        showMessage('❌ Nama Desa harus diisi!', 'danger');
    } else {
        // Data lainnya
        $motto_desa = mysqli_real_escape_string($conn, trim($_POST['motto_desa'] ?? ''));
        $alamat_kantor = mysqli_real_escape_string($conn, trim($_POST['alamat_kantor'] ?? ''));
        $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
        $website = mysqli_real_escape_string($conn, trim($_POST['website'] ?? ''));
        $facebook = mysqli_real_escape_string($conn, trim($_POST['facebook'] ?? ''));
        $instagram = mysqli_real_escape_string($conn, trim($_POST['instagram'] ?? ''));
        $youtube = mysqli_real_escape_string($conn, trim($_POST['youtube'] ?? ''));
        $twitter = mysqli_real_escape_string($conn, trim($_POST['twitter'] ?? ''));
        $whatsapp = mysqli_real_escape_string($conn, trim($_POST['whatsapp'] ?? ''));
        $latitude = mysqli_real_escape_string($conn, trim($_POST['latitude'] ?? ''));
        $longitude = mysqli_real_escape_string($conn, trim($_POST['longitude'] ?? ''));
        $tentang_desa = mysqli_real_escape_string($conn, trim($_POST['tentang_desa'] ?? ''));
        $sejarah_desa = mysqli_real_escape_string($conn, trim($_POST['sejarah_desa'] ?? ''));
        $visi = mysqli_real_escape_string($conn, trim($_POST['visi'] ?? ''));
        $misi = mysqli_real_escape_string($conn, trim($_POST['misi'] ?? ''));
        $theme_color = mysqli_real_escape_string($conn, trim($_POST['theme_color'] ?? '#2c3e50'));
        
        // Format URL yang user-friendly
        $website = validate_url($website);
        $facebook = validate_url($facebook);
        $instagram = validate_url($instagram);
        $youtube = validate_url($youtube);
        $twitter = validate_url($twitter);
        
        // Handle file uploads
        $logo = $data['logo'] ?? '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../uploads/logo/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                $logo = $filename;
            }
        }
        
        $favicon = $data['favicon'] ?? '';
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
            $upload_dir = '../uploads/logo/';
            $file_ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
            $filename = 'favicon_' . time() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $upload_dir . $filename)) {
                $favicon = $filename;
            }
        }
        
        // QUERY UPDATE
        $sql = "UPDATE pengaturan SET 
                nama_desa = '$nama_desa',
                motto_desa = '$motto_desa',
                alamat_kantor = '$alamat_kantor',
                telepon = '$telepon',
                email = '$email',
                website = '$website',
                facebook = '$facebook',
                instagram = '$instagram',
                youtube = '$youtube',
                twitter = '$twitter',
                whatsapp = '$whatsapp',
                latitude = '$latitude',
                longitude = '$longitude',
                tentang_desa = '$tentang_desa',
                sejarah_desa = '$sejarah_desa',
                visi = '$visi',
                misi = '$misi',
                logo = '$logo',
                favicon = '$favicon',
                theme_color = '$theme_color'
                WHERE id = 1";
        
        // Debug: log SQL query
        error_log("SQL Query: " . $sql);
        
        // Eksekusi query - INI YANG DIPERBAIKI
        if (mysqli_query($conn, $sql)) {
            error_log("SUCCESS: Data berhasil disimpan");
            showMessage('✅ Pengaturan berhasil disimpan!', 'success');
            
            // Refresh data SETELAH berhasil
            $result = mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1");
            $data = mysqli_fetch_assoc($result);
            
            // Redirect untuk menghindari resubmit
            header("Location: " . $_SERVER['PHP_SELF'] . "?saved=1");
            exit();
        } else {
            error_log("ERROR: " . mysqli_error($conn));
            showMessage('❌ Gagal menyimpan: ' . mysqli_error($conn), 'danger');
        }
    }
}

// Tampilkan status saved
if (isset($_GET['saved'])) {
    showMessage('✅ Pengaturan berhasil disimpan!', 'success');
}

// Refresh data untuk ditampilkan di form (setelah redirect)
$result = mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1");
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .settings-container { 
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
        }
        
        .settings-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(39, 174, 96, 0.3);
        }
        
        .section-title {
            color: #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 25px;
            border-bottom: 3px solid #3498db;
        }
        
        .social-input .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }
        
        .social-input .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        
        .color-picker {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            border: 3px solid #ddd;
            cursor: pointer;
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
        }
        
        .info-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .debug-panel {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .debug-panel h5 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <!-- Header -->
        <div class="settings-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-cog"></i> Pengaturan Website</h1>
                    <p class="mb-0"><?php echo SITE_NAME; ?> - Kelola pengaturan sistem dengan mudah</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Body -->
        <div class="settings-body">
            <!-- Alert Messages -->
            <?php displayAlert(); ?>
            
            <!-- Debug Panel -->
            <div class="debug-panel">
                <h5><i class="fas fa-bug"></i> Debug Info</h5>
                <p><strong>Status Data:</strong> <?php echo $data ? 'Data ditemukan (ID: ' . $data['id'] . ')' : 'Data tidak ditemukan'; ?></p>
                <p><strong>Nama Desa Saat Ini:</strong> <?php echo htmlspecialchars($data['nama_desa'] ?? 'Belum diatur'); ?></p>
                <p><strong>Terakhir Update:</strong> <?php echo date('d-m-Y H:i:s'); ?></p>
                <button type="button" class="btn btn-sm btn-info" onclick="location.reload()">
                    <i class="fas fa-sync"></i> Refresh Halaman
                </button>
            </div>
            
            <!-- Settings Form -->
            <form method="POST" enctype="multipart/form-data" id="settingsForm">
                
                <!-- SECTION 1: INFORMASI DASAR -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle"></i> Informasi Dasar
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Nama Desa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_desa" 
                                       value="<?php echo htmlspecialchars($data['nama_desa'] ?? 'Desa Lengkong'); ?>" 
                                       required
                                       placeholder="Masukkan nama desa">
                                <small class="text-muted">Contoh: Desa Lengkong</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Motto Desa</label>
                                <input type="text" class="form-control" name="motto_desa" 
                                       value="<?php echo htmlspecialchars($data['motto_desa'] ?? ''); ?>"
                                       placeholder="Contoh: Maju, Mandiri, Sejahtera">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alamat Kantor Desa</label>
                        <textarea class="form-control" name="alamat_kantor" rows="3"
                                  placeholder="Jl. Contoh No. 123, Kelurahan Lengkong"><?php echo htmlspecialchars($data['alamat_kantor'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- SECTION 2: KONTAK -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-address-book"></i> Informasi Kontak
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control" name="telepon" 
                                       value="<?php echo htmlspecialchars($data['telepon'] ?? ''); ?>"
                                       placeholder="Contoh: 021-1234567">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                       placeholder="Contoh: desa@email.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Website</label>
                                <div class="input-group social-input">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="text" class="form-control" name="website" 
                                           value="<?php echo htmlspecialchars($data['website'] ?? ''); ?>"
                                           placeholder="desalengkong.id atau https://desalengkong.id">
                                </div>
                                <div class="info-text">Bisa tanpa https://</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="whatsapp" 
                                           value="<?php echo htmlspecialchars($data['whatsapp'] ?? ''); ?>"
                                           placeholder="Contoh: 081234567890">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 3: MEDIA SOSIAL -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-share-alt"></i> Media Sosial
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Facebook</label>
                                <div class="input-group social-input">
                                    <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                    <input type="text" class="form-control" name="facebook" 
                                           value="<?php echo htmlspecialchars($data['facebook'] ?? ''); ?>"
                                           placeholder="facebook.com/namadesa">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Instagram</label>
                                <div class="input-group social-input">
                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                    <input type="text" class="form-control" name="instagram" 
                                           value="<?php echo htmlspecialchars($data['instagram'] ?? ''); ?>"
                                           placeholder="instagram.com/namadesa">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">YouTube</label>
                                <div class="input-group social-input">
                                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                    <input type="text" class="form-control" name="youtube" 
                                           value="<?php echo htmlspecialchars($data['youtube'] ?? ''); ?>"
                                           placeholder="youtube.com/c/namadesa">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Twitter</label>
                                <div class="input-group social-input">
                                    <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                    <input type="text" class="form-control" name="twitter" 
                                           value="<?php echo htmlspecialchars($data['twitter'] ?? ''); ?>"
                                           placeholder="twitter.com/namadesa">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 4: LOKASI -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Lokasi
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Latitude</label>
                                <input type="text" class="form-control" name="latitude" 
                                       value="<?php echo htmlspecialchars($data['latitude'] ?? ''); ?>"
                                       placeholder="Contoh: -6.9185">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Longitude</label>
                                <input type="text" class="form-control" name="longitude" 
                                       value="<?php echo htmlspecialchars($data['longitude'] ?? ''); ?>"
                                       placeholder="Contoh: 107.6186">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 5: BRANDING -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-palette"></i> Branding
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Logo Utama</label>
                                <div class="file-upload">
                                    <button type="button" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-upload"></i> Pilih Logo
                                    </button>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                </div>
                                <?php if (!empty($data['logo'])): ?>
                                <div class="mt-3">
                                    <p class="mb-1">Logo saat ini:</p>
                                    <img src="../uploads/logo/<?php echo htmlspecialchars($data['logo']); ?>" 
                                         alt="Logo" class="preview-image">
                                </div>
                                <?php else: ?>
                                <div class="info-text">Belum ada logo. Ukuran rekomendasi: 200x200px</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Favicon</label>
                                <div class="file-upload">
                                    <button type="button" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-upload"></i> Pilih Favicon
                                    </button>
                                    <input type="file" class="form-control" name="favicon" accept="image/*">
                                </div>
                                <?php if (!empty($data['favicon'])): ?>
                                <div class="mt-3">
                                    <p class="mb-1">Favicon saat ini:</p>
                                    <img src="../uploads/logo/<?php echo htmlspecialchars($data['favicon']); ?>" 
                                         alt="Favicon" class="preview-image">
                                </div>
                                <?php else: ?>
                                <div class="info-text">Belum ada favicon. Ukuran rekomendasi: 32x32px</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Warna Tema</label>
                        <div class="d-flex align-items-center">
                            <input type="color" class="color-picker" name="theme_color" 
                                   value="<?php echo htmlspecialchars($data['theme_color'] ?? '#2c3e50'); ?>">
                            <input type="text" class="form-control ms-3" style="max-width: 150px;"
                                   value="<?php echo htmlspecialchars($data['theme_color'] ?? '#2c3e50'); ?>"
                                   readonly>
                            <div class="ms-3">
                                <small class="text-muted">Klik kotak warna untuk memilih</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 6: KONTEN -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-file-alt"></i> Konten
                    </h3>
                    
                    <div class="form-group">
                        <label class="form-label">Tentang Desa</label>
                        <textarea class="form-control" name="tentang_desa" rows="4"
                                  placeholder="Deskripsi singkat tentang desa..."><?php echo htmlspecialchars($data['tentang_desa'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sejarah Desa</label>
                        <textarea class="form-control" name="sejarah_desa" rows="4"
                                  placeholder="Sejarah berdirinya desa..."><?php echo htmlspecialchars($data['sejarah_desa'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Visi Desa</label>
                        <textarea class="form-control" name="visi" rows="3"
                                  placeholder="Visi pembangunan desa..."><?php echo htmlspecialchars($data['visi'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Misi Desa</label>
                        <textarea class="form-control" name="misi" rows="4"
                                  placeholder="Misi-misi pembangunan desa..."><?php echo htmlspecialchars($data['misi'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- SAVE BUTTON -->
                <div class="text-center mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> SIMPAN SEMUA PENGATURAN
                    </button>
                    <button type="reset" class="btn btn-secondary ms-3" style="padding: 15px 40px;">
                        <i class="fas fa-undo"></i> RESET FORM
                    </button>
                    <button type="button" class="btn btn-warning ms-3" style="padding: 15px 40px;" onclick="testSave()">
                        <i class="fas fa-test"></i> TEST SAVE
                    </button>
                </div>
            </form>
            
            <!-- Current Data -->
            <div class="mt-5 p-4 border rounded bg-light">
                <h5><i class="fas fa-database"></i> Data Saat Ini di Database:</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Nama Desa:</strong> <?php echo htmlspecialchars($data['nama_desa'] ?? 'NULL'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Telepon:</strong> <?php echo htmlspecialchars($data['telepon'] ?? 'NULL'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email'] ?? 'NULL'); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Website:</strong> <?php echo htmlspecialchars($data['website'] ?? 'NULL'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Theme Color:</strong> <span style="color: <?php echo $data['theme_color'] ?? '#2c3e50'; ?>"><?php echo htmlspecialchars($data['theme_color'] ?? 'NULL'); ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Logo:</strong> <?php echo !empty($data['logo']) ? 'Ada' : 'Tidak ada'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Format URL secara otomatis
        document.querySelectorAll('.social-input input').forEach(input => {
            input.addEventListener('blur', function() {
                let value = this.value.trim();
                if (value && !value.startsWith('http')) {
                    // Hapus @ jika ada
                    if (value.startsWith('@')) {
                        value = value.substring(1);
                    }
                    
                    // Tambahkan https:// jika perlu
                    if (!value.includes('.')) {
                        // Jika hanya username, ubah ke format yang umum
                        if (this.name === 'instagram' || this.name === 'twitter') {
                            value = this.name + '.com/' + value;
                        }
                    }
                    
                    if (!value.startsWith('http')) {
                        value = 'https://' + value;
                    }
                    
                    this.value = value;
                }
            });
        });
        
        // Preview file upload
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('img');
                        preview.src = e.target.result;
                        preview.className = 'preview-image mt-2';
                        preview.style.maxWidth = '150px';
                        preview.style.maxHeight = '150px';
                        
                        const parent = input.parentElement.parentElement;
                        const existingPreview = parent.querySelector('.preview-image');
                        if (existingPreview) {
                            existingPreview.src = e.target.result;
                        } else {
                            parent.appendChild(preview);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // Color picker sync
        const colorInput = document.querySelector('input[name="theme_color"]');
        const colorText = colorInput.nextElementSibling;
        
        colorInput.addEventListener('input', function() {
            colorText.value = this.value;
        });
        
        // Test save dengan data dummy
        function testSave() {
            const form = document.getElementById('settingsForm');
            const timestamp = new Date().getTime();
            
            // Isi data test
            const testData = {
                'nama_desa': 'Desa Test ' + timestamp,
                'telepon': '0812' + timestamp.toString().substr(-8),
                'email': 'test' + timestamp + '@desa.com',
                'website': 'testdesa' + timestamp + '.id',
                'facebook': 'facebook.com/testdesa' + timestamp,
                'instagram': 'instagram.com/testdesa' + timestamp
            };
            
            // Isi form dengan data test
            Object.keys(testData).forEach(fieldName => {
                const field = form.querySelector('[name="' + fieldName + '"]');
                if (field) {
                    field.value = testData[fieldName];
                }
            });
            
            if (confirm('Test save dengan data dummy? Nama Desa akan menjadi: ' + testData['nama_desa'])) {
                form.submit();
            }
        }
        
        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const namaDesa = document.querySelector('input[name="nama_desa"]');
            
            if (!namaDesa.value.trim()) {
                e.preventDefault();
                alert('⚠️ Nama Desa harus diisi!');
                namaDesa.focus();
                namaDesa.style.borderColor = '#e74c3c';
                return false;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
            
            // Reset button setelah 5 detik jika ada error
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
        
        // Reset form dengan konfirmasi
        document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
            if (!confirm('Yakin ingin mengosongkan semua form?')) {
                e.preventDefault();
            }
        });
        
        // Auto-format website field
        const websiteInput = document.querySelector('input[name="website"]');
        websiteInput.addEventListener('blur', function() {
            let value = this.value.trim();
            if (value) {
                // Hapus spasi dan karakter tidak perlu
                value = value.replace(/\s+/g, '');
                
                // Tambahkan https:// jika tidak ada protokol
                if (!value.startsWith('http://') && !value.startsWith('https://')) {
                    value = 'https://' + value;
                }
                
                this.value = value;
            }
        });
        
        // Debug: Log form data
        document.getElementById('settingsForm').addEventListener('submit', function() {
            console.log('Form submitted with data:', new FormData(this));
        });
    </script>
</body>
</html>