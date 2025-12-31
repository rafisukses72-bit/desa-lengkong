<?php
// modules/status-pengajuan.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Ambil pengaturan
$settings = [];
$settings_query = "SELECT * FROM pengaturan LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
}

// Handle search
$kode_pengajuan = '';
$pengajuan = null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['kode'])) {
    $kode_pengajuan = clean_input($_POST['kode_pengajuan'] ?? $_GET['kode'] ?? '');
    
    if (!empty($kode_pengajuan)) {
        $query = "SELECT p.*, l.nama as layanan_nama, l.ikon as layanan_ikon 
                  FROM pengajuan_layanan p 
                  LEFT JOIN layanan l ON p.layanan_id = l.id 
                  WHERE p.kode_pengajuan = '$kode_pengajuan'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $pengajuan = mysqli_fetch_assoc($result);
        } else {
            $error = 'Kode pengajuan tidak ditemukan';
        }
    } else {
        $error = 'Silakan masukkan kode pengajuan';
    }
}

function clean_input($data) {
    global $conn;
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Status colors
$status_colors = [
    'menunggu' => 'warning',
    'diproses' => 'info',
    'diverifikasi' => 'primary',
    'selesai' => 'success',
    'ditolak' => 'danger'
];

// Status icons
$status_icons = [
    'menunggu' => 'clock',
    'diproses' => 'cog',
    'diverifikasi' => 'check-circle',
    'selesai' => 'check-double',
    'ditolak' => 'times-circle'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pengajuan - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-top: -50px;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .status-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .status-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
        }
        .status-badge {
            font-size: 1rem;
            padding: 8px 20px;
            border-radius: 20px;
        }
        .status-timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline-item {
            display: flex;
            margin-bottom: 30px;
            position: relative;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
            z-index: 2;
            border: 3px solid white;
        }
        .timeline-marker.active {
            background: #28a745;
            color: white;
        }
        .timeline-content {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
        .timeline-content.active {
            background: #e8f5e9;
            border-left: 4px solid #28a745;
        }
        .timeline-connector {
            position: absolute;
            top: 40px;
            left: 19px;
            height: calc(100% - 40px);
            width: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        .timeline-item:last-child .timeline-connector {
            display: none;
        }
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-search:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .status-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="status-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Cek Status Pengajuan</h1>
            <p class="lead">Pantau perkembangan pengajuan layanan Anda</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Search Box -->
            <div class="row mb-5">
                <div class="col-lg-8 offset-lg-2">
                    <div class="search-box">
                        <h3 class="text-center mb-4">Cari Pengajuan</h3>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="text-center">
                            <div class="input-group mb-4">
                                <input type="text" name="kode_pengajuan" class="form-control form-control-lg" 
                                       placeholder="Masukkan kode pengajuan (contoh: PJ-20231201-ABC123)" 
                                       value="<?php echo htmlspecialchars($kode_pengajuan); ?>" required>
                                <button class="btn btn-search" type="submit">
                                    <i class="fas fa-search me-2"></i>Cari
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Kode pengajuan dapat Anda temukan di email konfirmasi atau catatan pengajuan
                            </small>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Results -->
            <?php if ($pengajuan): ?>
            <div class="row">
                <div class="col-12">
                    <div class="status-card mb-5">
                        <div class="status-header text-center">
                            <div class="status-icon">
                                <i class="fas fa-<?php echo htmlspecialchars($pengajuan['layanan_ikon'] ?? 'hands-helping'); ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($pengajuan['layanan_nama']); ?></h3>
                            <p class="mb-0">Kode: <?php echo htmlspecialchars($pengajuan['kode_pengajuan']); ?></p>
                        </div>
                        
                        <div class="card-body">
                            <!-- Status Badge -->
                            <div class="text-center mb-5">
                                <span class="badge status-badge bg-<?php echo $status_colors[$pengajuan['status']] ?? 'secondary'; ?>">
                                    <i class="fas fa-<?php echo $status_icons[$pengajuan['status']] ?? 'circle'; ?> me-2"></i>
                                    <?php echo ucfirst($pengajuan['status']); ?>
                                </span>
                            </div>
                            
                            <div class="row">
                                <!-- Left: Applicant Info -->
                                <div class="col-lg-6 mb-4">
                                    <h4 class="mb-4"><i class="fas fa-user-circle me-2"></i>Informasi Pemohon</h4>
                                    <div class="info-item">
                                        <strong>Nama Lengkap:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan['nama_lengkap']); ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>NIK:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan['nik']); ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Alamat:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan['alamat']); ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Telepon:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan['telepon']); ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Email:</strong><br>
                                        <?php echo htmlspecialchars($pengajuan['email']); ?>
                                    </div>
                                </div>
                                
                                <!-- Right: Submission Info -->
                                <div class="col-lg-6 mb-4">
                                    <h4 class="mb-4"><i class="fas fa-file-alt me-2"></i>Detail Pengajuan</h4>
                                    <div class="info-item">
                                        <strong>Tanggal Pengajuan:</strong><br>
                                        <?php echo date('d F Y H:i', strtotime($pengajuan['created_at'])); ?>
                                    </div>
                                    <div class="info-item">
                                        <strong>Keperluan:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($pengajuan['keperluan'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($pengajuan['dokumen'])): ?>
                                    <div class="info-item">
                                        <strong>Dokumen:</strong><br>
                                        <a href="../assets/uploads/pengajuan/<?php echo htmlspecialchars($pengajuan['dokumen']); ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($pengajuan['catatan_admin'])): ?>
                                    <div class="info-item">
                                        <strong>Catatan Admin:</strong><br>
                                        <div class="alert alert-info mt-2">
                                            <?php echo nl2br(htmlspecialchars($pengajuan['catatan_admin'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($pengajuan['status'] == 'selesai' && !empty($pengajuan['tanggal_selesai'])): ?>
                                    <div class="info-item">
                                        <strong>Tanggal Selesai:</strong><br>
                                        <?php echo date('d F Y', strtotime($pengajuan['tanggal_selesai'])); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Timeline -->
                            <div class="row mt-5">
                                <div class="col-12">
                                    <h4 class="mb-4"><i class="fas fa-history me-2"></i>Status Proses</h4>
                                    <div class="status-timeline">
                                        
                                        <!-- Step 1: Menunggu -->
                                        <div class="timeline-item">
                                            <div class="timeline-marker <?php echo in_array($pengajuan['status'], ['menunggu', 'diproses', 'diverifikasi', 'selesai']) ? 'active' : ''; ?>">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="timeline-content <?php echo $pengajuan['status'] == 'menunggu' ? 'active' : ''; ?>">
                                                <h6>Menunggu Verifikasi</h6>
                                                <small class="text-muted">
                                                    <?php if ($pengajuan['status'] == 'menunggu'): ?>
                                                        Sedang menunggu verifikasi admin
                                                    <?php else: ?>
                                                        Diverifikasi pada: <?php echo date('d M Y', strtotime($pengajuan['tanggal_diproses'] ?? $pengajuan['created_at'])); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="timeline-connector"></div>
                                        </div>
                                        
                                        <!-- Step 2: Diproses -->
                                        <div class="timeline-item">
                                            <div class="timeline-marker <?php echo in_array($pengajuan['status'], ['diproses', 'diverifikasi', 'selesai']) ? 'active' : ''; ?>">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="timeline-content <?php echo $pengajuan['status'] == 'diproses' ? 'active' : ''; ?>">
                                                <h6>Sedang Diproses</h6>
                                                <small class="text-muted">
                                                    <?php if (in_array($pengajuan['status'], ['menunggu'])): ?>
                                                        Belum diproses
                                                    <?php elseif (!empty($pengajuan['tanggal_diproses'])): ?>
                                                        Diproses pada: <?php echo date('d M Y', strtotime($pengajuan['tanggal_diproses'])); ?>
                                                    <?php else: ?>
                                                        Sedang dalam proses
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="timeline-connector"></div>
                                        </div>
                                        
                                        <!-- Step 3: Diverifikasi -->
                                        <div class="timeline-item">
                                            <div class="timeline-marker <?php echo in_array($pengajuan['status'], ['diverifikasi', 'selesai']) ? 'active' : ''; ?>">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="timeline-content <?php echo $pengajuan['status'] == 'diverifikasi' ? 'active' : ''; ?>">
                                                <h6>Terverifikasi</h6>
                                                <small class="text-muted">
                                                    <?php if (in_array($pengajuan['status'], ['menunggu', 'diproses'])): ?>
                                                        Belum diverifikasi
                                                    <?php elseif (!empty($pengajuan['tanggal_diverifikasi'])): ?>
                                                        Diverifikasi pada: <?php echo date('d M Y', strtotime($pengajuan['tanggal_diverifikasi'])); ?>
                                                    <?php else: ?>
                                                        Telah diverifikasi
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="timeline-connector"></div>
                                        </div>
                                        
                                        <!-- Step 4: Selesai/Ditolak -->
                                        <div class="timeline-item">
                                            <div class="timeline-marker <?php echo in_array($pengajuan['status'], ['selesai', 'ditolak']) ? 'active' : ''; ?>">
                                                <i class="fas fa-<?php echo $pengajuan['status'] == 'selesai' ? 'check-double' : 'times-circle'; ?>"></i>
                                            </div>
                                            <div class="timeline-content <?php echo in_array($pengajuan['status'], ['selesai', 'ditolak']) ? 'active' : ''; ?>">
                                                <h6><?php echo $pengajuan['status'] == 'selesai' ? 'Selesai' : 'Ditolak'; ?></h6>
                                                <small class="text-muted">
                                                    <?php if ($pengajuan['status'] == 'selesai'): ?>
                                                        <?php if (!empty($pengajuan['tanggal_selesai'])): ?>
                                                            Selesai pada: <?php echo date('d M Y', strtotime($pengajuan['tanggal_selesai'])); ?>
                                                        <?php else: ?>
                                                            Pengajuan telah selesai
                                                        <?php endif; ?>
                                                    <?php elseif ($pengajuan['status'] == 'ditolak'): ?>
                                                        Pengajuan ditolak
                                                        <?php if (!empty($pengajuan['alasan_penolakan'])): ?>
                                                            <br>Alasan: <?php echo htmlspecialchars($pengajuan['alasan_penolakan']); ?>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        Belum selesai
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="row mt-5 no-print">
                                <div class="col-12 text-center">
                                    <button onclick="window.print()" class="btn btn-outline-primary me-3">
                                        <i class="fas fa-print me-2"></i>Cetak
                                    </button>
                                    <a href="ajukan-layanan.php" class="btn btn-primary me-3">
                                        <i class="fas fa-plus-circle me-2"></i>Ajukan Layanan Baru
                                    </a>
                                    <a href="layanan.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-list me-2"></i>Lihat Layanan Lain
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
            <!-- Empty state after search -->
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h3>Data tidak ditemukan</h3>
                    <p class="text-muted">Pastikan kode pengajuan yang Anda masukkan benar</p>
                    <a href="status-pengajuan.php" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Cari Kembali
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Help Section -->
            <div class="row mt-5 no-print">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h4><i class="fas fa-question-circle me-2"></i>Butuh Bantuan?</h4>
                            <p class="mb-3">Jika Anda mengalami masalah atau memiliki pertanyaan terkait pengajuan layanan, silakan hubungi kami:</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-phone text-primary fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Telepon</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($settings['telepon'] ?? '021-12345678'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-envelope text-primary fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Email</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($settings['email'] ?? 'desa@example.com'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-map-marker-alt text-primary fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Alamat</h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($settings['alamat'] ?? 'Jl. Desa No. 1'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="kode_pengajuan"]');
            if (searchInput) {
                searchInput.focus();
            }
            
            // Print functionality
            const printBtn = document.querySelector('button[onclick="window.print()"]');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    window.print();
                });
            }
        });
        
        // Share functionality
        function shareStatus() {
            if (navigator.share) {
                navigator.share({
                    title: 'Status Pengajuan - <?php echo htmlspecialchars($settings['nama_desa']); ?>',
                    text: 'Cek status pengajuan layanan saya: <?php echo $pengajuan ? htmlspecialchars($pengajuan['kode_pengajuan']) : ''; ?>',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                alert('Silakan salin URL ini untuk berbagi: ' + window.location.href);
            }
        }
    </script>
</body>
</html>