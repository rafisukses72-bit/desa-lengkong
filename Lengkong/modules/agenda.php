<?php
// modules/agenda.php
require_once '../config.php';
require_once '../functions.php';

$settings = get_settings();

// Get filter parameters
$bulan = isset($_GET['bulan']) ? clean_input($_GET['bulan']) : date('Y-m');
$jenis = isset($_GET['jenis']) ? clean_input($_GET['jenis']) : '';

// Get events for selected month
$tanggal_mulai = $bulan . '-01';
$tanggal_akhir = date('Y-m-t', strtotime($tanggal_mulai));

$conditions = "WHERE tanggal_mulai BETWEEN ? AND ?";
$params = [$tanggal_mulai, $tanggal_akhir];

if ($jenis) {
    $conditions .= " AND jenis = ?";
    $params[] = $jenis;
}

$query = "SELECT * FROM agenda $conditions ORDER BY tanggal_mulai, jam_mulai ASC";
$stmt = mysqli_prepare($conn, $query);
$types = str_repeat('s', count($params));
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$events = [];
$events_by_date = [];

while ($row = mysqli_fetch_assoc($result)) {
    $events[] = $row;
    
    $date = date('Y-m-d', strtotime($row['tanggal_mulai']));
    if (!isset($events_by_date[$date])) {
        $events_by_date[$date] = [];
    }
    $events_by_date[$date][] = $row;
}

// Get event types for filter
$jenis_options = [
    '' => 'Semua Jenis',
    'rapat' => 'Rapat',
    'posyandu' => 'Posyandu',
    'penyuluhan' => 'Penyuluhan',
    'kegiatan' => 'Kegiatan',
    'lainnya' => 'Lainnya'
];

