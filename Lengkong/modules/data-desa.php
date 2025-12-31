<?php
// modules/data-desa.php
require_once '../config.php';
require_once '../functions.php';

$settings = get_settings();

// Get RT/RW data
$rt_rw = get_rt_rw();

// Calculate statistics
$total_kk = 0;
foreach ($rt_rw as $data) {
    $total_kk += $data['jumlah_kk'];
}
$total_penduduk = $total_kk * 4; // Estimation

// Sample demographic data
$demographic_data = [
    '2023' => [
        'total' => 3500,
        'laki_laki' => 1750,
        'perempuan' => 1750,
        'kk' => 875,
        'usia_0_14' => 1050,
        'usia_15_64' => 2275,
        'usia_65_plus' => 175,
        'petani' => 700,
        'wiraswasta' => 525,
        'pns' => 175,
        'buruh' => 350,
        'lainnya' => 1750
    ],
    '2022' => [
        'total' => 3450,
        'laki_laki' => 1725,
        'perempuan' => 1725,
        'kk' => 862,
        'usia_0_14' => 1035,
        'usia_15_64' => 2242,
        'usia_65_plus' => 173,
        'petani' => 690,
        'wiraswasta' => 517,
        'pns' => 172,
        'buruh' => 345,
        'lainnya' => 1725
    ]
];

