<?php
// modules/ajukan-layanan.php - FIXED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Fungsi helper
function clean_input($data) {
    global $conn;
    if (!isset($data) || $data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Ambil pengaturan
$settings = [];
$settings_query = "SELECT * FROM pengaturan LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
} else {
    $settings = [
        'nama_desa' => 'Desa Kita', 
        'alamat' => 'Jl. Desa No. 1', 
        'telepon' => '08123456789',
        'email' => 'desa@example.com'
    ];
}

// Get layanan ID if specified
$layanan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$layanan = null;

if ($layanan_id > 0) {
    $query = "SELECT * FROM layanan WHERE id = $layanan_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $layanan = mysqli_fetch_assoc($result);
    }
}

// Kategori layanan
$kategori_layanan = [
    'administrasi' => 'Administrasi',
    'sosial' => 'Sosial',
    'kesehatan' => 'Kesehatan',
    'pendidikan' => 'Pendidikan',
    'ekonomi' => 'Ekonomi',
    'hukum' => 'Hukum',
    'umum' => 'Umum'
];

// Handle form submission
$message = '';
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = clean_input($_POST['nama_lengkap'] ?? '');
    $nik = clean_input($_POST['nik'] ?? '');
    $tempat_lahir = clean_input($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = clean_input($_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = clean_input($_POST['jenis_kelamin'] ?? '');
    $agama = clean_input($_POST['agama'] ?? '');
    $pekerjaan = clean_input($_POST['pekerjaan'] ?? '');
    $alamat = clean_input($_POST['alamat'] ?? '');
    $telepon = clean_input($_POST['telepon'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $layanan_id = clean_input($_POST['layanan_id'] ?? '');
    $keperluan = clean_input($_POST['keperluan'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($nik)) $errors[] = 'NIK harus diisi';
    elseif (strlen($nik) != 16) $errors[] = 'NIK harus 16 digit';
    if (empty($tempat_lahir)) $errors[] = 'Tempat lahir harus diisi';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir harus diisi';
    if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin harus dipilih';
    if (empty($agama)) $errors[] = 'Agama harus diisi';
    if (empty($pekerjaan)) $errors[] = 'Pekerjaan harus diisi';
    if (empty($alamat)) $errors[] = 'Alamat harus diisi';
    if (empty($telepon)) $errors[] = 'Telepon harus diisi';
    if (empty($email)) $errors[] = 'Email harus diisi';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
    if (empty($layanan_id)) $errors[] = 'Jenis layanan harus dipilih';
    if (empty($keperluan)) $errors[] = 'Keperluan harus diisi';
    
    // Check if service exists
    if ($layanan_id > 0) {
        $check_query = "SELECT * FROM layanan WHERE id = $layanan_id";
        $check_result = mysqli_query($conn, $check_query);
        if (!$check_result || mysqli_num_rows($check_result) == 0) {
            $errors[] = 'Layanan tidak valid';
        }
    }
    
    if (empty($errors)) {
        // Generate kode pengajuan
        $kode_pengajuan = 'PJ-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Upload files
        $dokumen_path = '';
        if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $file_ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_ext)) {
                $filename = 'doc_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $upload_path = '../assets/uploads/pengajuan/' . $filename;
                
                // Buat folder jika belum ada
                if (!is_dir('../assets/uploads/pengajuan/')) {
                    mkdir('../assets/uploads/pengajuan/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $upload_path)) {
                    $dokumen_path = $filename;
                } else {
                    $errors[] = 'Gagal mengupload dokumen';
                }
            } else {
                $errors[] = 'Format dokumen tidak didukung. Gunakan PDF, JPG, PNG, DOC, DOCX';
            }
        }
        
        if (empty($errors)) {
            // Periksa apakah tabel pengajuan_layanan ada
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'pengajuan_layanan'");
            
            if (mysqli_num_rows($table_check) == 0) {
                // Buat tabel jika tidak ada
                $create_table = "CREATE TABLE IF NOT EXISTS pengajuan_layanan (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    kode_pengajuan VARCHAR(50) UNIQUE NOT NULL,
                    layanan_id INT,
                    nama_lengkap VARCHAR(100) NOT NULL,
                    nik VARCHAR(16) NOT NULL,
                    tempat_lahir VARCHAR(100),
                    tanggal_lahir DATE,
                    jenis_kelamin ENUM('L', 'P'),
                    agama VARCHAR(50),
                    pekerjaan VARCHAR(100),
                    alamat TEXT,
                    telepon VARCHAR(20),
                    email VARCHAR(100),
                    keperluan TEXT,
                    dokumen VARCHAR(255),
                    status ENUM('menunggu', 'diproses', 'diverifikasi', 'selesai', 'ditolak') DEFAULT 'menunggu',
                    catatan_admin TEXT,
                    alasan_penolakan TEXT,
                    tanggal_diproses DATETIME,
                    tanggal_diverifikasi DATETIME,
                    tanggal_selesai DATETIME,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                mysqli_query($conn, $create_table);
            }
            
            // Save to database
            $query = "INSERT INTO pengajuan_layanan (
                kode_pengajuan, layanan_id, nama_lengkap, nik, tempat_lahir, tanggal_lahir,
                jenis_kelamin, agama, pekerjaan, alamat, telepon, email, keperluan, dokumen,
                status, created_at
            ) VALUES (
                '$kode_pengajuan', $layanan_id, '$nama_lengkap', '$nik', '$tempat_lahir', '$tanggal_lahir',
                '$jenis_kelamin', '$agama', '$pekerjaan', '$alamat', '$telepon', '$email', '$keperluan', '$dokumen_path',
                'menunggu', NOW()
            )";
            
            if (mysqli_query($conn, $query)) {
                $success = true;
                $message = "Pengajuan berhasil! Kode pengajuan Anda: <strong>$kode_pengajuan</strong>. Simpan kode ini untuk mengecek status.";
                
                // Clear form
                $_POST = [];
                $layanan_id = 0;
                $layanan = null;
            } else {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi. ' . mysqli_error($conn);
            }
        }
    }
}

// Helper function untuk safe get
function safe_get($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Get all services for selection
$all_services = [];

// Cek apakah tabel layanan ada
$table_check_result = mysqli_query($conn, "SHOW TABLES LIKE 'layanan'");
if ($table_check_result && mysqli_num_rows($table_check_result) > 0) {
    // Cek struktur tabel
    $columns = [];
    $column_result = mysqli_query($conn, "SHOW COLUMNS FROM layanan");
    if ($column_result) {
        while ($col = mysqli_fetch_assoc($column_result)) {
            $columns[] = $col['Field'];
        }
    }
    
    // Bangun query berdasarkan kolom yang ada
    $select_fields = "id";
    if (in_array('judul', $columns)) {
        $select_fields .= ", judul AS nama";
        $order_by = "ORDER BY judul ASC";
    } elseif (in_array('nama_layanan', $columns)) {
        $select_fields .= ", nama_layanan AS nama";
        $order_by = "ORDER BY nama_layanan ASC";
    } elseif (in_array('title', $columns)) {
        $select_fields .= ", title AS nama";
        $order_by = "ORDER BY title ASC";
    } elseif (in_array('nama', $columns)) {
        $select_fields .= ", nama";
        $order_by = "ORDER BY nama ASC";
    } else {
        // Default jika tidak ada kolom nama
        $select_fields .= ", 'Layanan' AS nama";
        $order_by = "ORDER BY id ASC";
    }
    
    if (in_array('ikon', $columns)) {
        $select_fields .= ", ikon";
    } else {
        $select_fields .= ", 'hands-helping' AS ikon";
    }
    
    $services_query = "SELECT $select_fields FROM layanan $order_by";
    $services_result = mysqli_query($conn, $services_query);
    
    if ($services_result && mysqli_num_rows($services_result) > 0) {
        while ($service = mysqli_fetch_assoc($services_result)) {
            $all_services[] = $service;
        }
    }
}

// Jika tidak ada data, buat contoh
if (empty($all_services)) {
    $all_services = [
        ['id' => 1, 'nama' => 'Surat Keterangan Tidak Mampu', 'ikon' => 'file-contract'],
        ['id' => 2, 'nama' => 'Surat Keterangan Domisili', 'ikon' => 'home'],
        ['id' => 3, 'nama' => 'Surat Keterangan Usaha', 'ikon' => 'store'],
        ['id' => 4, 'nama' => 'Bantuan Sosial', 'ikon' => 'hands-helping'],
        ['id' => 5, 'nama' => 'Surat Keterangan Sehat', 'ikon' => 'stethoscope'],
        ['id' => 6, 'nama' => 'Surat Pengantar Nikah', 'ikon' => 'heart']
    ];
}

// Debug info (opsional, hapus di production)
$debug_info = '';
if (isset($_GET['debug'])) {
    $debug_info = "Tabel layanan ada: " . (mysqli_num_rows($table_check_result) > 0 ? 'Ya' : 'Tidak') . "<br>";
    $debug_info .= "Jumlah layanan ditemukan: " . count($all_services) . "<br>";
    $debug_info .= "Query: " . ($services_query ?? 'Tidak ada query');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Layanan - <?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .ajukan-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            position: relative;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        .step.active .step-number {
            background: #3498db;
            color: white;
            transform: scale(1.1);
        }
        .step-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        .step.active .step-label {
            color: #3498db;
            font-weight: 600;
        }
        .step-connector {
            position: absolute;
            top: 25px;
            left: 25%;
            width: 50%;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        .btn-navigation {
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-next {
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            color: white;
            border: none;
        }
        .btn-prev {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            color: #495057;
        }
        .btn-submit {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
        }
        .service-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
        }
        .service-card:hover {
            border-color: #3498db;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .service-card.selected {
            border-color: #3498db;
            background-color: #f0f8ff;
        }
        .service-icon {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .step-connector {
                display: none;
            }
            .form-container {
                padding: 20px;
                margin-top: -30px;
            }
            .ajukan-hero {
                padding: 60px 0;
            }
            .ajukan-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Simple Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-home me-2"></i><?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="berita.php">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="potensi.php">Potensi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="layanan.php">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kontak.php">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="ajukan-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Ajukan Layanan</h1>
            <p class="lead">Isi formulir berikut untuk mengajukan layanan publik</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Debug Info (optional) -->
            <?php if (!empty($debug_info) && isset($_GET['debug'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5>Debug Info:</h5>
                        <?php echo $debug_info; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Step Indicator -->
            <div class="row">
                <div class="col-12">
                    <div class="step-indicator">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Pilih Layanan</div>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Data Diri</div>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Upload Dokumen</div>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label">Konfirmasi</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if (!empty($errors)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan:</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success">
                        <h4><i class="fas fa-check-circle me-2"></i>Berhasil!</h4>
                        <p class="mb-3"><?php echo $message; ?></p>
                        <div class="d-flex gap-3">
                            <a href="status-pengajuan.php?kode=<?php echo htmlspecialchars($kode_pengajuan ?? ''); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-search me-2"></i>Cek Status
                            </a>
                            <a href="layanan.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>Layanan Lainnya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Form Container -->
            <?php if (!$success): ?>
            <div class="row">
                <div class="col-12">
                    <div class="form-container">
                        <form method="POST" action="" enctype="multipart/form-data" id="layananForm">
                            <!-- Step 1: Pilih Layanan -->
                            <div class="form-section active" id="step1">
                                <h3 class="mb-4"><i class="fas fa-list-alt me-2"></i>Pilih Jenis Layanan</h3>
                                
                                <?php if ($layanan): ?>
                                <!-- Selected Service -->
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-check-circle me-2"></i>Layanan Terpilih</h5>
                                    <p class="mb-0">Anda akan mengajukan: <strong><?php echo htmlspecialchars(safe_get($layanan, 'nama', 'Layanan')); ?></strong></p>
                                    <input type="hidden" name="layanan_id" id="layanan_id" value="<?php echo htmlspecialchars(safe_get($layanan, 'id', '')); ?>">
                                </div>
                                <?php else: ?>
                                <!-- List Services -->
                                <div class="row">
                                    <?php if (empty($all_services)): ?>
                                    <div class="col-12 text-center py-4">
                                        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada layanan tersedia</p>
                                        <a href="layanan.php" class="btn btn-primary">Kembali ke Layanan</a>
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($all_services as $service): ?>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="service-card" data-id="<?php echo htmlspecialchars(safe_get($service, 'id', '')); ?>" data-name="<?php echo htmlspecialchars(safe_get($service, 'nama', 'Layanan')); ?>">
                                                <div class="text-center">
                                                    <div class="service-icon">
                                                        <i class="fas fa-<?php echo htmlspecialchars(safe_get($service, 'ikon', 'hands-helping')); ?>"></i>
                                                    </div>
                                                    <h5><?php echo htmlspecialchars(safe_get($service, 'nama', 'Layanan')); ?></h5>
                                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 select-service-btn">
                                                        Pilih Layanan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="layanan_id" id="layanan_id" value="">
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="layanan.php" class="btn btn-prev btn-navigation">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                    <button type="button" class="btn btn-next btn-navigation" onclick="nextStep()" 
                                            <?php echo !$layanan && empty($all_services) ? 'disabled' : ''; ?>>
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Data Diri -->
                            <div class="form-section" id="step2">
                                <h3 class="mb-4"><i class="fas fa-user me-2"></i>Data Pribadi</h3>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama_lengkap" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">NIK *</label>
                                        <input type="text" name="nik" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['nik'] ?? ''); ?>" 
                                               maxlength="16" required>
                                        <small class="text-muted">16 digit NIK</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tempat Lahir *</label>
                                        <input type="text" name="tempat_lahir" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['tempat_lahir'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Lahir *</label>
                                        <input type="date" name="tanggal_lahir" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jenis Kelamin *</label>
                                        <select name="jenis_kelamin" class="form-select" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo ($_POST['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($_POST['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Agama *</label>
                                        <select name="agama" class="form-select" required>
                                            <option value="">Pilih Agama</option>
                                            <option value="Islam" <?php echo ($_POST['agama'] ?? '') == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                                            <option value="Kristen" <?php echo ($_POST['agama'] ?? '') == 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                                            <option value="Katolik" <?php echo ($_POST['agama'] ?? '') == 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                                            <option value="Hindu" <?php echo ($_POST['agama'] ?? '') == 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                                            <option value="Buddha" <?php echo ($_POST['agama'] ?? '') == 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                                            <option value="Konghucu" <?php echo ($_POST['agama'] ?? '') == 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pekerjaan *</label>
                                        <input type="text" name="pekerjaan" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['pekerjaan'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Telepon/HP *</label>
                                        <input type="tel" name="telepon" class="form-control" 
                                               value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>" 
                                               required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alamat Lengkap *</label>
                                    <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           required>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-prev btn-navigation" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Sebelumnya
                                    </button>
                                    <button type="button" class="btn btn-next btn-navigation" onclick="nextStep()">
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Upload Dokumen -->
                            <div class="form-section" id="step3">
                                <h3 class="mb-4"><i class="fas fa-file-upload me-2"></i>Upload Dokumen</h3>
                                
                                <?php 
                                $selected_service_name = '';
                                if ($layanan) {
                                    $selected_service_name = safe_get($layanan, 'nama', '');
                                } elseif (!empty($_POST['layanan_id'])) {
                                    // Cari nama layanan dari all_services
                                    foreach ($all_services as $service) {
                                        if (safe_get($service, 'id', 0) == $_POST['layanan_id']) {
                                            $selected_service_name = safe_get($service, 'nama', '');
                                            break;
                                        }
                                    }
                                }
                                ?>
                                
                                <?php if ($selected_service_name): ?>
                                <div class="alert alert-info mb-4">
                                    <h5><i class="fas fa-info-circle me-2"></i>Anda mengajukan: <?php echo htmlspecialchars($selected_service_name); ?></h5>
                                    <p class="mb-0">Silakan upload dokumen yang diperlukan sesuai persyaratan.</p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Dokumen Pendukung (Opsional)</label>
                                    <input type="file" name="dokumen" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX (Maks: 5MB)</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Keperluan / Tujuan Pengajuan *</label>
                                    <textarea name="keperluan" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['keperluan'] ?? ''); ?></textarea>
                                    <small class="text-muted">Jelaskan secara singkat tujuan pengajuan layanan ini</small>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="confirmData" required>
                                    <label class="form-check-label" for="confirmData">
                                        Saya menyatakan bahwa data yang saya berikan adalah benar dan dapat dipertanggungjawabkan
                                    </label>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-prev btn-navigation" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Sebelumnya
                                    </button>
                                    <button type="button" class="btn btn-next btn-navigation" onclick="nextStep()">
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 4: Konfirmasi -->
                            <div class="form-section" id="step4">
                                <h3 class="mb-4"><i class="fas fa-check-circle me-2"></i>Konfirmasi Pengajuan</h3>
                                
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Periksa Kembali Data Anda</h5>
                                    <p class="mb-0">Pastikan semua data yang Anda isi sudah benar sebelum mengirimkan pengajuan.</p>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5>Ringkasan Pengajuan</h5>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <p><strong>Jenis Layanan:</strong><br>
                                                <span id="summaryService"><?php echo htmlspecialchars($selected_service_name); ?></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Nama Lengkap:</strong><br>
                                                <span id="summaryName"><?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>NIK:</strong><br>
                                                <span id="summaryNIK"><?php echo htmlspecialchars($_POST['nik'] ?? ''); ?></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Email:</strong><br>
                                                <span id="summaryEmail"><?php echo htmlspecialchars($_POST['email'] ?? ''); ?></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Telepon:</strong><br>
                                                <span id="summaryPhone"><?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Keperluan:</strong><br>
                                                <span id="summaryKeperluan"><?php echo htmlspecialchars($_POST['keperluan'] ?? ''); ?></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-prev btn-navigation" onclick="prevStep()">
                                        <i class="fas fa-arrow-left me-2"></i>Perbaiki Data
                                    </button>
                                    <button type="submit" class="btn btn-submit">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pengajuan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Simple Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?></h5>
                    <p class="mb-0"><?php echo htmlspecialchars(safe_get($settings, 'alamat', 'Jl. Desa No. 1')); ?></p>
                    <p>Telp: <?php echo htmlspecialchars(safe_get($settings, 'telepon', '08123456789')); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Desa <?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?>. All rights reserved.</p>
                    <div class="mt-2">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        
        // Service selection
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selection from all cards
                document.querySelectorAll('.service-card').forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Add selection to clicked card
                this.classList.add('selected');
                
                const serviceId = this.getAttribute('data-id');
                const serviceName = this.getAttribute('data-name');
                
                // Set hidden input value
                document.getElementById('layanan_id').value = serviceId;
                
                // Update summary
                document.getElementById('summaryService').textContent = serviceName;
                
                // Enable next button
                document.querySelector('#step1 .btn-next').disabled = false;
            });
        });
        
        // Also handle button clicks inside cards
        document.querySelectorAll('.select-service-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const card = this.closest('.service-card');
                card.click();
            });
        });
        
        // Initialize if service is already selected
        <?php if ($layanan): ?>
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
        });
        <?php endif; ?>
        
        // Step navigation
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show current step
            document.getElementById(`step${step}`).classList.add('active');
            
            // Update step indicator
            document.querySelectorAll('.step').forEach(s => {
                s.classList.remove('active');
                if (parseInt(s.getAttribute('data-step')) <= step) {
                    s.classList.add('active');
                }
            });
            
            currentStep = step;
        }
        
        function nextStep() {
            if (currentStep < totalSteps) {
                // Validate current step
                if (validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }
        
        function validateStep(step) {
            const form = document.querySelector('#layananForm');
            let isValid = true;
            
            switch(step) {
                case 1:
                    const serviceId = document.getElementById('layanan_id').value;
                    if (!serviceId) {
                        alert('Silakan pilih jenis layanan terlebih dahulu');
                        isValid = false;
                    }
                    break;
                    
                case 2:
                    const requiredFields = [
                        'nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir',
                        'jenis_kelamin', 'agama', 'pekerjaan', 'telepon', 'alamat', 'email'
                    ];
                    
                    requiredFields.forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input && !input.value.trim()) {
                            input.classList.add('is-invalid');
                            isValid = false;
                        } else if (input) {
                            input.classList.remove('is-invalid');
                        }
                    });
                    
                    // Validate NIK length
                    const nikInput = form.querySelector('[name="nik"]');
                    if (nikInput && nikInput.value.length !== 16) {
                        nikInput.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    // Validate email
                    const emailInput = form.querySelector('[name="email"]');
                    if (emailInput && !validateEmail(emailInput.value)) {
                        emailInput.classList.add('is-invalid');
                        isValid = false;
                    }
                    break;
                    
                case 3:
                    const keperluan = form.querySelector('[name="keperluan"]');
                    const confirmCheck = form.querySelector('#confirmData');
                    
                    if (!keperluan.value.trim()) {
                        keperluan.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        keperluan.classList.remove('is-invalid');
                    }
                    
                    if (!confirmCheck.checked) {
                        confirmCheck.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        confirmCheck.classList.remove('is-invalid');
                    }
                    break;
            }
            
            return isValid;
        }
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        function updateSummary() {
            const form = document.querySelector('#layananForm');
            document.getElementById('summaryName').textContent = form.querySelector('[name="nama_lengkap"]')?.value || '';
            document.getElementById('summaryNIK').textContent = form.querySelector('[name="nik"]')?.value || '';
            document.getElementById('summaryEmail').textContent = form.querySelector('[name="email"]')?.value || '';
            document.getElementById('summaryPhone').textContent = form.querySelector('[name="telepon"]')?.value || '';
            document.getElementById('summaryKeperluan').textContent = form.querySelector('[name="keperluan"]')?.value || '';
        }
        
        // Auto-update summary when data changes
        document.querySelectorAll('#step2 input, #step2 textarea, #step2 select, #step3 textarea').forEach(input => {
            input.addEventListener('change', updateSummary);
            input.addEventListener('keyup', updateSummary);
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date for birth date (18 years ago)
            const today = new Date();
            const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
            const maxDate = new Date(today.getFullYear() - 17, today.getMonth(), today.getDate());
            
            const dateInput = document.querySelector('input[name="tanggal_lahir"]');
            if (dateInput) {
                dateInput.min = minDate.toISOString().split('T')[0];
                dateInput.max = maxDate.toISOString().split('T')[0];
            }
            
            // Auto-focus first field in current step
            const currentStepElement = document.querySelector('.form-section.active');
            if (currentStepElement) {
                const firstInput = currentStepElement.querySelector('input, select, textarea');
                if (firstInput && !firstInput.value) {
                    firstInput.focus();
                }
            }
        });
    </script>
</body>
</html>