// Get upcoming events for sidebar
$today = date('Y-m-d');
$upcoming_query = "SELECT * FROM agenda WHERE tanggal_mulai >= ? ORDER BY tanggal_mulai ASC LIMIT 5";
$upcoming_stmt = mysqli_prepare($conn, $upcoming_query);
mysqli_stmt_bind_param($upcoming_stmt, 's', $today);
mysqli_stmt_execute($upcoming_stmt);
$upcoming_result = mysqli_stmt_get_result($upcoming_stmt);
$upcoming_events = [];
while ($row = mysqli_fetch_assoc($upcoming_result)) {
    $upcoming_events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        .agenda-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('../assets/images/agenda-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .calendar-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .calendar-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #eee;
        }
        
        .calendar-day {
            background: white;
            padding: 15px 5px;
            text-align: center;
            min-height: 100px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .calendar-day:hover {
            background: #f8f9fa;
            transform: scale(1.02);
            z-index: 1;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .day-header {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .day-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .today .day-number {
            background: var(--secondary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .other-month {
            background: #f8f9fa;
            color: #999;
        }
        
        .event-dot {
            width: 8px;
            height: 8px;
            background: var(--secondary-color);
            border-radius: 50%;
            margin: 2px auto;
        }
        
        .event-popup {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 15px;
            width: 250px;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .calendar-day:hover .event-popup {
            opacity: 1;
            visibility: visible;
            bottom: calc(100% + 10px);
        }
        
        .event-item {
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .event-time {
            font-size: 0.8rem;
            color: var(--secondary-color);
            font-weight: bold;
        }
        
        .filter-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .agenda-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .agenda-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .agenda-date {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .agenda-day {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1;
        }
        
        .agenda-month {
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .agenda-content {
            padding: 20px;
        }
        
        .event-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .type-rapat { background: #ff6b6b; color: white; }
        .type-posyandu { background: #1dd1a1; color: white; }
        .type-penyuluhan { background: #54a0ff; color: white; }
        .type-kegiatan { background: #f368e0; color: white; }
        .type-lainnya { background: #ff9f43; color: white; }
        
        @media (max-width: 768px) {
            .calendar-grid {
                grid-template-columns: repeat(7, 1fr);
            }
            
            .calendar-day {
                padding: 10px 2px;
                min-height: 80px;
                font-size: 0.9rem;
            }
            
            .day-number {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="agenda-hero" data-aos="fade-down">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Agenda Desa</h1>
            <p class="lead">Kalender kegiatan dan acara Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-box" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bulan</label>
                            <input type="month" name="bulan" class="form-control" 
                                   value="<?php echo htmlspecialchars($bulan); ?>" 
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kegiatan</label>
                            <select name="jenis" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($jenis_options as $key => $value): ?>
                                    <option value="<?php echo $key; ?>" 
                                        <?php echo $jenis === $key ? 'selected' : ''; ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filter Agenda
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="row">
                <!-- Calendar View -->
                <div class="col-lg-8 mb-5">
                    <div class="calendar-container" data-aos="fade-right">
                        <div class="calendar-header">
                            <h3 class="mb-0">
                                <?php echo date('F Y', strtotime($bulan . '-01')); ?>
                            </h3>
                            <div class="mt-2">
                                <a href="?bulan=<?php echo date('Y-m', strtotime($bulan . ' -1 month')); ?><?php echo $jenis ? '&jenis=' . $jenis : ''; ?>" 
                                   class="btn btn-sm btn-light me-2">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="?bulan=<?php echo date('Y-m'); ?><?php echo $jenis ? '&jenis=' . $jenis : ''; ?>" 
                                   class="btn btn-sm btn-light me-2">
                                    Hari Ini
                                </a>
                                <a href="?bulan=<?php echo date('Y-m', strtotime($bulan . ' +1 month')); ?><?php echo $jenis ? '&jenis=' . $jenis : ''; ?>" 
                                   class="btn btn-sm btn-light">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <?php
                        // Generate calendar
                        $first_day = date('w', strtotime($tanggal_mulai));
                        $last_day = date('t', strtotime($tanggal_mulai));
                        $today = date('Y-m-d');
                        ?>
                        
                        <div class="calendar-grid">
                            <!-- Day headers -->
                            <div class="day-header">Minggu</div>
                            <div class="day-header">Senin</div>
                            <div class="day-header">Selasa</div>
                            <div class="day-header">Rabu</div>
                            <div class="day-header">Kamis</div>
                            <div class="day-header">Jumat</div>
                            <div class="day-header">Sabtu</div>
                            
                            <!-- Empty days before first day -->
                            <?php for ($i = 0; $i < $first_day; $i++): ?>
                                <div class="calendar-day other-month"></div>
                            <?php endfor; ?>
                            
                            <!-- Days of the month -->
                            <?php for ($day = 1; $day <= $last_day; $day++): ?>
                                <?php
                                $current_date = date('Y-m-d', strtotime($bulan . '-' . sprintf('%02d', $day)));
                                $is_today = $current_date === $today;
                                $has_events = isset($events_by_date[$current_date]);
                                ?>
                                
                                <div class="calendar-day <?php echo $is_today ? 'today' : ''; ?> animate-on-hover">
                                    <div class="day-number"><?php echo $day; ?></div>
                                    
                                    <?php if ($has_events): ?>
                                        <div class="event-dot"></div>
                                        <div class="event-popup">
                                            <h6>Agenda <?php echo date('d/m/Y', strtotime($current_date)); ?></h6>
                                            <?php foreach ($events_by_date[$current_date] as $event): ?>
                                                <div class="event-item">
                                                    <div class="event-time">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('H:i', strtotime($event['jam_mulai'])); ?>
                                                        <?php if ($event['jam_selesai']): ?>
                                                            - <?php echo date('H:i', strtotime($event['jam_selesai'])); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="event-title">
                                                        <?php echo htmlspecialchars($event['judul']); ?>
                                                    </div>
                                                    <div class="event-location small text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($event['lokasi']); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (($first_day + $day) % 7 == 0 && $day != $last_day): ?>
                                    </div><div class="calendar-grid">
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <!-- Empty days after last day -->
                            <?php 
                            $last_day_of_week = ($first_day + $last_day - 1) % 7;
                            for ($i = $last_day_of_week + 1; $i < 7; $i++):
                            ?>
                                <div class="calendar-day other-month"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Events & Sidebar -->
                <div class="col-lg-4">
                    <!-- Upcoming Events -->
                    <div class="mb-5" data-aos="fade-left">
                        <h4 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Agenda Mendatang</h4>
                        
                        <?php if (empty($upcoming_events)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Tidak ada agenda mendatang.
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="agenda-card animate-on-hover">
                                    <div class="event-type-badge type-<?php echo $event['jenis']; ?>">
                                        <?php echo ucfirst($event['jenis']); ?>
                                    </div>
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <div class="agenda-date">
                                                <div class="agenda-day">
                                                    <?php echo date('d', strtotime($event['tanggal_mulai'])); ?>
                                                </div>
                                                <div class="agenda-month">
                                                    <?php echo date('M', strtotime($event['tanggal_mulai'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="agenda-content">
                                                <h6 class="mb-2"><?php echo htmlspecialchars($event['judul']); ?></h6>
                                                <p class="text-muted small mb-1">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php echo date('H:i', strtotime($event['jam_mulai'])); ?>
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($event['lokasi']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Event Types -->
                    <div class="card mb-5" data-aos="fade-left" data-aos-delay="100">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-tags me-2"></i>Jenis Kegiatan</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($jenis_options as $key => $value): ?>
                                    <?php if ($key): ?>
                                        <a href="?jenis=<?php echo $key; ?>&bulan=<?php echo $bulan; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <?php echo $value; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="card" data-aos="fade-left" data-aos-delay="200">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-chart-bar me-2"></i>Statistik Bulan Ini</h5>
                            <ul class="list-unstyled">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span>Total Agenda</span>
                                    <span class="badge bg-primary"><?php echo count($events); ?></span>
                                </li>
                                <?php foreach ($jenis_options as $key => $value): ?>
                                    <?php if ($key): ?>
                                        <?php 
                                        $count = 0;
                                        foreach ($events as $event) {
                                            if ($event['jenis'] === $key) {
                                                $count++;
                                            }
                                        }
                                        if ($count > 0):
                                        ?>
                                            <li class="d-flex justify-content-between py-2 border-bottom">
                                                <span><?php echo $value; ?></span>
                                                <span class="badge bg-secondary"><?php echo $count; ?></span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Events List -->
            <div class="row mt-5" data-aos="fade-up">
                <div class="col-12">
                    <h2 class="text-center mb-5">Daftar Agenda Bulan <?php echo date('F Y', strtotime($bulan . '-01')); ?></h2>
                    
                    <?php if (empty($events)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-4"></i>
                            <h3>Tidak ada agenda untuk bulan ini</h3>
                            <p class="text-muted">Coba filter bulan atau jenis kegiatan yang berbeda</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Agenda</th>
                                        <th>Lokasi</th>
                                        <th>Jenis</th>
                                        <th>Peserta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr class="animate-on-hover">
                                            <td>
                                                <strong><?php echo date('d/m/Y', strtotime($event['tanggal_mulai'])); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('H:i', strtotime($event['jam_mulai'])); ?>
                                                <?php if ($event['jam_selesai']): ?>
                                                    - <?php echo date('H:i', strtotime($event['jam_selesai'])); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($event['judul']); ?></strong>
                                                <?php if ($event['deskripsi']): ?>
                                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($event['deskripsi']); ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['lokasi']); ?></td>
                                            <td>
                                                <span class="badge type-<?php echo $event['jenis']; ?>">
                                                    <?php echo ucfirst($event['jenis']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($event['peserta']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($event['peserta']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
        
        // Calendar hover effects
        document.addEventListener('DOMContentLoaded', function() {
            // Calendar day hover
            const calendarDays = document.querySelectorAll('.calendar-day');
            calendarDays.forEach(day => {
                day.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });
                
                day.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Event card hover
            const eventCards = document.querySelectorAll('.agenda-card');
            eventCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Table row hover
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
            
            // Filter form animation
            const filterForm = document.querySelector('form');
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
            
            // Print calendar function
            function printCalendar() {
                const printContent = document.querySelector('.calendar-container').cloneNode(true);
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Kalender Agenda - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                            <style>
                                body { padding: 20px; }
                                .calendar-container { max-width: 100%; }
                                @media print {
                                    .calendar-day { break-inside: avoid; }
                                }
                            </style>
                        </head>
                        <body>
                            <h1>Kalender Agenda <?php echo date('F Y', strtotime($bulan . '-01')); ?></h1>
                            <p>Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
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
            printBtn.className = 'btn btn-outline-secondary';
            printBtn.innerHTML = '<i class="fas fa-print me-2"></i>Cetak Kalender';
            printBtn.onclick = printCalendar;
            
            const headerActions = document.querySelector('.calendar-header');
            if (headerActions) {
                headerActions.appendChild(printBtn);
            }
        });
    </script>
</body>
</html>