<?php
// modules/struktur-desa.php
require_once '../config.php';
require_once '../functions.php';

$settings = get_settings();

// Get structure data
$query = "SELECT * FROM struktur_desa WHERE status = 'aktif' ORDER BY urutan ASC";
$result = mysqli_query($conn, $query);
$structure = [];
while ($row = mysqli_fetch_assoc($result)) {
    $structure[] = $row;
}

// Get RT/RW data
$rt_rw = get_rt_rw();

// Group structure by category
$categories = [
    'pemerintahan' => [],
    'badan_perwakilan' => [],
    'lembaga' => []
];

foreach ($structure as $person) {
    if (stripos($person['jabatan'], 'kepala') !== false || 
        stripos($person['jabatan'], 'sekretaris') !== false || 
        stripos($person['jabatan'], 'bendahara') !== false) {
        $categories['pemerintahan'][] = $person;
    } elseif (stripos($person['jabatan'], 'bpd') !== false || 
              stripos($person['jabatan'], 'badan') !== false) {
        $categories['badan_perwakilan'][] = $person;
    } else {
        $categories['lembaga'][] = $person;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        .struktur-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('../assets/images/struktur-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .org-chart {
            position: relative;
            padding: 20px;
            text-align: center;
        }
        
        .level-1 {
            margin-bottom: 50px;
        }
        
        .level-2 {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .level-3 {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .org-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            width: 250px;
            position: relative;
        }
        
        .org-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .org-card.kepala-desa {
            border: 3px solid var(--primary-color);
            width: 280px;
        }
        
        .org-photo {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #f8f9fa;
        }
        
        .org-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .org-lines {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 20px;
            background: var(--primary-color);
        }
        
        .level-2 .org-card::before {
            content: '';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 30px;
            background: var(--primary-color);
        }
        
        .level-3 .org-card::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 20px;
            background: var(--primary-color);
        }
        
        .badge-jabatan {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--secondary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .rt-rw-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .rt-rw-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .rt-rw-item:hover {
            background: #e9ecef;
            transform: translateX(10px);
        }
        
        .rt-rw-header {
            background: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .contact-info {
            list-style: none;
            padding: 0;
        }
        
        .contact-info li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .contact-info li:last-child {
            border-bottom: none;
        }
        
        .contact-info i {
            width: 20px;
            color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .level-2, .level-3 {
                flex-direction: column;
                align-items: center;
            }
            
            .org-card {
                width: 100%;
                max-width: 300px;
            }
            
            .rt-rw-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="struktur-hero" data-aos="fade-down">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Struktur Organisasi Desa</h1>
            <p class="lead">Tata kelola pemerintahan <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Organizational Chart -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-5">Bagan Struktur Organisasi</h2>
                    
                    <div class="org-chart" data-aos="fade-up">
                        <!-- Level 1: Kepala Desa -->
                        <div class="level-1">
                            <?php 
                            $kepala_desa = null;
                            foreach ($structure as $person) {
                                if (stripos($person['jabatan'], 'kepala desa') !== false) {
                                    $kepala_desa = $person;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($kepala_desa): ?>
                                <div class="org-card mx-auto kepala-desa animate-on-hover">
                                    <div class="badge-jabatan">Pimpinan</div>
                                    <div class="org-photo">
                                        <?php if ($kepala_desa['foto']): ?>
                                            <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($kepala_desa['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($kepala_desa['nama']); ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="mb-2"><?php echo htmlspecialchars($kepala_desa['nama']); ?></h4>
                                    <p class="text-primary fw-bold"><?php echo htmlspecialchars($kepala_desa['jabatan']); ?></p>
                                    <?php if ($kepala_desa['kontak']): ?>
                                        <p class="text-muted">
                                            <i class="fas fa-phone me-1"></i> 
                                            <?php echo htmlspecialchars($kepala_desa['kontak']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Level 2: Sekretaris & Bendahara -->
                        <div class="level-2">
                            <?php 
                            $sekretaris = null;
                            $bendahara = null;
                            
                            foreach ($structure as $person) {
                                if (stripos($person['jabatan'], 'sekretaris') !== false) {
                                    $sekretaris = $person;
                                }
                                if (stripos($person['jabatan'], 'bendahara') !== false) {
                                    $bendahara = $person;
                                }
                            }
                            ?>
                            
                            <?php if ($sekretaris): ?>
                                <div class="org-card animate-on-hover" data-aos="fade-right" data-aos-delay="100">
                                    <div class="badge-jabatan">Staf</div>
                                    <div class="org-photo">
                                        <?php if ($sekretaris['foto']): ?>
                                            <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($sekretaris['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($sekretaris['nama']); ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="mb-2"><?php echo htmlspecialchars($sekretaris['nama']); ?></h5>
                                    <p class="text-primary fw-bold"><?php echo htmlspecialchars($sekretaris['jabatan']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($bendahara): ?>
                                <div class="org-card animate-on-hover" data-aos="fade-left" data-aos-delay="100">
                                    <div class="badge-jabatan">Staf</div>
                                    <div class="org-photo">
                                        <?php if ($bendahara['foto']): ?>
                                            <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($bendahara['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($bendahara['nama']); ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="mb-2"><?php echo htmlspecialchars($bendahara['nama']); ?></h5>
                                    <p class="text-primary fw-bold"><?php echo htmlspecialchars($bendahara['jabatan']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Level 3: Kasi & Staff -->
                        <div class="level-3">
                            <?php 
                            $staff_count = 0;
                            foreach ($structure as $person):
                                if ($person === $kepala_desa || $person === $sekretaris || $person === $bendahara) continue;
                                
                                if ($staff_count < 6): // Limit to 6 for display
                            ?>
                                <div class="org-card animate-on-hover" 
                                     data-aos="fade-up" 
                                     data-aos-delay="<?php echo ($staff_count % 3) * 100; ?>">
                                    <div class="org-photo">
                                        <?php if ($person['foto']): ?>
                                            <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($person['nama']); ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h6 class="mb-2"><?php echo htmlspecialchars($person['nama']); ?></h6>
                                    <p class="text-primary fw-bold small"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                </div>
                            <?php 
                                $staff_count++;
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detail Structure by Category -->
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-5">Detail Struktur Perangkat Desa</h2>
                    
                    <div class="accordion" id="structureAccordion">
                        <!-- Pemerintahan Desa -->
                        <div class="accordion-item" data-aos="fade-up">
                            <h2 class="accordion-header" id="headingPemerintahan">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapsePemerintahan" aria-expanded="true">
                                    <i class="fas fa-landmark me-3"></i> Pemerintahan Desa
                                </button>
                            </h2>
                            <div id="collapsePemerintahan" class="accordion-collapse collapse show" 
                                 data-bs-parent="#structureAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <?php foreach ($categories['pemerintahan'] as $index => $person): ?>
                                            <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                                                <div class="card h-100 animate-on-hover">
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <?php if ($person['foto']): ?>
                                                                <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($person['nama']); ?>"
                                                                     class="img-fluid rounded-circle"
                                                                     style="width: 120px; height: 120px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                                     style="width: 120px; height: 120px;">
                                                                    <i class="fas fa-user fa-3x text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <h5 class="card-title"><?php echo htmlspecialchars($person['nama']); ?></h5>
                                                        <p class="text-primary fw-bold"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                                        <?php if ($person['deskripsi']): ?>
                                                            <p class="card-text small"><?php echo htmlspecialchars($person['deskripsi']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($person['kontak']): ?>
                                                            <div class="mt-3">
                                                                <a href="tel:<?php echo htmlspecialchars($person['kontak']); ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-phone me-1"></i> Kontak
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Badan Perwakilan -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                            <h2 class="accordion-header" id="headingBPD">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseBPD" aria-expanded="false">
                                    <i class="fas fa-users me-3"></i> Badan Permusyawaratan Desa (BPD)
                                </button>
                            </h2>
                            <div id="collapseBPD" class="accordion-collapse collapse" 
                                 data-bs-parent="#structureAccordion">
                                <div class="accordion-body">
                                    <?php if (!empty($categories['badan_perwakilan'])): ?>
                                        <div class="row">
                                            <?php foreach ($categories['badan_perwakilan'] as $index => $person): ?>
                                                <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                                                    <div class="card h-100 animate-on-hover">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <?php if ($person['foto']): ?>
                                                                    <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($person['nama']); ?>"
                                                                         class="img-fluid rounded-circle"
                                                                         style="width: 120px; height: 120px; object-fit: cover;">
                                                                <?php else: ?>
                                                                    <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                                         style="width: 120px; height: 120px;">
                                                                        <i class="fas fa-user fa-3x text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <h5 class="card-title"><?php echo htmlspecialchars($person['nama']); ?></h5>
                                                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Data BPD sedang dalam proses pembentukan.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lembaga Desa -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                            <h2 class="accordion-header" id="headingLembaga">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapseLembaga" aria-expanded="false">
                                    <i class="fas fa-building me-3"></i> Lembaga Kemasyarakatan
                                </button>
                            </h2>
                            <div id="collapseLembaga" class="accordion-collapse collapse" 
                                 data-bs-parent="#structureAccordion">
                                <div class="accordion-body">
                                    <?php if (!empty($categories['lembaga'])): ?>
                                        <div class="row">
                                            <?php foreach ($categories['lembaga'] as $index => $person): ?>
                                                <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                                                    <div class="card h-100 animate-on-hover">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <?php if ($person['foto']): ?>
                                                                    <img src="../assets/uploads/struktur/<?php echo htmlspecialchars($person['foto']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($person['nama']); ?>"
                                                                         class="img-fluid rounded-circle"
                                                                         style="width: 120px; height: 120px; object-fit: cover;">
                                                                <?php else: ?>
                                                                    <div class="mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                                         style="width: 120px; height: 120px;">
                                                                        <i class="fas fa-user fa-3x text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <h5 class="card-title"><?php echo htmlspecialchars($person['nama']); ?></h5>
                                                            <p class="text-primary fw-bold"><?php echo htmlspecialchars($person['jabatan']); ?></p>
                                                            <?php if ($person['tugas']): ?>
                                                                <p class="card-text small"><?php echo htmlspecialchars($person['tugas']); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Data lembaga kemasyarakatan akan segera diupdate.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RT/RW Structure -->
            <div class="row" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-center mb-5">Struktur RT & RW</h2>
                    
                    <div class="row mb-4">
                        <?php 
                        $rw_data = [];
                        $rt_data = [];
                        
                        foreach ($rt_rw as $data) {
                            if ($data['jenis'] === 'RW') {
                                $rw_data[] = $data;
                            } else {
                                $rt_data[] = $data;
                            }
                        }
                        ?>
                        
                        <!-- RW Section -->
                        <div class="col-lg-6 mb-4">
                            <h4 class="mb-4"><i class="fas fa-users me-2"></i> Rukun Warga (RW)</h4>
                            <div class="rt-rw-grid">
                                <?php foreach ($rw_data as $index => $rw): ?>
                                    <div class="rt-rw-item animate-on-hover" data-aos="fade-right" data-aos-delay="<?php echo $index * 100; ?>">
                                        <div class="rt-rw-header">
                                            <h5 class="mb-0">RW <?php echo $rw['nomor']; ?></h5>
                                        </div>
                                        <ul class="contact-info">
                                            <li>
                                                <i class="fas fa-user"></i> 
                                                <strong>Ketua:</strong> <?php echo htmlspecialchars($rw['ketua']); ?>
                                            </li>
                                            <li>
                                                <i class="fas fa-home"></i> 
                                                <strong>KK:</strong> <?php echo $rw['jumlah_kk']; ?> Kepala Keluarga
                                            </li>
                                            <?php if ($rw['wilayah']): ?>
                                                <li>
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    <strong>Wilayah:</strong> <?php echo htmlspecialchars($rw['wilayah']); ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- RT Section -->
                        <div class="col-lg-6 mb-4">
                            <h4 class="mb-4"><i class="fas fa-user-friends me-2"></i> Rukun Tetangga (RT)</h4>
                            <div class="rt-rw-grid">
                                <?php foreach ($rt_data as $index => $rt): ?>
                                    <div class="rt-rw-item animate-on-hover" data-aos="fade-left" data-aos-delay="<?php echo $index * 100; ?>">
                                        <div class="rt-rw-header">
                                            <h5 class="mb-0">RT <?php echo $rt['nomor']; ?></h5>
                                        </div>
                                        <ul class="contact-info">
                                            <li>
                                                <i class="fas fa-user"></i> 
                                                <strong>Ketua:</strong> <?php echo htmlspecialchars($rt['ketua']); ?>
                                            </li>
                                            <li>
                                                <i class="fas fa-home"></i> 
                                                <strong>KK:</strong> <?php echo $rt['jumlah_kk']; ?> Kepala Keluarga
                                            </li>
                                            <?php if ($rt['wilayah']): ?>
                                                <li>
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    <strong>Wilayah:</strong> <?php echo htmlspecialchars($rt['wilayah']); ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center p-5">
                                    <h3 class="mb-4"><i class="fas fa-headset me-2"></i> Kontak & Informasi</h3>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="p-4">
                                                <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                                                <h5>Alamat Kantor</h5>
                                                <p><?php echo htmlspecialchars($settings['alamat_kantor']); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="p-4">
                                                <i class="fas fa-phone fa-2x mb-3"></i>
                                                <h5>Telepon</h5>
                                                <p><?php echo htmlspecialchars($settings['telepon']); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="p-4">
                                                <i class="fas fa-clock fa-2x mb-3"></i>
                                                <h5>Jam Kerja</h5>
                                                <p>Senin - Jumat<br>08:00 - 16:00 WIB</p>
                                            </div>
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
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/animasi.js"></script>
    
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Accordion animation
        document.addEventListener('DOMContentLoaded', function() {
            const accordionButtons = document.querySelectorAll('.accordion-button');
            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const target = this.getAttribute('data-bs-target');
                    const collapseElement = document.querySelector(target);
                    
                    // Animate content when expanded
                    if (!this.classList.contains('collapsed')) {
                        const body = collapseElement.querySelector('.accordion-body');
                        body.classList.remove('animate__fadeIn');
                        void body.offsetWidth; // Trigger reflow
                        body.classList.add('animate__fadeIn');
                    }
                });
            });
            
            // Add hover effect to org cards
            const orgCards = document.querySelectorAll('.org-card, .rt-rw-item');
            orgCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>