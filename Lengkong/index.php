<?php
// index.php
require_once 'config.php';
require_once 'functions.php';

// Get settings
$settings = get_settings();

// Get data for homepage
$banners = get_active_banners();
$recent_news = get_recent_news(6);
$featured_news = get_featured_news(3);
$upcoming_events = get_upcoming_events(5);
$potentials = get_active_potentials(4);
$structure = get_structure();
$services = get_services();
$gallery = get_gallery(8);
$rt_rw = get_rt_rw();

// Count statistics dengan error handling
$total_news = 0;
$total_potentials = 0;
$total_services = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM berita WHERE status = 'published'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_news = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM potensi WHERE status = 'aktif'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_potentials = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM layanan WHERE aktif = 1");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_services = $row['total'];
}


// Cek apakah tabel penduduk ada (dari database sistem_desa_sekolah)
// Untuk demo, kita gunakan data statis
$demographic = null;
$jumlah_penduduk = 3500;
$jumlah_kk = 850;

// Jika ingin mengambil dari tabel penduduk yang ada di database sistem_desa_sekolah:
// $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM sistem_desa_sekolah.penduduk");
// if ($result && mysqli_num_rows($result) > 0) {
//     $row = mysqli_fetch_assoc($result);
//     $jumlah_penduduk = $row['total'];
// }

// Jika ingin mengambil dari tabel rt_rw:
$result = mysqli_query($conn, "SELECT SUM(jumlah_kk) as total_kk FROM rt_rw");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $jumlah_kk = $row['total_kk'] ?: 850;
}

