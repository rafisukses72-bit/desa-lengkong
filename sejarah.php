<?php
// modules/sejarah.php
require_once '../config.php';
require_once '../functions.php';

$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sejarah Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        .sejarah-hero {
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                        url('../assets/images/sejarah-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background: var(--primary-color);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
            border-radius: 3px;
        }
        
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
            margin-bottom: 40px;
        }
        
        .timeline-item:nth-child(odd) {
            left: 0;
            text-align: right;
        }
        
        .timeline-item:nth-child(even) {
            left: 50%;
            text-align: left;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border: 4px solid var(--primary-color);
            border-radius: 50%;
            top: 15px;
            z-index: 1;
        }
        
        .timeline-item:nth-child(odd)::after {
            right: -10px;
        }
        
        .timeline-item:nth-child(even)::after {
            left: -10px;
        }
        
        .timeline-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .timeline-item:nth-child(odd) .timeline-content {
            margin-right: 30px;
        }
        
        .timeline-item:nth-child(even) .timeline-content {
            margin-left: 30px;
        }
        
        .timeline-content::before {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-style: solid;
        }
        
        .timeline-item:nth-child(odd) .timeline-content::before {
            border-width: 10px 0 10px 10px;
            border-color: transparent transparent transparent white;
            right: -10px;
            top: 20px;
        }
        
        .timeline-item:nth-child(even) .timeline-content::before {
            border-width: 10px 10px 10px 0;
            border-color: transparent white transparent transparent;
            left: -10px;
            top: 20px;
        }
        
        .timeline-date {
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .timeline-item:nth-child(odd) .timeline-date {
            float: right;
        }
        
        .timeline-item:nth-child(even) .timeline-date {
            float: left;
        }
        
        .sejarah-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.5s ease;
        }
        
        .sejarah-image:hover {
            transform: scale(1.02);
        }
        
        @media (max-width: 768px) {
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
                text-align: left !important;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
            
            .timeline-item::after {
                left: 21px;
                right: auto;
            }
            
            .timeline-content {
                margin: 0 !important;
            }
            
            .timeline-content::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="sejarah-hero" data-aos="fade-down">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Sejarah Desa</h1>
            <p class="lead">Perjalanan panjang <?php echo htmlspecialchars($settings['nama_desa']); ?> dari masa ke masa</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Introduction -->
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <div class="sejarah-image">
                        <img src="../assets/images/sejarah-old.jpg" alt="Desa Zaman Dulu" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2 class="mb-4">Sejarah <?php echo htmlspecialchars($settings['nama_desa']); ?></h2>
                    <?php if ($settings['sejarah_desa']): ?>
                        <p><?php echo nl2br(htmlspecialchars(substr($settings['sejarah_desa'], 0, 500))); ?>...</p>
                    <?php else: ?>
                        <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> memiliki sejarah panjang yang penuh dengan perjuangan dan perkembangan. Berawal dari sekelompok kecil masyarakat yang tinggal di wilayah ini, desa ini terus berkembang menjadi desa yang maju dan mandiri seperti sekarang.</p>
                        <p>Nama "<?php echo htmlspecialchars($settings['nama_desa']); ?>" sendiri memiliki makna yang dalam dalam bahasa setempat, mencerminkan karakter dan harapan masyarakat desa.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-5">Lini Masa Perkembangan Desa</h2>
                    
                    <div class="timeline">
                        <!-- Item 1 -->
                        <div class="timeline-item" data-aos="fade-right">
                            <div class="timeline-date">1980</div>
                            <div class="timeline-content">
                                <h4>Pembentukan Desa</h4>
                                <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> resmi dibentuk berdasarkan Peraturan Daerah No. 15 Tahun 1980. Wilayah yang sebelumnya merupakan bagian dari desa tetangga kemudian berdiri sendiri dengan struktur pemerintahan desa yang lengkap.</p>
                            </div>
                        </div>
                        
                        <!-- Item 2 -->
                        <div class="timeline-item" data-aos="fade-left">
                            <div class="timeline-date">1985</div>
                            <div class="timeline-content">
                                <h4>Pembangunan Balai Desa</h4>
                                <p>Balai Desa pertama dibangun sebagai pusat kegiatan masyarakat dan pemerintahan. Bangunan sederhana ini menjadi saksi bisu berbagai rapat dan musyawarah penting dalam pembangunan desa.</p>
                            </div>
                        </div>
                        
                        <!-- Item 3 -->
                        <div class="timeline-item" data-aos="fade-right">
                            <div class="timeline-date">1990</div>
                            <div class="timeline-content">
                                <h4>Program Listrik Masuk Desa</h4>
                                <p>Program listrik masuk desa berhasil diwujudkan, memberikan akses penerangan bagi seluruh masyarakat. Momen bersejarah ini menjadi titik balik kemajuan desa dalam berbagai aspek kehidupan.</p>
                            </div>
                        </div>
                        
                        <!-- Item 4 -->
                        <div class="timeline-item" data-aos="fade-left">
                            <div class="timeline-date">1995</div>
                            <div class="timeline-content">
                                <h4>Pembangunan Jalan Desa</h4>
                                <p>Jalan desa pertama yang beraspal dibangun, menghubungkan desa dengan kecamatan. Infrastruktur ini sangat mendukung perekonomian masyarakat dengan mempermudah distribusi hasil pertanian.</p>
                            </div>
                        </div>
                        
                        <!-- Item 5 -->
                        <div class="timeline-item" data-aos="fade-right">
                            <div class="timeline-date">2000</div>
                            <div class="timeline-content">
                                <h4>Revolusi Pertanian</h4>
                                <p>Program intensifikasi pertanian dimulai dengan pengenalan teknologi modern. Produktivitas pertanian meningkat signifikan, menjadikan desa sebagai lumbung padi wilayah kecamatan.</p>
                            </div>
                        </div>
                        
                        <!-- Item 6 -->
                        <div class="timeline-item" data-aos="fade-left">
                            <div class="timeline-date">2005</div>
                            <div class="timeline-content">
                                <h4>Pengembangan Pendidikan</h4>
                                <p>Pembangunan SDN <?php echo htmlspecialchars($settings['nama_desa']); ?> 01 dan 02 memberikan akses pendidikan dasar yang lebih baik bagi anak-anak desa. Anggaran pendidikan desa ditingkatkan untuk mendukung program ini.</p>
                            </div>
                        </div>
                        
                        <!-- Item 7 -->
                        <div class="timeline-item" data-aos="fade-right">
                            <div class="timeline-date">2010</div>
                            <div class="timeline-content">
                                <h4>Digitalisasi Administrasi</h4>
                                <p>Sistem administrasi desa mulai didigitalkan. Pelayanan kepada masyarakat menjadi lebih cepat dan transparan dengan implementasi sistem informasi desa.</p>
                            </div>
                        </div>
                        
                        <!-- Item 8 -->
                        <div class="timeline-item" data-aos="fade-left">
                            <div class="timeline-date">2015</div>
                            <div class="timeline-content">
                                <h4>Penghargaan Desa Mandiri</h4>
                                <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> mendapatkan penghargaan sebagai Desa Mandiri tingkat kabupaten. Prestasi ini menjadi bukti keberhasilan pembangunan yang berkelanjutan.</p>
                            </div>
                        </div>
                        
                        <!-- Item 9 -->
                        <div class="timeline-item" data-aos="fade-right">
                            <div class="timeline-date">2020</div>
                            <div class="timeline-content">
                                <h4>Pandemi dan Solidaritas</h4>
                                <p>Masyarakat desa menunjukkan solidaritas tinggi dalam menghadapi pandemi COVID-19. Berbagai program bantuan sosial dan kesehatan dilaksanakan dengan baik.</p>
                            </div>
                        </div>
                        
                        <!-- Item 10 -->
                        <div class="timeline-item" data-aos="fade-left">
                            <div class="timeline-date">2023 - Sekarang</div>
                            <div class="timeline-content">
                                <h4>Menuju Desa Digital</h4>
                                <p>Implementasi website desa dan sistem pelayanan online. Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> terus berinovasi untuk memberikan pelayanan terbaik bagi masyarakat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historical Photos -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-5">Foto Sejarah</h2>
                    <div class="row">
                        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="100">
                            <div class="card">
                                <img src="../assets/images/sejarah1.jpg" class="card-img-top" alt="Desa Tahun 1980">
                                <div class="card-body">
                                    <h5 class="card-title">Desa Tahun 1980</h5>
                                    <p class="card-text">Kondisi awal desa dengan rumah-rumah tradisional</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="200">
                            <div class="card">
                                <img src="../assets/images/sejarah2.jpg" class="card-img-top" alt="Pembangunan Pertama">
                                <div class="card-body">
                                    <h5 class="card-title">Pembangunan Balai Desa 1985</h5>
                                    <p class="card-text">Masyarakat gotong royong membangun balai desa</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="300">
                            <div class="card">
                                <img src="../assets/images/sejarah3.jpg" class="card-img-top" alt="Panen Raya">
                                <div class="card-body">
                                    <h5 class="card-title">Panen Raya 1995</h5>
                                    <p class="card-text">Kegembiraan masyarakat saat panen melimpah</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Legacy Section -->
            <div class="row" data-aos="fade-up">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body text-center p-5">
                            <h2 class="mb-4">Warisan Budaya & Nilai Luhur</h2>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="p-4">
                                        <i class="fas fa-hands-helping fa-3x text-primary mb-3"></i>
                                        <h4>Gotong Royong</h4>
                                        <p>Nilai kebersamaan dan saling membantu menjadi ciri khas masyarakat desa</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-4">
                                        <i class="fas fa-seedling fa-3x text-success mb-3"></i>
                                        <h4>Kearifan Lokal</h4>
                                        <p>Pengetahuan tradisional dalam pertanian dan pengelolaan sumber daya alam</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-4">
                                        <i class="fas fa-landmark fa-3x text-warning mb-3"></i>
                                        <h4>Adat Istiadat</h4>
                                        <p>Tradisi dan upacara adat yang tetap dilestarikan hingga sekarang</p>
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
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/animasi.js"></script>
    
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Timeline animation
        document.addEventListener('DOMContentLoaded', function() {
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.2}s`;
            });
            
            // Image hover effect
            const sejarahImages = document.querySelectorAll('.sejarah-image');
            sejarahImages.forEach(img => {
                img.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                img.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>