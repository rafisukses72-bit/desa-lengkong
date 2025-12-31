<?php
// modules/potensi.php - FIXED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once '../functions.php'; // Gunakan fungsi dari functions.php

// HAPUS fungsi clean_input dari sini karena sudah ada di functions.php
// Hanya gunakan require_once functions.php

// Fungsi untuk mendapatkan pengaturan dengan fallback yang aman
function get_potensi_settings($conn) {
    // Default settings
    $default_settings = [
        'nama_desa' => 'Desa Lengkong', 
        'alamat' => 'Jl. Desa No. 1', 
        'telepon' => '08123456789',
        'logo' => 'default-logo.png',
        'deskripsi' => 'Desa yang kaya akan potensi alam dan budaya'
    ];
    
    // Cek apakah tabel settings ada
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'pengaturan'");
    if ($result && mysqli_num_rows($result) > 0) {
        $settings_result = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            return mysqli_fetch_assoc($settings_result);
        }
    }
    
    // Cek tabel settings alternatif
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
    if ($result && mysqli_num_rows($result) > 0) {
        $settings_result = mysqli_query($conn, "SELECT * FROM settings LIMIT 1");
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            return mysqli_fetch_assoc($settings_result);
        }
    }
    
    // Return default settings
    return $default_settings;
}

// Ambil pengaturan
$settings = get_potensi_settings($conn);

// Get kategori filter - gunakan clean_input dari functions.php
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';
$search = isset($_GET['cari']) ? clean_input($_GET['cari']) : '';

// Kategori potensi
$kategori_potensi = [
    'pertanian' => 'Pertanian',
    'perkebunan' => 'Perkebunan',
    'peternakan' => 'Peternakan',
    'perikanan' => 'Perikanan',
    'kerajinan' => 'Kerajinan',
    'wisata' => 'Wisata',
    'industri' => 'Industri Rumah Tangga',
    'umum' => 'Umum'
];

// Cek struktur tabel potensi untuk menentukan kolom yang tersedia
$table_columns = [];
$check_columns = @mysqli_query($conn, "SHOW COLUMNS FROM potensi");
if ($check_columns && mysqli_num_rows($check_columns) > 0) {
    while ($column = mysqli_fetch_assoc($check_columns)) {
        $table_columns[] = $column['Field'];
    }
}

// Build query dengan pengecekan kolom
$conditions = "WHERE 1=1";
$params = [];
$types = '';

// Cek apakah kolom 'status' ada
if (in_array('status', $table_columns)) {
    $conditions .= " AND status = 'aktif'";
}

// Cek apakah kolom 'kategori' ada
$has_kategori_column = in_array('kategori', $table_columns);
if ($has_kategori_column && $kategori && isset($kategori_potensi[$kategori])) {
    $conditions .= " AND kategori = ?";
    $params[] = $kategori;
    $types .= 's';
}

if ($search) {
    // Cek kolom yang ada untuk search
    $search_conditions = [];
    if (in_array('nama', $table_columns)) {
        $search_conditions[] = "nama LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }
    if (in_array('deskripsi', $table_columns)) {
        $search_conditions[] = "deskripsi LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }
    if (in_array('judul', $table_columns)) {
        $search_conditions[] = "judul LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }
    
    if (!empty($search_conditions)) {
        $conditions .= " AND (" . implode(" OR ", $search_conditions) . ")";
    }
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = $page < 1 ? 1 : $page;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Total records - Gunakan query langsung untuk menghindari error
$count_query = "SELECT COUNT(*) as total FROM potensi $conditions";
$total_records = 0;

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $count_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $count_result = mysqli_stmt_get_result($stmt);
        if ($count_result && mysqli_num_rows($count_result) > 0) {
            $count_row = mysqli_fetch_assoc($count_result);
            $total_records = $count_row['total'];
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $count_result = @mysqli_query($conn, $count_query);
    if ($count_result && mysqli_num_rows($count_result) > 0) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total_records = $count_row['total'];
    }
}

$total_pages = ceil($total_records / $per_page);
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
} elseif ($total_pages == 0) {
    $total_pages = 1;
}

// Get potensi data
$query = "SELECT * FROM potensi $conditions ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$potensi_data = [];

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $potensi_data[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $result = @mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $potensi_data[] = $row;
        }
    }
}