// Get data RT/RW jika ada
$rt_rw_data = [];
$result = mysqli_query($conn, "SELECT jenis, nomor, ketua, jumlah_kk FROM rt_rw ORDER BY jenis, nomor");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rt_rw_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['nama_desa']); ?> - Website Resmi</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animasi.css">
    
    <style>
        :root {
            --primary-color: <?php echo $settings['theme_color'] ?: '#2c3e50'; ?>;
            --secondary-color: #3498db;
        }
        
        .hero-banner {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('assets/images/banner-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern.png');
            opacity: 0.1;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <main>
        <!-- Hero Section with Animation -->
        <section class="hero-banner text-white">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                        <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">
                            Selamat Datang di <br>
                            <span class="text-warning"><?php echo htmlspecialchars($settings['nama_desa']); ?></span>
                        </h1>
                        <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo htmlspecialchars($settings['motto_desa']); ?>
                        </p>
                        <div class="d-flex gap-3 animate__animated animate__fadeInUp animate__delay-2s">
                            <a href="#about" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-info-circle me-2"></i> Tentang Desa
                            </a>
                            <a href="#services" class="btn btn-outline-light btn-lg px-4">
                                <i class="fas fa-handshake me-2"></i> Layanan
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                        <div class="floating-image">
                            <img src="assets/images/desa-illustration.png" alt="Desa Illustration" 
                                 class="img-fluid animate__animated animate__pulse animate__infinite">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Animated Elements -->
            <div class="floating-elements">
                <div class="floating-element el1"></div>
                <div class="floating-element el2"></div>
                <div class="floating-element el3"></div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                        <img src="assets/images/kantor-desa.jpg" alt="Kantor Desa" 
                             class="img-fluid rounded shadow-lg animate__animated animate__zoomIn">
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <h2 class="section-title mb-4">Tentang <?php echo htmlspecialchars($settings['nama_desa']); ?></h2>
                        <?php if (!empty($settings['tentang_desa'])): ?>
                            <p class="lead"><?php echo nl2br(htmlspecialchars(substr($settings['tentang_desa'], 0, 300))); ?>...</p>
                        <?php else: ?>
                            <p class="lead"><?php echo htmlspecialchars($settings['nama_desa']); ?> adalah desa yang terletak di daerah Kuningan dengan masyarakat yang aktif dan produktif.</p>
                        <?php endif; ?>
                        <a href="modules/profil.php" class="btn btn-primary mt-3">
                            Baca Selengkapnya <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section with Counter Animation -->
        <section class="statistics-section py-5" style="background: var(--primary-color); color: white;">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Statistik Desa</h2>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-box">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo $jumlah_penduduk; ?>">0</h3>
                            <p>Jumlah Penduduk</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-box">
                            <i class="fas fa-home fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo $jumlah_kk; ?>">0</h3>
                            <p>Kepala Keluarga</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-box">
                            <i class="fas fa-newspaper fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo $total_news; ?>">0</h3>
                            <p>Berita & Informasi</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-box">
                            <i class="fas fa-store fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo $total_potentials; ?>">0</h3>
                            <p>Potensi & UMKM</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Latest News -->
        <section class="news-section py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">Berita & Informasi Terbaru</h2>
                    <a href="modules/berita.php" class="btn btn-outline-primary" data-aos="fade-left">
                        Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
                
                <div class="row">
                    <?php if (!empty($recent_news)): ?>
                        <?php foreach ($recent_news as $index => $news): ?>
                            <div class="col-lg-4 col-md-6 mb-4" 
                                 data-aos="fade-up" 
                                 data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card news-card h-100 animate-on-hover">
                                    <?php if (!empty($news['gambar'])): ?>
                                        <div class="news-image-container">
                                            <img src="assets/uploads/berita/<?php echo htmlspecialchars($news['gambar']); ?>" 
                                                 class="card-img-top" 
                                                 alt="<?php echo htmlspecialchars($news['judul']); ?>">
                                            <div class="news-overlay">
                                                <a href="modules/detail-berita.php?slug=<?php echo $news['slug']; ?>" 
                                                   class="btn btn-primary">Baca Selengkapnya</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <?php if (!empty($news['kategori'])): ?>
                                            <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($news['kategori']); ?></span>
                                        <?php endif; ?>
                                        <h5 class="card-title"><?php echo htmlspecialchars($news['judul']); ?></h5>
                                        <?php if (!empty($news['konten'])): ?>
                                            <p class="card-text"><?php echo substr(strip_tags($news['konten']), 0, 100); ?>...</p>
                                        <?php else: ?>
                                            <p class="card-text text-muted">Tidak ada deskripsi...</p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="far fa-calendar me-1"></i> 
                                                <?php echo !empty($news['created_at']) ? format_date($news['created_at']) : '-'; ?>
                                            </small>
                                            <span class="text-muted">
                                                <i class="far fa-eye me-1"></i> <?php echo !empty($news['views']) ? $news['views'] : '0'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada berita yang dipublikasikan.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Featured News Carousel -->
        <?php if (!empty($featured_news)): ?>
        <section class="featured-news py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Berita Unggulan</h2>
                <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel" data-aos="zoom-in">
                    <div class="carousel-inner">
                        <?php foreach ($featured_news as $index => $news): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <?php if (!empty($news['gambar'])): ?>
                                            <img src="assets/uploads/berita/<?php echo htmlspecialchars($news['gambar']); ?>" 
                                                 class="d-block w-100 rounded" 
                                                 alt="<?php echo htmlspecialchars($news['judul']); ?>">
                                        <?php else: ?>
                                            <div class="d-block w-100 rounded bg-secondary" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image fa-5x text-light"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="carousel-caption text-dark position-static p-0">
                                            <h3><?php echo htmlspecialchars($news['judul']); ?></h3>
                                            <?php if (!empty($news['konten'])): ?>
                                                <p><?php echo substr(strip_tags($news['konten']), 0, 200); ?>...</p>
                                            <?php else: ?>
                                                <p class="text-muted">Tidak ada deskripsi...</p>
                                            <?php endif; ?>
                                            <a href="modules/detail-berita.php?slug=<?php echo $news['slug']; ?>" 
                                               class="btn btn-primary">Baca Lengkap</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Upcoming Events -->
        <section class="events-section py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Agenda Terdekat</h2>
                <div class="row">
                    <?php if (empty($upcoming_events)): ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada agenda terdekat.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_events as $index => $event): ?>
                            <div class="col-md-6 mb-4" 
                                 data-aos="fade-up" 
                                 data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card event-card h-100 animate-on-hover">
                                    <div class="card-body">
                                        <div class="event-date bg-primary text-white text-center p-3 rounded mb-3">
                                            <div class="event-day h2 mb-0"><?php echo date('d', strtotime($event['tanggal_mulai'])); ?></div>
                                            <div class="event-month"><?php echo date('M', strtotime($event['tanggal_mulai'])); ?></div>
                                            <div class="event-year"><?php echo date('Y', strtotime($event['tanggal_mulai'])); ?></div>
                                        </div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['judul']); ?></h5>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo htmlspecialchars($event['lokasi']); ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="far fa-clock me-2"></i>
                                            <?php echo date('H:i', strtotime($event['jam_mulai'])); ?>
                                            <?php if (!empty($event['jam_selesai'])): ?>
                                                - <?php echo date('H:i', strtotime($event['jam_selesai'])); ?>
                                            <?php endif; ?>
                                        </p>
                                        <a href="modules/agenda.php" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="modules/agenda.php" class="btn btn-primary">
                        Lihat Kalender Lengkap <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Potentials & UMKM -->
        <section id="potentials" class="potentials-section py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 data-aos="fade-right">Potensi & UMKM Desa</h2>
                    <a href="modules/potensi.php" class="btn btn-outline-primary" data-aos="fade-left">
                        Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
                <div class="row">
                    <?php if (!empty($potentials)): ?>
                        <?php foreach ($potentials as $index => $potential): ?>
                            <div class="col-lg-3 col-md-6 mb-4" 
                                 data-aos="flip-left" 
                                 data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="card potential-card h-100 animate-on-hover">
                                    <?php if (!empty($potential['gambar'])): ?>
                                        <img src="assets/uploads/potensi/<?php echo htmlspecialchars($potential['gambar']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($potential['nama']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top bg-secondary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-store fa-4x text-light"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <?php if (!empty($potential['jenis'])): ?>
                                            <span class="badge bg-success mb-2"><?php echo ucfirst($potential['jenis']); ?></span>
                                        <?php endif; ?>
                                        <h5 class="card-title"><?php echo htmlspecialchars($potential['nama']); ?></h5>
                                        <?php if (!empty($potential['deskripsi'])): ?>
                                            <p class="card-text"><?php echo substr(strip_tags($potential['deskripsi']), 0, 80); ?>...</p>
                                        <?php else: ?>
                                            <p class="card-text text-muted">Tidak ada deskripsi...</p>
                                        <?php endif; ?>
                                        <?php if (!empty($potential['harga'])): ?>
                                            <p class="text-primary fw-bold">Rp <?php echo number_format($potential['harga'], 0, ',', '.'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                        <a href="modules/detail-potensi.php?id=<?php echo $potential['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary w-100">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada data potensi & UMKM.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="services-section py-5" style="background: var(--primary-color); color: white;">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Layanan Publik</h2>
                <div class="row">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $index => $service): ?>
                            <div class="col-lg-3 col-md-6 mb-4" 
                                 data-aos="zoom-in" 
                                 data-aos-delay="<?php echo $index * 100; ?>">
                                <a href="modules/ajukan-layanan.php?jenis=<?php echo urlencode($service['nama_layanan']); ?>" 
                                   class="service-link">
                                    <div class="service-card text-center p-4 h-100 animate-on-hover">
                                        <div class="service-icon mb-3">
                                            <i class="fas <?php echo !empty($service['icon']) ? htmlspecialchars($service['icon']) : 'fa-handshake'; ?> fa-3x"></i>
                                        </div>
                                        <h4><?php echo htmlspecialchars($service['nama_layanan']); ?></h4>
                                        <?php if (!empty($service['deskripsi'])): ?>
                                            <p><?php echo substr(strip_tags($service['deskripsi']), 0, 60); ?>...</p>
                                        <?php else: ?>
                                            <p class="text-light">Layanan publik dari desa</p>
                                        <?php endif; ?>
                                        <div class="service-meta">
                                            <?php if (!empty($service['estimasi_waktu'])): ?>
                                                <span class="badge bg-light text-dark me-2">
                                                    <?php echo htmlspecialchars($service['estimasi_waktu']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($service['biaya'])): ?>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($service['biaya']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-light">
                                <i class="fas fa-info-circle"></i> Belum ada data layanan.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="modules/layanan.php" class="btn btn-light">
                        Lihat Semua Layanan <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Village Structure -->
        <section class="structure-section py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Struktur Desa</h2>
                <div class="row justify-content-center">
                    <?php if (!empty($structure)): ?>
                        <?php foreach ($structure as $index => $person): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4" 
                                 data-aos="fade-up" 
                                 data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="structure-card text-center animate-on-hover">
                                    <div class="structure-photo mb-3">
                                        <?php if (!empty($person['foto'])): ?>
                                            <img src="assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($person['nama']); ?>"
                                                 class="img-fluid rounded-circle">
                                        <?php else: ?>
                                            <div class="no-photo rounded-circle">
                                                <i class="fas fa-user fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h5><?php echo htmlspecialchars($person['nama']); ?></h5>
                                    <?php if (!empty($person['jabatan'])): ?>
                                        <p class="text-primary fw-bold"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($person['kontak'])): ?>
                                        <p class="text-muted">
                                            <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($person['kontak']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada data struktur desa.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="modules/struktur-desa.php" class="btn btn-primary">
                        Lihat Struktur Lengkap <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Gallery Preview -->
        <section class="gallery-section py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Galeri Foto</h2>
                <div class="row">
                    <?php if (!empty($gallery)): ?>
                        <?php foreach ($gallery as $index => $photo): ?>
                            <div class="col-lg-3 col-md-4 col-6 mb-4" 
                                 data-aos="fade-up" 
                                 data-aos-delay="<?php echo $index * 50; ?>">
                                <?php if (!empty($photo['gambar'])): ?>
                                    <a href="assets/uploads/galeri/<?php echo htmlspecialchars($photo['gambar']); ?>" 
                                       data-lightbox="gallery" 
                                       data-title="<?php echo !empty($photo['judul']) ? htmlspecialchars($photo['judul']) : 'Foto Desa'; ?>">
                                        <div class="gallery-item">
                                            <img src="assets/uploads/galeri/<?php echo htmlspecialchars($photo['gambar']); ?>" 
                                                 alt="<?php echo !empty($photo['judul']) ? htmlspecialchars($photo['judul']) : 'Foto Desa'; ?>"
                                                 class="img-fluid">
                                            <div class="gallery-overlay">
                                                <i class="fas fa-search-plus fa-2x"></i>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12" data-aos="fade-up">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada foto di galeri.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="modules/galeri.php" class="btn btn-primary">
                        Lihat Galeri Lengkap <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS (Animate On Scroll) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Lightbox -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animasi.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Counter Animation
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            const speed = 200; // Lower = faster
            
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-count');
                    const count = +counter.innerText.replace(/,/g, '');
                    const increment = target / speed;
                    
                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment).toLocaleString('id-ID');
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };
                
                // Start counter when in viewport
                const observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        updateCount();
                        observer.unobserve(counter);
                    }
                });
                
                observer.observe(counter);
            });
            
            // Add hover effects
            const hoverElements = document.querySelectorAll('.animate-on-hover');
            hoverElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Floating animation for elements
            const floatingElements = document.querySelectorAll('.floating-element');
            floatingElements.forEach((el, index) => {
                el.style.animation = `float ${3 + index}s ease-in-out infinite`;
            });
            
            // Typewriter effect for motto
            const mottoElement = document.querySelector('.lead');
            if (mottoElement) {
                const motto = mottoElement.textContent || mottoElement.innerText;
                if (motto && motto.trim()) {
                    let i = 0;
                    const mottoText = motto.trim();
                    
                    const typeWriter = () => {
                        if (i < mottoText.length) {
                            mottoElement.innerHTML = mottoText.substring(0, i + 1);
                            i++;
                            setTimeout(typeWriter, 50);
                        }
                    };
                    
                    // Start typing when in viewport
                    const mottoObserver = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            mottoElement.innerHTML = '';
                            typeWriter();
                            mottoObserver.unobserve(mottoElement);
                        }
                    });
                    
                    <?php
