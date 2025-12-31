<?php
// modules/galeri.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Cek koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    if (!isset($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Get filter parameters
$album = isset($_GET['album']) ? intval($_GET['album']) : 0;
$search = isset($_GET['cari']) ? clean_input($_GET['cari']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = $page < 1 ? 1 : $page;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Initialize variables
$settings = ['nama_desa' => 'Desa Lengkong'];
$albums = [];
$photos = [];
$total_records = 0;
$total_pages = 0;
$album_detail = null;

// 1. Ambil pengaturan
$settings_query = "SELECT * FROM pengaturan LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
}

// 2. Ambil album
$albums_query = "SELECT * FROM album ORDER BY id DESC";
$albums_result = mysqli_query($conn, $albums_query);
if ($albums_result && mysqli_num_rows($albums_result) > 0) {
    while ($row = mysqli_fetch_assoc($albums_result)) {
        $albums[] = $row;
    }
}

// 3. Cek apakah tabel galeri ada dan struktur kolomnya
$table_check = mysqli_query($conn, "SHOW COLUMNS FROM galeri LIKE 'album_id'");
$has_album_id = ($table_check && mysqli_num_rows($table_check) > 0);

// 4. Hitung total foto
$count_query = "SELECT COUNT(*) as total FROM galeri";
$where_conditions = [];

if ($album > 0 && $has_album_id) {
    $where_conditions[] = "album_id = " . intval($album);
}

if (!empty($search)) {
    $search_term = mysqli_real_escape_string($conn, $search);
    $where_conditions[] = "(judul LIKE '%$search_term%' OR deskripsi LIKE '%$search_term%')";
}

if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $where_conditions);
}

$count_result = mysqli_query($conn, $count_query);
if ($count_result && mysqli_num_rows($count_result) > 0) {
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = $count_row['total'];
}

$total_pages = ceil($total_records / $per_page);

// 5. Ambil foto dengan pagination
if ($has_album_id) {
    // Jika ada kolom album_id, gunakan JOIN
    $query = "SELECT galeri.*, album.nama as album_nama FROM galeri 
              LEFT JOIN album ON galeri.album_id = album.id";
} else {
    // Jika tidak ada kolom album_id, ambil data tanpa JOIN
    $query = "SELECT galeri.*, 'Tanpa Album' as album_nama FROM galeri";
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY galeri.id DESC LIMIT $per_page OFFSET $offset";

$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['album_nama'] = $row['album_nama'] ?? 'Tanpa Album';
        $row['deskripsi'] = $row['deskripsi'] ?? '';
        $row['foto'] = $row['foto'] ?? 'default.jpg';
        $photos[] = $row;
    }
}

// 6. Ambil detail album jika dipilih
if ($album > 0 && !empty($albums)) {
    foreach ($albums as $album_item) {
        if ($album_item['id'] == $album) {
            $album_detail = $album_item;
            break;
        }
    }
}

