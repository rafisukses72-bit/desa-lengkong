<?php
// modules/layanan.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Fungsi helper untuk membersihkan input
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

// Get kategori filter
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

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

// Build query
$conditions = "";
if ($kategori && isset($kategori_layanan[$kategori])) {
    $conditions = "WHERE kategori = '$kategori'";
}

// Get layanan from database
$layanan_data = [];
try {
    $query = "SELECT * FROM layanan";
    if ($conditions) {
        $query .= " $conditions";
    }
    $query .= " ORDER BY urutan ASC";
    
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $layanan_data[] = $row;
        }
    }
} catch (Exception $e) {
    // Jika error, gunakan data contoh
    $layanan_data = get_contoh_layanan();
}

// Jika tidak ada data, gunakan contoh
if (empty($layanan_data)) {
    $layanan_data = get_contoh_layanan();
}

// Fungsi untuk data contoh
function get_contoh_layanan() {
    return [
        [
            'id' => 1,
            'nama' => 'Surat Keterangan Tidak Mampu',
            'deskripsi_singkat' => 'Pengajuan surat keterangan tidak mampu untuk berbagai keperluan',
            'ikon' => 'file-contract',
            'kategori' => 'administrasi',
            'persyaratan' => "1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT/RW\n4. Pas foto 3x4",
            'waktu_proses' => '1-2 hari kerja',
            'biaya' => 0,
            'urutan' => 1
        ],
        [
            'id' => 2,
            'nama' => 'Surat Keterangan Domisili',
            'deskripsi_singkat' => 'Surat keterangan domisili untuk penduduk',
            'ikon' => 'home',
            'kategori' => 'administrasi',
            'persyaratan' => "1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT/RW",
            'waktu_proses' => '1 hari kerja',
            'biaya' => 0,
            'urutan' => 2
        ],
        [
            'id' => 3,
            'nama' => 'Surat Keterangan Usaha',
            'deskripsi_singkat' => 'Surat keterangan usaha untuk pengusaha kecil',
            'ikon' => 'store',
            'kategori' => 'ekonomi',
            'persyaratan' => "1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT/RW\n4. Foto usaha",
            'waktu_proses' => '2-3 hari kerja',
            'biaya' => 50000,
            'urutan' => 3
        ],
        [
            'id' => 4,
            'nama' => 'Bantuan Sosial',
            'deskripsi_singkat' => 'Pengajuan bantuan sosial dari pemerintah',
            'ikon' => 'hands-helping',
            'kategori' => 'sosial',
            'persyaratan' => "1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT/RW\n4. Data pendapatan",
            'waktu_proses' => '3-5 hari kerja',
            'biaya' => 0,
            'urutan' => 4
        ],
        [
            'id' => 5,
            'nama' => 'Surat Keterangan Sehat',
            'deskripsi_singkat' => 'Surat keterangan sehat untuk kerja/beasiswa',
            'ikon' => 'stethoscope',
            'kategori' => 'kesehatan',
            'persyaratan' => "1. Fotokopi KTP\n2. Pas foto 3x4\n3. Periksa kesehatan di puskesmas",
            'waktu_proses' => '1 hari kerja',
            'biaya' => 25000,
            'urutan' => 5
        ],
        [
            'id' => 6,
            'nama' => 'Surat Pengantar Nikah',
            'deskripsi_singkat' => 'Pengajuan surat pengantar untuk pernikahan',
            'ikon' => 'heart',
            'kategori' => 'administrasi',
            'persyaratan' => "1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat pengantar RT/RW\n4. Surat keterangan belum menikah",
            'waktu_proses' => '2-3 hari kerja',
            'biaya' => 0,
            'urutan' => 6
        ]
    ];
}

// Fungsi helper untuk mendapatkan nilai yang aman dari array
function safe_get($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
}