// index.php
require_once 'config.php';
require_once 'functions.php';

// Get settings
$settings = get_settings();

// Get data for homepage
$banners = get_active_banners();
$recent_news = get_recent_news(6);
$featured_news = get_featured_news(3);
$upcoming_events = get_upcoming_events(5);
$potentials = get_active_potentials(4);
$structure = get_structure();
$services = get_services();
$gallery = get_gallery(8);
$rt_rw = get_rt_rw();

// Count statistics dengan error handling
$total_news = 0;
$total_potentials = 0;
$total_services = 0;
$jumlah_penduduk = 0;
$jumlah_kk = 0;
$rt_rw_data = [];

// Hitung jumlah berita
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM berita WHERE status = 'published'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_news = $row['total'];
}

// Hitung jumlah potensi
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM potensi WHERE status = 'aktif'");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_potentials = $row['total'];
}

// Hitung jumlah layanan
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM layanan WHERE aktif = 1");
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $total_services = $row['total'];
}

// HITUNG JUMLAH PENDUDUK DENGAN BENAR
// Cek apakah tabel penduduk ada dalam database yang sama
$result = mysqli_query($conn, "SHOW TABLES LIKE 'penduduk'");
if ($result && mysqli_num_rows($result) > 0) {
    // Jika tabel penduduk ada di database utama
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM penduduk WHERE status = 'aktif'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $jumlah_penduduk = $row['total'];
    }
} else {
    // Coba cek di database sistem_desa_sekolah jika menggunakan database berbeda
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM sistem_desa_sekolah.penduduk WHERE status = 'aktif'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $jumlah_penduduk = $row['total'];
    } else {
        // Jika tidak ada tabel penduduk, hitung dari data RT/RW
        $result = mysqli_query($conn, "SELECT SUM(jumlah_kk) as total_kk, SUM(jumlah_jiwa) as total_jiwa FROM rt_rw");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $jumlah_kk = $row['total_kk'] ?: 0;
            $jumlah_penduduk = $row['total_jiwa'] ?: 0;
        }
        
        // Jika masih kosong, gunakan estimasi (rata-rata 4 orang per KK)
        if ($jumlah_penduduk == 0 && $jumlah_kk > 0) {
            $jumlah_penduduk = $jumlah_kk * 4;
        }
    }
}

