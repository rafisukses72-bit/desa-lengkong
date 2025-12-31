<?php
// modules/profil.php
require_once '../config.php';
require_once '../functions.php';

$settings = get_settings();
$structure = get_structure();
$rt_rw = get_rt_rw();

// Hitung total penduduk dari RT/RW
$total_kk = 0;
$total_penduduk = 0;
foreach ($rt_rw as $data) {
    $total_kk += $data['jumlah_kk'];
    $total_penduduk += $data['jumlah_kk'] * 4; // Asumsi 4 orang per KK
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        .profil-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('../assets/images/profil-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .profil-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .profil-card:hover {
            transform: translateY(-10px);
        }
        
        .profil-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .profil-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .tab-content {
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .nav-tabs .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            border: none;
            padding: 15px 30px;
            margin-right: 10px;
            border-radius: 10px 10px 0 0;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .nav-tabs .nav-link:hover {
            background: rgba(52, 152, 219, 0.1);
        }
        
        .rt-rw-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .rt-rw-card:hover {
            background: #e9ecef;
            transform: translateX(10px);
        }
        
        .visi-misi-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 30px;
        }
        
        .visi-misi-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--secondary-color);
            font-weight: bold;
        }
        
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="profil-hero" data-aos="fade-down">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Profil Desa</h1>
            <p class="lead">Mengenal lebih dekat <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Quick Stats -->
            <div class="row mb-5">
                <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="profil-card text-center p-4">
                        <div class="profil-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="counter" data-count="<?php echo $total_penduduk; ?>">0</h3>
                        <p>Jumlah Penduduk</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="profil-card text-center p-4">
                        <div class="profil-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="counter" data-count="<?php echo $total_kk; ?>">0</h3>
                        <p>Kepala Keluarga</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="profil-card text-center p-4">
                        <div class="profil-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3>4</h3>
                        <p>Dusun/RW</p>
                    </div>
                </div>
                
                <div class="col-md-3 col-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="profil-card text-center p-4">
                        <div class="profil-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <h3>12</h3>
                        <p>RT</p>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Section -->
            <div class="row" data-aos="fade-up">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="profilTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tentang-tab" data-bs-toggle="tab" 
                                    data-bs-target="#tentang" type="button" role="tab">
                                <i class="fas fa-info-circle me-2"></i>Tentang Desa
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="visi-tab" data-bs-toggle="tab" 
                                    data-bs-target="#visi" type="button" role="tab">
                                <i class="fas fa-bullseye me-2"></i>Visi & Misi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sejarah-tab" data-bs-toggle="tab" 
                                    data-bs-target="#sejarah" type="button" role="tab">
                                <i class="fas fa-history me-2"></i>Sejarah
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="wilayah-tab" data-bs-toggle="tab" 
                                    data-bs-target="#wilayah" type="button" role="tab">
                                <i class="fas fa-map me-2"></i>Wilayah
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profilTabContent">
                        <!-- Tentang Desa -->
                        <div class="tab-pane fade show active" id="tentang" role="tabpanel">
                            <h2 class="mb-4">Tentang <?php echo htmlspecialchars($settings['nama_desa']); ?></h2>
                            <div class="row">
                                <div class="col-md-8">
                                    <?php echo nl2br(htmlspecialchars($settings['tentang_desa'] ?: 'Desa ' . $settings['nama_desa'] . ' adalah desa yang terletak di wilayah yang strategis dengan potensi alam dan sumber daya manusia yang melimpah. Desa ini terus berkomitmen untuk meningkatkan kesejahteraan masyarakat melalui berbagai program pembangunan.')); ?>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-map-marker-alt me-2"></i>Lokasi</h5>
                                            <p class="card-text"><?php echo htmlspecialchars($settings['alamat_kantor']); ?></p>
                                            
                                            <h5 class="card-title mt-4"><i class="fas fa-phone me-2"></i>Kontak</h5>
                                            <p class="card-text"><?php echo htmlspecialchars($settings['telepon']); ?></p>
                                            
                                            <h5 class="card-title mt-4"><i class="fas fa-envelope me-2"></i>Email</h5>
                                            <p class="card-text"><?php echo htmlspecialchars($settings['email']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Visi & Misi -->
                        <div class="tab-pane fade" id="visi" role="tabpanel">
                            <h2 class="mb-4">Visi & Misi Desa</h2>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Visi</h4>
                                        </div>
                                        <div class="card-body">
                                            <blockquote class="blockquote">
                                                <p class="lead"><?php echo htmlspecialchars($settings['visi'] ?: 'Mewujudkan Desa ' . $settings['nama_desa'] . ' yang Maju, Mandiri, dan Sejahtera'); ?></p>
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h4 class="mb-0"><i class="fas fa-bullseye me-2"></i>Misi</h4>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($settings['misi']): ?>
                                                <?php echo nl2br(htmlspecialchars($settings['misi'])); ?>
                                            <?php else: ?>
                                                <ul class="visi-misi-list">
                                                    <li>Meningkatkan kualitas sumber daya manusia melalui pendidikan dan pelatihan</li>
                                                    <li>Mengembangkan potensi ekonomi lokal dan UMKM desa</li>
                                                    <li>Meningkatkan infrastruktur dasar untuk kesejahteraan masyarakat</li>
                                                    <li>Menguatkan kelembagaan desa dan partisipasi masyarakat</li>
                                                    <li>Melestarikan budaya dan lingkungan desa</li>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sejarah -->
                        <div class="tab-pane fade" id="sejarah" role="tabpanel">
                            <h2 class="mb-4">Sejarah Desa</h2>
                            <?php if ($settings['sejarah_desa']): ?>
                                <?php echo nl2br(htmlspecialchars($settings['sejarah_desa'])); ?>
                            <?php else: ?>
                                <div class="timeline">
                                    <div class="timeline-item" data-aos="fade-right">
                                        <div class="timeline-date">1980</div>
                                        <div class="timeline-content">
                                            <h5>Pembentukan Desa</h5>
                                            <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> resmi dibentuk berdasarkan Peraturan Daerah setempat.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item" data-aos="fade-left">
                                        <div class="timeline-date">1990</div>
                                        <div class="timeline-content">
                                            <h5>Pembangunan Infrastruktur</h5>
                                            <p>Pembangunan jalan desa dan fasilitas umum pertama kali dilakukan.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item" data-aos="fade-right">
                                        <div class="timeline-date">2000</div>
                                        <div class="timeline-content">
                                            <h5>Pengembangan Pertanian</h5>
                                            <p>Program pengembangan pertanian modern mulai diimplementasikan.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item" data-aos="fade-left">
                                        <div class="timeline-date">2010</div>
                                        <div class="timeline-content">
                                            <h5>Digitalisasi Administrasi</h5>
                                            <p>Sistem administrasi desa mulai didigitalkan untuk pelayanan yang lebih baik.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item" data-aos="fade-right">
                                        <div class="timeline-date">2020</div>
                                        <div class="timeline-content">
                                            <h5>Desa Mandiri</h5>
                                            <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> mendapatkan penghargaan sebagai desa mandiri.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Wilayah -->
                        <div class="tab-pane fade" id="wilayah" role="tabpanel">
                            <h2 class="mb-4">Wilayah Administratif</h2>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h4><i class="fas fa-map-signs me-2"></i>Batas Wilayah</h4>
                                    <ul class="list-group mb-4">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Sebelah Utara</span>
                                            <span class="fw-bold">Desa Sebelah Utara</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Sebelah Selatan</span>
                                            <span class="fw-bold">Desa Sebelah Selatan</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Sebelah Timur</span>
                                            <span class="fw-bold">Desa Sebelah Timur</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Sebelah Barat</span>
                                            <span class="fw-bold">Desa Sebelah Barat</span>
                                        </li>
                                    </ul>
                                    
                                    <h4><i class="fas fa-users me-2"></i>Struktur RT/RW</h4>
                                    <div class="row">
                                        <?php foreach ($rt_rw as $data): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="rt-rw-card">
                                                    <h5 class="mb-2"><?php echo $data['jenis']; ?> <?php echo $data['nomor']; ?></h5>
                                                    <p class="mb-1"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($data['ketua']); ?></p>
                                                    <p class="mb-0"><i class="fas fa-home me-2"></i><?php echo $data['jumlah_kk']; ?> KK</p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h4><i class="fas fa-map-marked-alt me-2"></i>Peta Desa</h4>
                                    <div class="map-container">
                                        <iframe 
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.234567890123!2d107.618610!3d-6.903890!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNTQnMTQuMCJTIDEwN8KwMzcnMTcuMCJF!5e0!3m2!1sid!2sid!4v1640000000000!5m2!1sid!2sid" 
                                            width="100%" 
                                            height="100%" 
                                            style="border:0;" 
                                            allowfullscreen="" 
                                            loading="lazy">
                                        </iframe>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h5><i class="fas fa-info-circle me-2"></i>Informasi Geografis</h5>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-mountain me-2"></i>Ketinggian: 500 mdpl</li>
                                            <li><i class="fas fa-thermometer-half me-2"></i>Suhu Rata-rata: 25-30°C</li>
                                            <li><i class="fas fa-cloud-rain me-2"></i>Curah Hujan: 2000 mm/tahun</li>
                                            <li><i class="fas fa-ruler-combined me-2"></i>Luas Wilayah: 250 Ha</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pimpinan Desa -->
            <div class="row mt-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-center mb-5">Pimpinan Desa</h2>
                    <div class="row justify-content-center">
                        <?php foreach ($structure as $index => $person): ?>
                            <?php if ($index < 4): ?>
                                <div class="col-lg-3 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                                    <div class="card text-center h-100 animate-on-hover">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <?php if ($person['foto']): ?>
                                                    <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                         alt="<?php echo htmlspecialchars($person['nama']); ?>"
                                                         class="img-fluid rounded-circle" 
                                                         style="width: 150px; height: 150px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 150px; height: 150px;">
                                                        <i class="fas fa-user fa-4x text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="card-title"><?php echo htmlspecialchars($person['nama']); ?></h5>
                                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                            <?php if ($person['kontak']): ?>
                                                <p class="text-muted">
                                                    <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($person['kontak']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="struktur-desa.php" class="btn btn-primary">
                            Lihat Struktur Lengkap <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/animasi.js"></script>
    
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Counter Animation
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const duration = 2000;
                const increment = target / (duration / 16);
                
                let current = 0;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target.toLocaleString('id-ID');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString('id-ID');
                    }
                }, 16);
            });
            
            // Tab animation
            const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const target = this.getAttribute('data-bs-target');
                    const tabPane = document.querySelector(target);
                    
                    // Reset animations
                    tabPane.classList.remove('animate__fadeIn');
                    void tabPane.offsetWidth; // Trigger reflow
                    tabPane.classList.add('animate__fadeIn');
                });
            });
        });
    </script>
</body>
</html>