// Fungsi untuk format biaya
function format_biaya($biaya) {
    if (empty($biaya) || $biaya == 0) {
        return 'Gratis';
    }
    // Pastikan $biaya adalah angka
    $biaya = floatval($biaya);
    return 'Rp ' . number_format($biaya, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Publik - <?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
        }
        .layanan-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .layanan-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }
        .layanan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .layanan-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -40px auto 20px;
            color: white;
            font-size: 2rem;
            position: relative;
            z-index: 2;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .layanan-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            height: 100px;
            position: relative;
        }
        .layanan-badge {
            background: var(--secondary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        .category-filter .btn {
            border-radius: 25px;
            padding: 8px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .layanan-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .layanan-features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .layanan-features li:last-child {
            border-bottom: none;
        }
        .layanan-features i {
            color: var(--secondary-color);
            margin-right: 10px;
        }
        .btn-ajukan {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-ajukan:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
        }
        .procedure-step {
            text-align: center;
            padding: 20px;
            position: relative;
        }
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        .procedure-connector {
            position: absolute;
            top: 25px;
            right: -25%;
            width: 50%;
            height: 2px;
            background: var(--primary-color);
            z-index: 1;
        }
        @media (max-width: 768px) {
            .layanan-hero {
                padding: 60px 0;
            }
            .layanan-hero h1 {
                font-size: 2rem;
            }
            .procedure-connector {
                display: none;
            }
            .category-filter .btn {
                margin: 3px;
                padding: 6px 15px;
                font-size: 0.9rem;
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
    <section class="layanan-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Layanan Publik</h1>
            <p class="lead">Pelayanan terbaik untuk masyarakat Desa <?php echo htmlspecialchars(safe_get($settings, 'nama_desa', 'Desa')); ?></p>
            <a href="ajukan-layanan.php" class="btn btn-light btn-lg mt-3">
                <i class="fas fa-plus-circle me-2"></i>Ajukan Layanan
            </a>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Info Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <h4><i class="fas fa-info-circle me-2"></i>Informasi Pelayanan</h4>
                                <p class="mb-0">Berikut adalah layanan yang tersedia. Anda dapat mengajukan layanan melalui sistem online atau datang langsung ke kantor desa.</p>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <a href="status-pengajuan.php" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Cek Status Pengajuan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kategori Filter -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="text-center">
                        <h3 class="mb-4">Pilih Jenis Layanan</h3>
                        <div class="d-flex flex-wrap justify-content-center">
                            <a href="?" class="btn <?php echo !$kategori ? 'btn-primary' : 'btn-outline-primary'; ?> category-btn">
                                Semua Layanan
                            </a>
                            <?php foreach ($kategori_layanan as $key => $name): ?>
                            <a href="?kategori=<?php echo $key; ?>" 
                               class="btn <?php echo $kategori === $key ? 'btn-primary' : 'btn-outline-primary'; ?> category-btn">
                                <?php echo htmlspecialchars($name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Procedure -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-4"><i class="fas fa-list-ol me-2"></i>Cara Mengajukan Layanan</h2>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4 position-relative">
                            <div class="procedure-step">
                                <div class="step-number">1</div>
                                <h5>Pilih Layanan</h5>
                                <p class="text-muted">Pilih jenis layanan yang Anda butuhkan</p>
                            </div>
                            <div class="procedure-connector"></div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4 position-relative">
                            <div class="procedure-step">
                                <div class="step-number">2</div>
                                <h5>Isi Formulir</h5>
                                <p class="text-muted">Isi data diri dan kelengkapan dokumen</p>
                            </div>
                            <div class="procedure-connector"></div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4 position-relative">
                            <div class="procedure-step">
                                <div class="step-number">3</div>
                                <h5>Verifikasi</h5>
                                <p class="text-muted">Tunggu verifikasi dari admin desa</p>
                            </div>
                            <div class="procedure-connector"></div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="procedure-step">
                                <div class="step-number">4</div>
                                <h5>Selesai</h5>
                                <p class="text-muted">Ambil dokumen di kantor desa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Layanan List -->
            <div class="row">
                <?php if (empty($layanan_data)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-concierge-bell fa-4x text-muted mb-3"></i>
                    <h3>Tidak ada layanan ditemukan</h3>
                    <p class="text-muted">Silakan pilih kategori lain atau hubungi kantor desa</p>
                </div>
                <?php else: ?>
                    <?php foreach ($layanan_data as $index => $layanan): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="layanan-card">
                            <div class="layanan-header"></div>
                            <div class="card-body p-4">
                                <div class="layanan-icon">
                                    <i class="fas fa-<?php echo htmlspecialchars(safe_get($layanan, 'ikon', 'hands-helping')); ?>"></i>
                                </div>
                                
                                <?php 
                                $kategori_layanan_key = safe_get($layanan, 'kategori', 'umum');
                                $kategori_nama = isset($kategori_layanan[$kategori_layanan_key]) ? $kategori_layanan[$kategori_layanan_key] : 'Umum';
                                ?>
                                <span class="layanan-badge"><?php echo htmlspecialchars($kategori_nama); ?></span>
                                
                                <h4 class="card-title mb-3"><?php echo htmlspecialchars(safe_get($layanan, 'nama', 'Layanan Desa')); ?></h4>
                                
                                <?php if (!empty($layanan['deskripsi_singkat'])): ?>
                                <p class="card-text text-muted mb-4"><?php echo htmlspecialchars(safe_get($layanan, 'deskripsi_singkat', '')); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($layanan['persyaratan'])): ?>
                                <div class="mb-4">
                                    <h6><i class="fas fa-list-ul me-2"></i>Persyaratan:</h6>
                                    <ul class="layanan-features">
                                        <?php 
                                        $syarat_text = safe_get($layanan, 'persyaratan', '');
                                        $syarat_array = explode("\n", $syarat_text);
                                        foreach ($syarat_array as $item):
                                            $item = trim($item);
                                            if (!empty($item)):
                                        ?>
                                        <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($item); ?></li>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($layanan['waktu_proses'])): ?>
                                <div class="mb-4">
                                    <h6><i class="far fa-clock me-2"></i>Waktu Proses:</h6>
                                    <p class="mb-0 text-primary"><?php echo htmlspecialchars(safe_get($layanan, 'waktu_proses', '1-3 hari kerja')); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            <?php echo format_biaya(safe_get($layanan, 'biaya', 0)); ?>
                                        </span>
                                    </div>
                                    <a href="ajukan-layanan.php?id=<?php echo intval(safe_get($layanan, 'id', $index + 1)); ?>" class="btn-ajukan">
                                        <i class="fas fa-paper-plane me-2"></i>Ajukan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Additional Info -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4><i class="fas fa-question-circle me-2"></i>Butuh Bantuan?</h4>
                                    <p class="mb-0">Jika Anda mengalami kesulitan dalam mengajukan layanan atau memiliki pertanyaan, silakan hubungi kami.</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="kontak.php" class="btn btn-primary">
                                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        // Category filter animation
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Layanan card animation
        document.querySelectorAll('.layanan-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
        });
        
        // Smooth scroll untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId !== '#') {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>