// Get potensi unggulan
$potensi_unggulan = [];

// Cek apakah kolom 'is_unggulan' ada
$has_unggulan_column = in_array('is_unggulan', $table_columns);

if ($has_unggulan_column) {
    $unggulan_query = "SELECT * FROM potensi WHERE status = 'aktif' AND is_unggulan = 1 ORDER BY RAND() LIMIT 4";
} else {
    // Jika tidak ada kolom is_unggulan, ambil 4 data terbaru sebagai unggulan
    $unggulan_query = "SELECT * FROM potensi ORDER BY created_at DESC LIMIT 4";
}

$unggulan_result = @mysqli_query($conn, $unggulan_query);
if ($unggulan_result && mysqli_num_rows($unggulan_result) > 0) {
    while ($row = mysqli_fetch_assoc($unggulan_result)) {
        $potensi_unggulan[] = $row;
    }
}

// Get total per kategori untuk statistik
$kategori_counts = [];
$total_all = 0;

// Hitung total semua dulu
$total_all_query = "SELECT COUNT(*) as total FROM potensi";
$total_all_result = @mysqli_query($conn, $total_all_query);
if ($total_all_result && mysqli_num_rows($total_all_result) > 0) {
    $row = mysqli_fetch_assoc($total_all_result);
    $total_all = $row['total'];
}