// HITUNG JUMLAH KEPALA KELUARGA
if ($jumlah_kk == 0) {
    $result = mysqli_query($conn, "SELECT SUM(jumlah_kk) as total_kk FROM rt_rw");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $jumlah_kk = $row['total_kk'] ?: 0;
    }
    
    // Jika masih kosong, hitung dari tabel penduduk (unik KK)
    if ($jumlah_kk == 0) {
        $result = mysqli_query($conn, "SELECT COUNT(DISTINCT no_kk) as total_kk FROM penduduk");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $jumlah_kk = $row['total_kk'];
        }
    }
}

// Jika semua perhitungan gagal, berikan nilai default
if ($jumlah_penduduk == 0) $jumlah_penduduk = 3500;
if ($jumlah_kk == 0) $jumlah_kk = 850;

// Get data RT/RW untuk struktur
$result = mysqli_query($conn, "SELECT jenis, nomor, ketua, jumlah_kk, jumlah_jiwa FROM rt_rw ORDER BY jenis, nomor");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $rt_rw_data[] = $row;
    }
}

// Debug: Lihat nilai yang didapat
error_log("Statistik Desa: Penduduk=$jumlah_penduduk, KK=$jumlah_kk, Berita=$total_news, Potensi=$total_potentials");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['nama_desa']); ?> - Website Resmi</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animasi.css">
    
    <style>
        :root {
            --primary-color: <?php echo $settings['theme_color'] ?: '#2c3e50'; ?>;
            --secondary-color: #3498db;
        }
        
        .hero-banner {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('assets/images/banner-bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern.png');
            opacity: 0.1;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }
        
        /* Statistik Section Styles */
        .statistics-section {
            background: linear-gradient(135deg, var(--primary-color), #1a2530);
        }
        
        .stat-box {
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease, background 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-box:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .stat-box i {
            color: #3498db;
        }
        
        .stat-box h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 15px 0;
            color: #fff;
        }
        
        .stat-box p {
            font-size: 1.1rem;
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.9);
        }
        
        @media (max-width: 768px) {
            .stat-box {
                padding: 20px 15px;
            }
            
            .stat-box h3 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <main>
        <!-- Hero Section with Animation -->
        <section class="hero-banner text-white">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                        <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInDown">
                            Selamat Datang di <br>
                            <span class="text-warning"><?php echo htmlspecialchars($settings['nama_desa']); ?></span>
                        </h1>
                        <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo htmlspecialchars($settings['motto_desa']); ?>
                        </p>
                        <div class="d-flex gap-3 animate__animated animate__fadeInUp animate__delay-2s">
                            <a href="#about" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-info-circle me-2"></i> Tentang Desa
                            </a>
                            <a href="#services" class="btn btn-outline-light btn-lg px-4">
                                <i class="fas fa-handshake me-2"></i> Layanan
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                        <div class="floating-image">
                            <img src="assets/images/desa-illustration.png" alt="Desa Illustration" 
                                 class="img-fluid animate__animated animate__pulse animate__infinite">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Animated Elements -->
            <div class="floating-elements">
                <div class="floating-element el1"></div>
                <div class="floating-element el2"></div>
                <div class="floating-element el3"></div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-5 bg-light">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                        <img src="assets/images/kantor-desa.jpg" alt="Kantor Desa" 
                             class="img-fluid rounded shadow-lg animate__animated animate__zoomIn">
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <h2 class="section-title mb-4">Tentang <?php echo htmlspecialchars($settings['nama_desa']); ?></h2>
                        <?php if (!empty($settings['tentang_desa'])): ?>
                            <p class="lead"><?php echo nl2br(htmlspecialchars(substr($settings['tentang_desa'], 0, 300))); ?>...</p>
                        <?php else: ?>
                            <p class="lead"><?php echo htmlspecialchars($settings['nama_desa']); ?> adalah desa yang terletak di daerah Kuningan dengan masyarakat yang aktif dan produktif.</p>
                        <?php endif; ?>
                        <a href="modules/profil.php" class="btn btn-primary mt-3">
                            Baca Selengkapnya <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section with Counter Animation - DIPERBAIKI -->
        <section class="statistics-section py-5">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Statistik Desa</h2>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="100">
                        <div class="stat-box">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo (int)$jumlah_penduduk; ?>">0</h3>
                            <p>Jumlah Penduduk</p>
                            <small class="text-muted">Data <?php echo date('Y'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="200">
                        <div class="stat-box">
                            <i class="fas fa-home fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo (int)$jumlah_kk; ?>">0</h3>
                            <p>Kepala Keluarga</p>
                            <small class="text-muted">Data <?php echo date('Y'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="300">
                        <div class="stat-box">
                            <i class="fas fa-newspaper fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo (int)$total_news; ?>">0</h3>
                            <p>Berita & Informasi</p>
                            <small class="text-muted">Dipublikasikan</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-4" data-aos="zoom-in" data-aos-delay="400">
                        <div class="stat-box">
                            <i class="fas fa-store fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo (int)$total_potentials; ?>">0</h3>
                            <p>Potensi & UMKM</p>
                            <small class="text-muted">Aktif</small>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Statistics Row -->
                <div class="row text-center mt-4">
                    <div class="col-md-4 col-6 mb-4" data-aos="zoom-in" data-aos-delay="500">
                        <div class="stat-box">
                            <i class="fas fa-handshake fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo (int)$total_services; ?>">0</h3>
                            <p>Layanan Publik</p>
                            <small class="text-muted">Tersedia</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-4" data-aos="zoom-in" data-aos-delay="600">
                        <div class="stat-box">
                            <i class="fas fa-images fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo count($gallery); ?>">0</h3>
                            <p>Foto Galeri</p>
                            <small class="text-muted">Tersimpan</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-6 mb-4" data-aos="zoom-in" data-aos-delay="700">
                        <div class="stat-box">
                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                            <h3 class="counter" data-count="<?php echo count($upcoming_events); ?>">0</h3>
                            <p>Agenda Mendatang</p>
                            <small class="text-muted">Akan Datang</small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5" data-aos="fade-up">
                    <p class="text-light mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Data diperbarui: <?php echo date('d F Y H:i:s'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- ... bagian lainnya tetap sama ... -->

    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS (Animate On Scroll) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Lightbox -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animasi.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Counter Animation - IMPROVED VERSION
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            
            // Function to animate counter
            const animateCounter = (counter) => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000; // 2 seconds
                const frameDuration = 1000 / 60; // 60fps
                const totalFrames = Math.round(duration / frameDuration);
                const easeOutQuad = t => t * (2 - t);
                
                let frame = 0;
                
                const countTo = () => {
                    frame++;
                    const progress = easeOutQuad(frame / totalFrames);
                    const currentCount = Math.round(target * progress);
                    
                    // Format number with thousand separators
                    counter.innerText = currentCount.toLocaleString('id-ID');
                    
                    if (frame < totalFrames) {
                        requestAnimationFrame(countTo);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };
                
                countTo();
            };
            
            // Observer for counters
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.5
            });
            
            counters.forEach(counter => {
                counterObserver.observe(counter);
            });
            
            // Add hover effects
            const hoverElements = document.querySelectorAll('.animate-on-hover');
            hoverElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Floating animation for elements
            const floatingElements = document.querySelectorAll('.floating-element');
            floatingElements.forEach((el, index) => {
                el.style.animation = `float ${3 + index}s ease-in-out infinite`;
            });
            
            // Update statistics automatically every 5 minutes
            function updateStatistics() {
                fetch('api/get-statistics.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update counter values
                            document.querySelectorAll('.counter').forEach(counter => {
                                const dataType = counter.closest('.stat-box').querySelector('p').textContent;
                                if (dataType.includes('Penduduk')) {
                                    counter.setAttribute('data-count', data.penduduk);
                                } else if (dataType.includes('Keluarga')) {
                                    counter.setAttribute('data-count', data.kk);
                                } else if (dataType.includes('Berita')) {
                                    counter.setAttribute('data-count', data.berita);
                                } else if (dataType.includes('Potensi')) {
                                    counter.setAttribute('data-count', data.potensi);
                                }
                            });
                            
                            // Update timestamp
                            const timestampEl = document.querySelector('.text-light .fa-info-circle');
                            if (timestampEl) {
                                timestampEl.parentElement.innerHTML = 
                                    '<i class="fas fa-info-circle me-2"></i>' +
                                    'Data diperbarui: ' + new Date().toLocaleString('id-ID');
                            }
                        }
                    })
                    .catch(error => console.error('Error updating statistics:', error));
            }
            
            // Uncomment untuk auto-refresh statistik setiap 5 menit
            // setInterval(updateStatistics, 5 * 60 * 1000);
        });
    </script>
</body>
</html>.observe(mottoElement);
                }
            }
        });
    </script>
</body>
</html>