<?php
// modules/detail-potensi.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Get ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: potensi.php');
    exit();
}

// Get potensi detail
$query = "SELECT p.*, COUNT(pp.id) as total_foto 
          FROM potensi p 
          LEFT JOIN potensi_foto pp ON p.id = pp.potensi_id 
          WHERE p.id = $id AND p.status = 'aktif'";

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: potensi.php');
    exit();
}

$potensi = mysqli_fetch_assoc($result);

// Get related potensi
$related_query = "SELECT * FROM potensi 
                  WHERE kategori = '{$potensi['kategori']}' 
                  AND id != $id 
                  AND status = 'aktif' 
                  ORDER BY RAND() LIMIT 3";
$related_result = mysqli_query($conn, $related_query);
$related_potensi = [];
if ($related_result && mysqli_num_rows($related_result) > 0) {
    while ($row = mysqli_fetch_assoc($related_result)) {
        $related_potensi[] = $row;
    }
}

// Get photos
$photos_query = "SELECT * FROM potensi_foto WHERE potensi_id = $id ORDER BY is_utama DESC";
$photos_result = mysqli_query($conn, $photos_query);
$photos = [];
if ($photos_result && mysqli_num_rows($photos_result) > 0) {
    while ($row = mysqli_fetch_assoc($photos_result)) {
        $photos[] = $row;
    }
}

// Update views
mysqli_query($conn, "UPDATE potensi SET views = views + 1 WHERE id = $id");

// Ambil pengaturan
$settings = [];
$settings_query = "SELECT * FROM pengaturan LIMIT 1";
$settings_result = mysqli_query($conn, $settings_query);
if ($settings_result && mysqli_num_rows($settings_result) > 0) {
    $settings = mysqli_fetch_assoc($settings_result);
}

// Kategori mapping
$kategori_potensi = [
    'pertanian' => 'Pertanian',
    'perkebunan' => 'Perkebunan',
    'peternakan' => 'Peternakan',
    'perikanan' => 'Perikanan',
    'kerajinan' => 'Kerajinan',
    'wisata' => 'Wisata',
    'industri' => 'Industri Rumah Tangga'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($potensi['nama']); ?> - Potensi Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .detail-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('../assets/uploads/potensi/<?php echo htmlspecialchars($potensi['gambar'] ?? ''); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .detail-img {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-detail {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 1rem;
        }
        .gallery-item {
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .modal-img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="detail-hero">
        <div class="container">
            <span class="badge-detail mb-3"><?php echo $kategori_potensi[$potensi['kategori']] ?? 'Umum'; ?></span>
            <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($potensi['nama']); ?></h1>
            <p class="lead">Potensi Unggulan Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <!-- Left Content -->
                <div class="col-lg-8">
                    <!-- Main Image -->
                    <div class="detail-img mb-4">
                        <?php if (!empty($potensi['gambar'])): ?>
                        <img src="../assets/uploads/potensi/<?php echo htmlspecialchars($potensi['gambar']); ?>" 
                             alt="<?php echo htmlspecialchars($potensi['nama']); ?>"
                             class="w-100"
                             onerror="this.src='https://via.placeholder.com/800x400?text=Potensi+Desa'">
                        <?php else: ?>
                        <img src="https://via.placeholder.com/800x400?text=Potensi+Desa" 
                             alt="Potensi" class="w-100">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-card text-center">
                                <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                                <h4><?php echo $potensi['views'] ?? 0; ?></h4>
                                <p class="text-muted mb-0">Dilihat</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card text-center">
                                <i class="fas fa-calendar-alt fa-2x text-success mb-2"></i>
                                <h4><?php echo date('d M Y', strtotime($potensi['created_at'])); ?></h4>
                                <p class="text-muted mb-0">Tanggal Dibuat</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-card text-center">
                                <i class="fas fa-images fa-2x text-warning mb-2"></i>
                                <h4><?php echo count($photos); ?></h4>
                                <p class="text-muted mb-0">Foto</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-5">
                        <h3 class="mb-4">Deskripsi</h3>
                        <div class="content">
                            <?php echo nl2br(htmlspecialchars($potensi['deskripsi'])); ?>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <?php if (!empty($potensi['lokasi'])): ?>
                    <div class="mb-5">
                        <h3 class="mb-4"><i class="fas fa-map-marker-alt text-danger me-2"></i>Lokasi</h3>
                        <p class="lead"><?php echo htmlspecialchars($potensi['lokasi']); ?></p>
                        <?php if (!empty($potensi['maps_link'])): ?>
                        <a href="<?php echo htmlspecialchars($potensi['maps_link']); ?>" 
                           target="_blank" class="btn btn-primary">
                            <i class="fas fa-map me-2"></i>Lihat di Google Maps
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gallery -->
                    <?php if (!empty($photos)): ?>
                    <div class="mb-5">
                        <h3 class="mb-4"><i class="fas fa-images text-info me-2"></i>Galeri Foto</h3>
                        <div class="row">
                            <?php foreach ($photos as $photo): ?>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal"
                                     onclick="openGalleryModal('<?php echo htmlspecialchars($photo['foto']); ?>', '<?php echo htmlspecialchars($photo['keterangan']); ?>')">
                                    <img src="../assets/uploads/potensi/foto/<?php echo htmlspecialchars($photo['foto']); ?>" 
                                         alt="<?php echo htmlspecialchars($photo['keterangan']); ?>"
                                         class="w-100"
                                         style="height: 200px; object-fit: cover;"
                                         onerror="this.src='https://via.placeholder.com/300x200?text=Foto'">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Related Potensi -->
                    <?php if (!empty($related_potensi)): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-link me-2"></i>Potensi Terkait</h5>
                            <?php foreach ($related_potensi as $item): ?>
                            <a href="detail-potensi.php?id=<?php echo $item['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <div class="d-flex align-items-center mb-3 p-2 border rounded hover-shadow">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($item['gambar'])): ?>
                                        <img src="../assets/uploads/potensi/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                             class="rounded"
                                             style="width: 70px; height: 70px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/70x70?text=Potensi'">
                                        <?php else: ?>
                                        <img src="https://via.placeholder.com/70x70?text=Potensi" 
                                             alt="Potensi" class="rounded">
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['nama']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo $kategori_potensi[$item['kategori']] ?? 'Umum'; ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contact Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi Kontak</h5>
                            <p class="mb-2">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <?php echo htmlspecialchars($settings['telepon'] ?? 'Belum diatur'); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <?php echo htmlspecialchars($settings['email'] ?? 'desa@example.com'); ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?php echo htmlspecialchars($settings['alamat'] ?? 'Jl. Desa No. 1'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Share -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-share-alt me-2"></i>Bagikan</h5>
                            <div class="d-flex justify-content-around">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($potensi['nama']); ?>" 
                                   target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($potensi['nama'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="btn btn-outline-success btn-sm">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="modal-img">
                    <p id="modalCaption" class="mt-3"></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openGalleryModal(imageSrc, caption) {
            document.getElementById('modalImage').src = '../assets/uploads/potensi/foto/' + imageSrc;
            document.getElementById('modalImage').alt = caption;
            document.getElementById('modalCaption').textContent = caption;
            document.getElementById('galleryModalLabel').textContent = caption;
        }
        
        // Error handling for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    if (!this.src.includes('via.placeholder.com')) {
                        this.src = 'https://via.placeholder.com/300x200?text=Gambar+Tidak+Tersedia';
                    }
                });
            });
        });
    </script>
</body>
</html>