// 7. Hitung jumlah foto per album
$album_counts = [];
foreach ($albums as $album_item) {
    if ($has_album_id) {
        $count_query = "SELECT COUNT(*) as total FROM galeri WHERE album_id = " . intval($album_item['id']);
    } else {
        $count_query = "SELECT COUNT(*) as total FROM galeri WHERE 1=0"; // Tidak ada album_id, jadi kosong
    }
    
    $count_result = mysqli_query($conn, $count_query);
    $album_counts[$album_item['id']] = 0;
    if ($count_result && mysqli_num_rows($count_result) > 0) {
        $count_row = mysqli_fetch_assoc($count_result);
        $album_counts[$album_item['id']] = $count_row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Foto - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.2);
        }
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 120px 0;
            text-align: center;
            margin-bottom: 60px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            display: inline-block;
            padding-bottom: 15px;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 2px;
        }
        
        .album-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-decoration: none !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .album-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }
        
        .album-card:hover:before {
            opacity: 1;
        }
        
        .album-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .album-card.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        
        .album-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            color: var(--primary);
            transition: color 0.4s ease;
        }
        
        .album-card:hover .album-icon {
            color: white;
        }
        
        .album-card h5 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
            color: var(--dark);
            transition: color 0.4s ease;
        }
        
        .album-card:hover h5 {
            color: white;
        }
        
        .album-card small {
            position: relative;
            z-index: 2;
            color: #6c757d;
            transition: color 0.4s ease;
        }
        
        .album-card:hover small {
            color: rgba(255,255,255,0.9);
        }
        
        .photo-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
            cursor: pointer;
            position: relative;
        }
        
        .photo-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .photo-img-container {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .photo-card:hover .photo-img {
            transform: scale(1.1);
        }
        
        .album-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 107, 107, 0.95);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .photo-info {
            padding: 20px;
        }
        
        .photo-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            line-height: 1.4;
        }
        
        .photo-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .search-container {
            max-width: 700px;
            margin: 0 auto 50px;
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .no-photos {
            background: white;
            border-radius: 20px;
            padding: 80px 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 3px dashed #e9ecef;
        }
        
        .no-photos-icon {
            font-size: 5rem;
            color: #e9ecef;
            margin-bottom: 25px;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-color: var(--primary);
            font-weight: 600;
        }
        
        .pagination .page-link {
            border-radius: 10px;
            margin: 0 3px;
            border: none;
            color: var(--dark);
            font-weight: 500;
            padding: 10px 18px;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background-color: #f1f3ff;
            color: var(--primary);
        }
        
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #eee;
            padding: 20px 30px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-img {
            max-width: 100%;
            max-height: 60vh;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%);
            color: white;
            padding: 50px 0;
            margin-top: 100px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .photo-img-container {
                height: 200px;
            }
            
            .album-card {
                height: 180px;
                padding: 25px;
            }
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../">
                <i class="fas fa-camera-retro me-2"></i>GALERI <?php echo htmlspecialchars($settings['nama_desa']); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $album == 0 ? 'active' : ''; ?>" href="?album=0">
                            <i class="fas fa-th-large me-1"></i> Semua
                        </a>
                    </li>
                    <?php foreach ($albums as $album_item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $album == $album_item['id'] ? 'active' : ''; ?>" 
                           href="?album=<?php echo $album_item['id']; ?>">
                            <i class="fas fa-<?php echo $album_item['ikon'] ?? 'images'; ?> me-1"></i>
                            <?php echo htmlspecialchars($album_item['nama']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Galeri Foto</h1>
            <p class="hero-subtitle">Kumpulan momen berharga dan dokumentasi kegiatan <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mb-5">
        <!-- Album Selection -->
        <div class="row mb-5 fade-in">
            <div class="col-12">
                <div class="section-title">
                    <h2>Pilih Album</h2>
                    <p class="text-muted">Telusuri foto berdasarkan kategori</p>
                </div>
                <div class="row">
                    <!-- Semua Foto -->
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <a href="?album=0" class="text-decoration-none">
                            <div class="album-card <?php echo $album == 0 ? 'active' : ''; ?>">
                                <div class="album-icon">
                                    <i class="fas fa-th-large"></i>
                                </div>
                                <h5>Semua Foto</h5>
                                <small><?php echo $total_records; ?> Foto</small>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Album List -->
                    <?php foreach ($albums as $album_item): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <a href="?album=<?php echo $album_item['id']; ?>" class="text-decoration-none">
                            <div class="album-card <?php echo $album == $album_item['id'] ? 'active' : ''; ?>"
                                 style="<?php echo $album_item['warna'] ? '--primary: ' . $album_item['warna'] . '; --secondary: ' . $album_item['warna2'] . ';' : ''; ?>">
                                <div class="album-icon">
                                    <i class="fas fa-<?php echo $album_item['ikon'] ?? 'images'; ?>"></i>
                                </div>
                                <h5><?php echo htmlspecialchars($album_item['nama']); ?></h5>
                                <small><?php echo $album_counts[$album_item['id']] ?? 0; ?> Foto</small>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="row mb-5 fade-in">
            <div class="col-12">
                <div class="search-container">
                    <div class="search-box">
                        <form method="GET" action="" id="searchForm">
                            <div class="input-group input-group-lg">
                                <input type="text" name="cari" 
                                       class="form-control border-0 shadow-none" 
                                       placeholder="Cari foto berdasarkan judul atau deskripsi..." 
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       style="border-radius: 10px 0 0 10px;">
                                <?php if ($album > 0): ?>
                                <input type="hidden" name="album" value="<?php echo $album; ?>">
                                <?php endif; ?>
                                <button class="btn btn-primary" type="submit" style="border-radius: 0 10px 10px 0;">
                                    <i class="fas fa-search me-1"></i> Cari
                                </button>
                            </div>
                        </form>
                        <?php if (!empty($search) || $album > 0): ?>
                        <div class="mt-3 text-center">
                            <a href="?" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Reset Filter
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Album Info -->
        <?php if ($album_detail): ?>
        <div class="row mb-4 fade-in">
            <div class="col-12">
                <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-folder-open me-2"></i><?php echo htmlspecialchars($album_detail['nama']); ?>
                            </h4>
                            <?php if (!empty($album_detail['deskripsi'])): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($album_detail['deskripsi']); ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="?" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Photo Grid -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">
                        <?php if ($album_detail): ?>
                            <i class="fas fa-folder text-primary me-2"></i><?php echo htmlspecialchars($album_detail['nama']); ?>
                        <?php else: ?>
                            <i class="fas fa-th-large text-primary me-2"></i>Semua Foto
                        <?php endif; ?>
                    </h3>
                    <span class="badge bg-primary px-3 py-2 fs-6">
                        <i class="fas fa-image me-1"></i><?php echo $total_records; ?> Foto
                    </span>
                </div>

                <?php if (empty($photos)): ?>
                <div class="no-photos fade-in">
                    <div class="no-photos-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 class="mb-3">Tidak ada foto ditemukan</h3>
                    <p class="text-muted mb-4">
                        <?php if (!empty($search)): ?>
                            Tidak ada hasil untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php elseif ($album > 0): ?>
                            Album "<strong><?php echo htmlspecialchars($album_detail['nama'] ?? 'ini'); ?></strong>" belum memiliki foto
                        <?php else: ?>
                            Belum ada foto yang diunggah ke galeri
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search) || $album > 0): ?>
                    <a href="?" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-images me-2"></i> Lihat Semua Foto
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($photos as $index => $photo): 
                        $image_url = '../assets/uploads/galeri/' . htmlspecialchars($photo['foto']);
                        $default_image = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=250&q=80';
                        
                        // Check if file exists
                        $actual_image = (!empty($photo['foto']) && $photo['foto'] != 'default.jpg' && file_exists($image_url)) 
                            ? $image_url 
                            : $default_image;
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4 fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="photo-card h-100"
                             onclick="openPhotoModal(
                                '<?php echo $actual_image; ?>',
                                '<?php echo addslashes($photo['judul']); ?>',
                                '<?php echo addslashes($photo['deskripsi']); ?>',
                                '<?php echo htmlspecialchars($photo['album_nama']); ?>'
                             )">
                            <div class="photo-img-container">
                                <img src="<?php echo $actual_image; ?>" 
                                     alt="<?php echo htmlspecialchars($photo['judul']); ?>"
                                     class="photo-img"
                                     loading="lazy">
                                <?php if ($album == 0 && !empty($photo['album_nama']) && $photo['album_nama'] != 'Tanpa Album'): ?>
                                <span class="album-badge"><?php echo htmlspecialchars($photo['album_nama']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="photo-info">
                                <div class="photo-title"><?php echo htmlspecialchars($photo['judul']); ?></div>
                                <div class="photo-meta">
                                    <i class="fas fa-folder me-1"></i>
                                    <small><?php echo htmlspecialchars($photo['album_nama']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="row fade-in">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $album > 0 ? '&album='.$album : ''; ?><?php echo !empty($search) ? '&cari='.urlencode($search) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $album > 0 ? '&album='.$album : ''; ?><?php echo !empty($search) ? '&cari='.urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $album > 0 ? '&album='.$album : ''; ?><?php echo !empty($search) ? '&cari='.urlencode($search) : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <p class="text-center text-muted mt-3">
                        Menampilkan <?php echo count($photos); ?> dari <?php echo $total_records; ?> foto
                        • Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                    </p>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-4">
                        <img id="modalImage" src="" alt="" class="modal-img">
                    </div>
                    <p id="modalDescription" class="text-muted mb-3"></p>
                    <div id="modalAlbum" class="badge bg-primary px-3 py-2 fs-6 mb-2" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h4 class="mb-3">
                        <i class="fas fa-camera-retro me-2"></i>Galeri <?php echo htmlspecialchars($settings['nama_desa']); ?>
                    </h4>
                    <p class="mb-0 opacity-75">
                        Platform dokumentasi visual kegiatan dan keindahan desa.
                    </p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <p class="mb-0 opacity-75">
                        &copy; <?php echo date('Y'); ?> Galeri Foto Desa. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openPhotoModal(imageSrc, title, description, album) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalImage').alt = title;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            
            const albumBadge = document.getElementById('modalAlbum');
            if (album && album !== 'Tanpa Album') {
                albumBadge.textContent = 'Album: ' + album;
                albumBadge.style.display = 'inline-block';
            } else {
                albumBadge.style.display = 'none';
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('photoModal'));
            modal.show();
        }
        
        // Handle image errors
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.photo-img').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=250&q=80';
                });
            });
            
            // Smooth scroll untuk link
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Loading effect untuk form
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencari...';
                        submitBtn.disabled = true;
                    }
                });
            }
        });
        
        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, observerOptions);
        
        // Observe all fade-in elements
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>