$years = array_keys($demographic_data);
$selected_year = isset($_GET['tahun']) ? $_GET['tahun'] : end($years);
$data = $demographic_data[$selected_year] ?? end($demographic_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        .data-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('../assets/images/data-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-trend {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            height: 100%;
        }
        
        .chart-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
        
        .progress-container {
            margin: 20px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .data-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .data-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .data-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .age-pyramid {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 10px;
            height: 300px;
            margin: 30px 0;
        }
        
        .pyramid-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 80px;
        }
        
        .bar-male, .bar-female {
            width: 100%;
            transition: height 0.5s ease;
        }
        
        .bar-male {
            background: var(--secondary-color);
            border-radius: 5px 5px 0 0;
        }
        
        .bar-female {
            background: #e74c3c;
            border-radius: 0 0 5px 5px;
        }
        
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }
            
            .data-grid {
                grid-template-columns: 1fr;
            }
            
            .age-pyramid {
                height: 200px;
            }
            
            .pyramid-bar {
                width: 40px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="data-hero" data-aos="fade-down">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Data & Statistik Desa</h1>
            <p class="lead">Informasi kependudukan dan statistik Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Year Selector -->
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Pilih Tahun Data</h4>
                                <div class="btn-group">
                                    <?php foreach ($years as $year): ?>
                                        <a href="?tahun=<?php echo $year; ?>" 
                                           class="btn <?php echo $selected_year == $year ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <?php echo $year; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Key Statistics -->
            <div class="row mb-5">
                <!-- Total Population -->
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card bg-primary text-white">
                        <div class="stat-icon bg-white text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-number counter" data-count="<?php echo $data['total']; ?>">0</h3>
                        <p>Total Penduduk</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>+1.4% dari tahun lalu</span>
                        </div>
                    </div>
                </div>
                
                <!-- Gender Distribution -->
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card bg-success text-white">
                        <div class="stat-icon bg-white text-success">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <h4 class="stat-number"><?php echo number_format($data['laki_laki']); ?></h4>
                                <small>Laki-laki</small>
                            </div>
                            <div class="col-6">
                                <h4 class="stat-number"><?php echo number_format($data['perempuan']); ?></h4>
                                <small>Perempuan</small>
                            </div>
                        </div>
                        <p>Komposisi Gender</p>
                    </div>
                </div>
                
                <!-- Households -->
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card bg-warning text-white">
                        <div class="stat-icon bg-white text-warning">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3 class="stat-number"><?php echo number_format($data['kk']); ?></h3>
                        <p>Kepala Keluarga</p>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>Rata-rata 4.0 orang/KK</span>
                        </div>
                    </div>
                </div>
                
                <!-- Age Groups -->
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card bg-danger text-white">
                        <div class="stat-icon bg-white text-danger">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <h5><?php echo round($data['usia_0_14'] / $data['total'] * 100); ?>%</h5>
                                <small>0-14</small>
                            </div>
                            <div class="col-4">
                                <h5><?php echo round($data['usia_15_64'] / $data['total'] * 100); ?>%</h5>
                                <small>15-64</small>
                            </div>
                            <div class="col-4">
                                <h5><?php echo round($data['usia_65_plus'] / $data['total'] * 100); ?>%</h5>
                                <small>65+</small>
                            </div>
                        </div>
                        <p>Kelompok Usia</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts & Graphs -->
            <div class="row mb-5">
                <!-- Population Pyramid -->
                <div class="col-lg-6 mb-4" data-aos="fade-right">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-chart-bar me-2"></i>Piramida Penduduk
                        </h3>
                        
                        <div class="age-pyramid" id="agePyramid">
                            <!-- Age 0-14 -->
                            <div class="pyramid-bar">
                                <div class="bar-male" style="height: <?php echo ($data['usia_0_14'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="bar-female" style="height: <?php echo ($data['usia_0_14'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="pyramid-label mt-2">0-14</div>
                            </div>
                            
                            <!-- Age 15-64 -->
                            <div class="pyramid-bar">
                                <div class="bar-male" style="height: <?php echo ($data['usia_15_64'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="bar-female" style="height: <?php echo ($data['usia_15_64'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="pyramid-label mt-2">15-64</div>
                            </div>
                            
                            <!-- Age 65+ -->
                            <div class="pyramid-bar">
                                <div class="bar-male" style="height: <?php echo ($data['usia_65_plus'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="bar-female" style="height: <?php echo ($data['usia_65_plus'] / 2 / $data['total'] * 200); ?>px;"></div>
                                <div class="pyramid-label mt-2">65+</div>
                            </div>
                        </div>
                        
                        <div class="row text-center mt-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="gender-legend male me-2"></div>
                                    <span>Laki-laki</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="gender-legend female me-2"></div>
                                    <span>Perempuan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Occupation Distribution -->
                <div class="col-lg-6 mb-4" data-aos="fade-left">
                    <div class="chart-container">
                        <h3 class="chart-title">
                            <i class="fas fa-briefcase me-2"></i>Distribusi Pekerjaan
                        </h3>
                        
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Petani</span>
                                <span><?php echo round($data['petani'] / $data['total'] * 100); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" 
                                     style="width: <?php echo $data['petani'] / $data['total'] * 100; ?>%"></div>
                            </div>
                            
                            <div class="progress-label">
                                <span>Wiraswasta</span>
                                <span><?php echo round($data['wiraswasta'] / $data['total'] * 100); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info" 
                                     style="width: <?php echo $data['wiraswasta'] / $data['total'] * 100; ?>%"></div>
                            </div>
                            
                            <div class="progress-label">
                                <span>PNS</span>
                                <span><?php echo round($data['pns'] / $data['total'] * 100); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" 
                                     style="width: <?php echo $data['pns'] / $data['total'] * 100; ?>%"></div>
                            </div>
                            
                            <div class="progress-label">
                                <span>Buruh</span>
                                <span><?php echo round($data['buruh'] / $data['total'] * 100); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" 
                                     style="width: <?php echo $data['buruh'] / $data['total'] * 100); ?>%"></div>
                            </div>
                            
                            <div class="progress-label">
                                <span>Lainnya</span>
                                <span><?php echo round($data['lainnya'] / $data['total'] * 100); ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-secondary" 
                                     style="width: <?php echo $data['lainnya'] / $data['total'] * 100; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Total Pekerja: <?php echo number_format($data['total'] - $data['usia_0_14']); ?> orang</h5>
                            <p class="text-muted small">*Usia produktif 15-64 tahun</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Data Grid -->
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-center mb-4">Data Detail Kependudukan</h2>
                    <div class="data-grid">
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-baby fa-2x text-primary mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['usia_0_14']); ?></div>
                            <p>Anak & Remaja (0-14)</p>
                        </div>
                        
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-user-tie fa-2x text-success mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['usia_15_64']); ?></div>
                            <p>Usia Produktif (15-64)</p>
                        </div>
                        
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-user-friends fa-2x text-warning mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['usia_65_plus']); ?></div>
                            <p>Lansia (65+)</p>
                        </div>
                        
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-tractor fa-2x text-info mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['petani']); ?></div>
                            <p>Petani</p>
                        </div>
                        
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-store fa-2x text-danger mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['wiraswasta']); ?></div>
                            <p>Wiraswasta</p>
                        </div>
                        
                        <div class="data-item animate-on-hover">
                            <i class="fas fa-user-tie fa-2x text-secondary mb-3"></i>
                            <div class="data-value"><?php echo number_format($data['pns']); ?></div>
                            <p>PNS/TNI/Polri</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RT/RW Data -->
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-12">
                    <div class="data-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>RT/RW</th>
                                        <th>Ketua</th>
                                        <th>Jumlah KK</th>
                                        <th>Perkiraan Penduduk</th>
                                        <th>Wilayah</th>
                                        <th>Kepadatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rt_rw as $data_rw): ?>
                                        <?php
                                        $estimated_population = $data_rw['jumlah_kk'] * 4;
                                        $density = round($estimated_population / 10); // Estimation
                                        ?>
                                        <tr class="animate-on-hover">
                                            <td>
                                                <strong><?php echo $data_rw['jenis']; ?> <?php echo $data_rw['nomor']; ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($data_rw['ketua']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $data_rw['jumlah_kk']; ?> KK</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $estimated_population; ?> orang</span>
                                            </td>
                                            <td><?php echo htmlspecialchars($data_rw['wilayah'] ?: '-'); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-warning" 
                                                             style="width: min(100%, <?php echo $density; ?>%)"></div>
                                                    </div>
                                                    <span><?php echo $density; ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <!-- Total Row -->
                                    <tr class="table-dark">
                                        <td><strong>TOTAL</strong></td>
                                        <td>-</td>
                                        <td><strong><?php echo number_format($total_kk); ?> KK</strong></td>
                                        <td><strong><?php echo number_format($total_penduduk); ?> orang</strong></td>
                                        <td>-</td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comparison & Trends -->
            <div class="row" data-aos="fade-up">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">
                                <i class="fas fa-chart-line me-2"></i>Trend Perkembangan Penduduk
                            </h3>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tahun</th>
                                            <th>Total Penduduk</th>
                                            <th>Laki-laki</th>
                                            <th>Perempuan</th>
                                            <th>Jumlah KK</th>
                                            <th>Pertumbuhan</th>
                                            <th>Kepadatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($demographic_data as $year => $year_data): ?>
                                            <?php
                                            $prev_year = $year - 1;
                                            $growth = isset($demographic_data[$prev_year]) 
                                                ? (($year_data['total'] - $demographic_data[$prev_year]['total']) / $demographic_data[$prev_year]['total'] * 100)
                                                : 0;
                                            $density = round($year_data['total'] / 250); // Estimation
                                            ?>
                                            <tr class="<?php echo $year == $selected_year ? 'table-primary' : ''; ?>">
                                                <td><strong><?php echo $year; ?></strong></td>
                                                <td><?php echo number_format($year_data['total']); ?></td>
                                                <td><?php echo number_format($year_data['laki_laki']); ?></td>
                                                <td><?php echo number_format($year_data['perempuan']); ?></td>
                                                <td><?php echo number_format($year_data['kk']); ?></td>
                                                <td>
                                                    <span class="<?php echo $growth >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        <i class="fas fa-arrow-<?php echo $growth >= 0 ? 'up' : 'down'; ?> me-1"></i>
                                                        <?php echo number_format(abs($growth), 2); ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                            <div class="progress-bar bg-info" 
                                                                 style="width: min(100%, <?php echo $density; ?>%)"></div>
                                                        </div>
                                                        <span><?php echo $density; ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Keterangan:</h5>
                                <ul class="text-muted">
                                    <li>Data berdasarkan sensus penduduk tahunan</li>
                                    <li>Perhitungan kepadatan berdasarkan luas wilayah 250 Ha</li>
                                    <li>Pertumbuhan dihitung dari tahun sebelumnya</li>
                                    <li>Estimasi 4 orang per Kepala Keluarga (KK)</li>
                                </ul>
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
        
        // Counter animation
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
            
            // Animate pyramid bars on scroll
            const pyramidBars = document.querySelectorAll('.bar-male, .bar-female');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const bar = entry.target;
                        const currentHeight = bar.style.height;
                        bar.style.height = '0';
                        
                        setTimeout(() => {
                            bar.style.transition = 'height 1s ease';
                            bar.style.height = currentHeight;
                        }, 100);
                        
                        observer.unobserve(bar);
                    }
                });
            }, { threshold: 0.5 });
            
            pyramidBars.forEach(bar => observer.observe(bar));
            
            // Table row hover effect
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('table-dark') && !this.classList.contains('table-primary')) {
                        this.style.transform = 'translateX(10px)';
                        this.style.transition = 'transform 0.3s ease';
                    }
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
            
            // Data item hover effect
            const dataItems = document.querySelectorAll('.data-item');
            dataItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Print functionality
            function printData() {
                const printContent = document.querySelector('main').cloneNode(true);
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Data Desa <?php echo htmlspecialchars($settings['nama_desa']); ?> - <?php echo $selected_year; ?></title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                            <style>
                                body { padding: 20px; }
                                @media print {
                                    .btn-group, .share-buttons { display: none; }
                                    .stat-card { break-inside: avoid; }
                                }
                            </style>
                        </head>
                        <body>
                            <h1>Data Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></h1>
                            <h3>Tahun <?php echo $selected_year; ?></h3>
                            <hr>
                            ${printContent.innerHTML}
                            <script>
                                window.onload = function() {
                                    window.print();
                                    window.onafterprint = function() {
                                        window.close();
                                    };
                                };
                            <\/script>
                        </body>
                    </html>
                `);
                printWindow.document.close();
            }
            
            // Add print button
            const printBtn = document.createElement('button');
            printBtn.className = 'btn btn-primary float-end mt-3';
            printBtn.innerHTML = '<i class="fas fa-print me-2"></i>Cetak Data';
            printBtn.onclick = printData;
            
            const lastCard = document.querySelector('.card:last-child .card-body');
            if (lastCard) {
                lastCard.appendChild(printBtn);
            }
        });
    </script>
</body>
</html>