// Hitung per kategori hanya jika kolom kategori ada
if ($has_kategori_column) {
    foreach ($kategori_potensi as $key => $name) {
        $count_query = "SELECT COUNT(*) as total FROM potensi WHERE kategori = ?";
        if ($stmt = mysqli_prepare($conn, $count_query)) {
            mysqli_stmt_bind_param($stmt, 's', $key);
            mysqli_stmt_execute($stmt);
            $count_result = mysqli_stmt_get_result($stmt);
            
            if ($count_result && mysqli_num_rows($count_result) > 0) {
                $row = mysqli_fetch_assoc($count_result);
                $kategori_counts[$key] = $row['total'];
            } else {
                $kategori_counts[$key] = 0;
            }
            mysqli_stmt_close($stmt);
        } else {
            $kategori_counts[$key] = 0;
        }
    }
} else {
    // Jika tidak ada kolom kategori, set semua ke 0
    foreach ($kategori_potensi as $key => $name) {
        $kategori_counts[$key] = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potensi Desa - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #28a745;
            --secondary: #ffc107;
            --accent: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }
        
        .hero-potensi {
            background: linear-gradient(rgba(40, 167, 69, 0.9), rgba(40, 167, 69, 0.7));
            color: white;
            padding: 100px 0 50px;
            text-align: center;
        }
        
        .potensi-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            background: white;
            margin-bottom: 20px;
        }
        
        .potensi-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .potensi-img {
            height: 200px;
            overflow: hidden;
        }
        
        .potensi-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .potensi-card:hover .potensi-img img {
            transform: scale(1.1);
        }
        
        .badge-potensi {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .unggulan-card {
            border: 3px solid var(--secondary);
            position: relative;
        }
        
        .unggulan-label {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--secondary);
            color: var(--dark);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.8rem;
            z-index: 2;
        }
        
        .category-btn {
            border-radius: 25px;
            padding: 8px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .category-btn:hover {
            transform: translateY(-3px);
        }
        
        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-box input {
            padding: 12px 50px 12px 20px;
            border-radius: 30px;
            border: 2px solid #ddd;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
            outline: none;
        }
        
        .search-box button {
            position: absolute;
            right: 10px;
            top: 10px;
            bottom: 10px;
            width: 40px;
            border-radius: 50%;
            border: none;
            background: var(--primary);
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .search-box button:hover {
            background: var(--dark);
        }
        
        .pagination .page-link {
            border: none;
            margin: 0 3px;
            border-radius: 5px;
            transition: all 0.3s ease;
            color: var(--dark);
            font-weight: 500;
        }
        
        .pagination .page-link:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .pagination .active .page-link {
            background: var(--primary);
            color: white;
        }
        
        .sidebar-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-card .card-header {
            background: var(--primary);
            color: white;
            border: none;
        }
        
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .category-list li {
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .category-list li:last-child {
            border-bottom: none;
        }
        
        .category-list a {
            text-decoration: none;
            color: var(--dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-count {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px dashed #dee2e6;
        }
        
        @media (max-width: 768px) {
            .hero-potensi {
                padding: 80px 0 40px;
            }
            
            .potensi-img {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Simple header jika tidak ada file header
    if (!file_exists('../includes/header.php')): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-home me-2"></i>
                <?php echo htmlspecialchars($settings['nama_desa']); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="berita.php">Berita</a></li>
                    <li class="nav-item"><a class="nav-link active" href="potensi.php">Potensi</a></li>
                    <li class="nav-item"><a class="nav-link" href="galeri.php">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <?php else: ?>
        <?php include '../includes/header.php'; ?>
    <?php endif; ?>
    
    <!-- Hero Section -->
    <section class="hero-potensi" style="margin-top: 80px;">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Potensi Desa</h1>
            <p class="lead">Menggali kekayaan alam dan budaya <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Potensi Unggulan -->
            <?php if (!empty($potensi_unggulan)): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="text-center mb-4">
                        <i class="fas fa-star text-warning me-2"></i>Potensi Unggulan
                    </h2>
                    <div class="row">
                        <?php foreach ($potensi_unggulan as $item): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="potensi-card unggulan-card">
                                <span class="unggulan-label">
                                    <i class="fas fa-crown me-1"></i> Unggulan
                                </span>
                                <div class="potensi-img">
                                    <?php if (!empty($item['gambar'])): ?>
                                        <img src="../assets/uploads/potensi/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nama'] ?? $item['judul'] ?? 'Potensi'); ?>"
                                             onerror="this.onerror=null; this.src='../assets/images/default-image.jpg';">
                                    <?php else: ?>
                                        <img src="../assets/images/default-image.jpg" 
                                             alt="Potensi Desa">
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if ($has_kategori_column && isset($item['kategori'])): ?>
                                    <span class="badge-potensi mb-2">
                                        <?php echo htmlspecialchars($kategori_potensi[$item['kategori']] ?? 'Umum'); ?>
                                    </span>
                                    <?php endif; ?>
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['nama'] ?? $item['judul'] ?? 'Potensi'); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php 
                                        $deskripsi = strip_tags($item['deskripsi'] ?? $item['konten'] ?? '');
                                        echo strlen($deskripsi) > 80 ? substr($deskripsi, 0, 80) . '...' : $deskripsi;
                                        ?>
                                    </p>
                                    <a href="detail-potensi.php?id=<?php echo $item['id']; ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="fas fa-eye me-2"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Kategori Filter (hanya tampil jika ada kolom kategori) -->
            <?php if ($has_kategori_column): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="text-center">
                        <h3 class="mb-4">
                            <i class="fas fa-filter text-primary me-2"></i>Filter Berdasarkan Kategori
                        </h3>
                        <div class="d-flex flex-wrap justify-content-center">
                            <a href="?" class="btn <?php echo !$kategori ? 'btn-primary' : 'btn-outline-primary'; ?> category-btn">
                                <i class="fas fa-layer-group me-2"></i>Semua Potensi
                                <span class="badge bg-light text-dark ms-2"><?php echo $total_all; ?></span>
                            </a>
                            <?php foreach ($kategori_potensi as $key => $name): ?>
                            <a href="?kategori=<?php echo urlencode($key); ?>" 
                               class="btn <?php echo $kategori === $key ? 'btn-primary' : 'btn-outline-primary'; ?> category-btn">
                                <i class="fas fa-folder me-2"></i><?php echo htmlspecialchars($name); ?>
                                <span class="badge bg-light text-dark ms-2"><?php echo $kategori_counts[$key] ?? 0; ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Search Box -->
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto">
                    <div class="search-box">
                        <form method="GET" action="">
                            <input type="text" 
                                   name="cari" 
                                   class="form-control" 
                                   placeholder="Cari potensi..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Results Info -->
            <?php if ($search || ($has_kategori_column && $kategori)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-primary">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-2 mb-md-0">
                                <i class="fas fa-search me-2"></i>
                                <?php if ($search && $has_kategori_column && $kategori): ?>
                                    Menampilkan hasil pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                    <?php if (isset($kategori_potensi[$kategori])): ?>
                                    dalam kategori <strong><?php echo htmlspecialchars($kategori_potensi[$kategori]); ?></strong>
                                    <?php endif; ?>
                                <?php elseif ($search): ?>
                                    Menampilkan hasil pencarian untuk "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                <?php elseif ($has_kategori_column && $kategori): ?>
                                    Menampilkan potensi dalam kategori <strong><?php echo htmlspecialchars($kategori_potensi[$kategori] ?? $kategori); ?></strong>
                                <?php endif; ?>
                                <span class="badge bg-dark ms-2"><?php echo $total_records; ?> hasil</span>
                            </div>
                            <a href="?" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-times me-1"></i> Hapus Filter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-9">
                    <?php if (empty($potensi_data)): ?>
                    <div class="empty-state">
                        <i class="fas fa-seedling fa-4x text-muted mb-3"></i>
                        <h3 class="mb-3">Tidak ada potensi ditemukan</h3>
                        <p class="text-muted mb-4">
                            <?php if ($search): ?>
                                Tidak ada potensi yang sesuai dengan pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php elseif ($has_kategori_column && $kategori): ?>
                                Tidak ada potensi dalam kategori "<strong><?php echo htmlspecialchars($kategori_potensi[$kategori] ?? $kategori); ?></strong>"
                            <?php else: ?>
                                Belum ada potensi yang ditambahkan
                            <?php endif; ?>
                        </p>
                        <a href="?" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i> Lihat Semua Potensi
                        </a>
                    </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($potensi_data as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="potensi-card">
                                    <div class="potensi-img">
                                        <?php if (!empty($item['gambar'])): ?>
                                            <img src="../assets/uploads/potensi/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nama'] ?? $item['judul'] ?? 'Potensi'); ?>"
                                                 onerror="this.onerror=null; this.src='../assets/images/default-image.jpg';">
                                        <?php else: ?>
                                            <img src="../assets/images/default-image.jpg" 
                                                 alt="Potensi Desa">
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($has_kategori_column && isset($item['kategori'])): ?>
                                        <span class="badge-potensi mb-2">
                                            <?php echo htmlspecialchars($kategori_potensi[$item['kategori']] ?? 'Umum'); ?>
                                        </span>
                                        <?php endif; ?>
                                        <h5 class="card-title"><?php echo htmlspecialchars($item['nama'] ?? $item['judul'] ?? 'Potensi'); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php 
                                            $deskripsi = strip_tags($item['deskripsi'] ?? $item['konten'] ?? '');
                                            echo strlen($deskripsi) > 100 ? substr($deskripsi, 0, 100) . '...' : $deskripsi;
                                            ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if (isset($item['created_at'])): ?>
                                            <small class="text-muted">
                                                <i class="far fa-calendar me-1"></i>
                                                <?php echo format_date($item['created_at']); ?>
                                            </small>
                                            <?php endif; ?>
                                            <a href="detail-potensi.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Detail <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?page=<?php echo $page-1; ?><?php echo $has_kategori_column && $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $search ? '&cari='.urlencode($search) : ''; ?>"
                                   aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <!-- Page Numbers -->
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=1<?php echo $has_kategori_column && $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $search ? '&cari='.urlencode($search) : ''; ?>">
                                        1
                                    </a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" 
                                   href="?page=<?php echo $i; ?><?php echo $has_kategori_column && $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $search ? '&cari='.urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=<?php echo $total_pages; ?><?php echo $has_kategori_column && $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $search ? '&cari='.urlencode($search) : ''; ?>">
                                        <?php echo $total_pages; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Next Page -->
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?page=<?php echo $page+1; ?><?php echo $has_kategori_column && $kategori ? '&kategori='.urlencode($kategori) : ''; ?><?php echo $search ? '&cari='.urlencode($search) : ''; ?>"
                                   aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <!-- Page Info -->
                        <div class="text-center mt-3 text-muted">
                            <small>
                                Menampilkan <?php echo count($potensi_data); ?> dari <?php echo $total_records; ?> potensi
                                (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)
                            </small>
                        </div>
                    </nav>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <!-- Statistics -->
                    <div class="sidebar-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Statistik Potensi
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <h2 class="text-primary"><?php echo $total_all; ?></h2>
                                <p class="text-muted mb-0">Total Potensi</p>
                            </div>
                            
                            <?php if ($has_kategori_column): ?>
                            <h6 class="mb-3">
                                <i class="fas fa-list me-2 text-primary"></i>Kategori
                            </h6>
                            <ul class="category-list">
                                <li>
                                    <a href="?">
                                        <span><i class="fas fa-layer-group me-2"></i>Semua Potensi</span>
                                        <span class="category-count"><?php echo $total_all; ?></span>
                                    </a>
                                </li>
                                <?php foreach ($kategori_potensi as $key => $name): ?>
                                <li>
                                    <a href="?kategori=<?php echo urlencode($key); ?>">
                                        <span><i class="fas fa-folder me-2"></i><?php echo htmlspecialchars($name); ?></span>
                                        <span class="category-count"><?php echo $kategori_counts[$key] ?? 0; ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Info Panel -->
                    <div class="sidebar-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-lightbulb fa-3x mb-3"></i>
                            <h5>Punya Potensi?</h5>
                            <p class="mb-3">Jika Anda memiliki potensi desa yang ingin ditampilkan, hubungi kami</p>
                            <?php if (isset($settings['telepon'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($settings['telepon']); ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-phone me-1"></i> Hubungi Kami
                            </a>
                            <?php else: ?>
                            <a href="kontak.php" class="btn btn-light btn-sm">
                                <i class="fas fa-info-circle me-1"></i> Hubungi Kami
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php 
    // Simple footer jika tidak ada file footer
    if (!file_exists('../includes/footer.php')): ?>
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo htmlspecialchars($settings['nama_desa']); ?></h5>
                    <p><?php echo htmlspecialchars($settings['alamat']); ?></p>
                    <?php if (isset($settings['telepon'])): ?>
                    <p><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($settings['telepon']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['nama_desa']); ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php else: ?>
        <?php include '../includes/footer.php'; ?>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi dan interaksi
        document.addEventListener('DOMContentLoaded', function() {
            // Animasi hover untuk kartu potensi
            const potensiCards = document.querySelectorAll('.potensi-card');
            potensiCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const image = this.querySelector('.potensi-img img');
                    if (image) {
                        image.style.transform = 'scale(1.1)';
                    }
                    this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
                });
                
                card.addEventListener('mouseleave', function() {
                    const image = this.querySelector('.potensi-img img');
                    if (image) {
                        image.style.transform = 'scale(1)';
                    }
                    this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                });
            });
            
            // Animasi untuk kategori button
            const categoryButtons = document.querySelectorAll('.category-btn');
            categoryButtons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Image error handling
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.src = '../assets/images/default-image.jpg';
                    this.alt = 'Gambar tidak tersedia';
                });
            });
        });
    </script>
</body>
</html>