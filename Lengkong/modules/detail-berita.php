<?php
// modules/detail-berita.php
require_once '../config.php';
require_once '../functions.php';


// Get BASE_URL from config
$base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$base_url .= $_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);


$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (!$slug) {
    header('Location: berita.php');
    exit();
}

// Get news details
$query = "SELECT b.*, u.nama_lengkap as penulis_nama 
          FROM berita b 
          LEFT JOIN users u ON b.penulis = u.id 
          WHERE b.slug = ? AND b.status = 'published'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

if (!$news) {
    header('Location: berita.php');
    exit();
}

// Increment views
if (isset($news['id'])) {
    $update_query = "UPDATE berita SET views = COALESCE(views, 0) + 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'i', $news['id']);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
}

$settings = get_settings();

// Get related news
$related_news = [];
if (!empty($news['kategori'])) {
    $related_query = "
        SELECT * FROM berita 
        WHERE kategori = ? AND id != ? AND status = 'published' 
        ORDER BY created_at DESC 
        LIMIT 3
    ";
    $related_stmt = mysqli_prepare($conn, $related_query);
    mysqli_stmt_bind_param($related_stmt, 'si', $news['kategori'], $news['id']);
    mysqli_stmt_execute($related_stmt);
    $related_result = mysqli_stmt_get_result($related_stmt);
    while ($row = mysqli_fetch_assoc($related_result)) {
        $related_news[] = $row;
    }
    if (isset($related_stmt)) mysqli_stmt_close($related_stmt);
}

// Get recent news for sidebar
$recent_news = [];
$recent_query = "SELECT * FROM berita WHERE status = 'published' AND id != ? ORDER BY created_at DESC LIMIT 5";
$recent_stmt = mysqli_prepare($conn, $recent_query);
mysqli_stmt_bind_param($recent_stmt, 'i', $news['id']);
mysqli_stmt_execute($recent_stmt);
$recent_result = mysqli_stmt_get_result($recent_stmt);
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_news[] = $row;
}
if (isset($recent_stmt)) mysqli_stmt_close($recent_stmt);

// Get categories
$categories = [];
$cat_query = "SELECT DISTINCT kategori FROM berita WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result && mysqli_num_rows($cat_result) > 0) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[$row['kategori']] = $row['kategori'];
    }
}

// Get category count
$category_counts = [];
foreach ($categories as $key => $name) {
    $count_query = "SELECT COUNT(*) as total FROM berita WHERE kategori = ? AND status = 'published'";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, 's', $key);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count = mysqli_fetch_assoc($count_result)['total'];
    $category_counts[$key] = $count;
    if (isset($count_stmt)) mysqli_stmt_close($count_stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['judul']); ?> - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    
    <?php 
    $description = !empty($news['konten']) ? substr(strip_tags($news['konten']), 0, 160) : '';
    $image_url = !empty($news['gambar']) ? BASE_URL . 'assets/uploads/berita/' . htmlspecialchars($news['gambar']) : BASE_URL . 'assets/images/default-news.jpg';
    $page_url = BASE_URL . 'modules/detail-berita.php?slug=' . $slug;
    ?>
    
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($news['judul']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo $image_url; ?>">
    <meta property="og:url" content="<?php echo $page_url; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($news['judul']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo $image_url; ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/animasi.css">
    
    <style>
        :root {
            --primary-color: <?php echo $settings['theme_color'] ?: '#2c3e50'; ?>;
            --secondary-color: #3498db;
        }
        
        .detail-hero {
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), 
                        url('<?php echo !empty($news['gambar']) ? '../assets/uploads/berita/' . htmlspecialchars($news['gambar']) : '../assets/images/default-banner.jpg'; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0;
            text-align: center;
            position: relative;
            margin-top: -20px;
        }
        
        .detail-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(0,0,0,0.5));
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .news-meta {
            margin: 20px 0;
        }
        
        .meta-item {
            display: inline-block;
            margin: 0 15px;
        }
        
        .meta-item i {
            margin-right: 8px;
            color: var(--secondary-color);
        }
        
        .content-container {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .content-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }
        
        .content-container img.zoomed {
            cursor: zoom-out;
        }
        
        .content-container p {
            margin-bottom: 1.5rem;
        }
        
        .content-container h2, 
        .content-container h3, 
        .content-container h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .content-container blockquote {
            border-left: 4px solid var(--primary-color);
            padding-left: 20px;
            margin: 30px 0;
            font-style: italic;
            color: #666;
        }
        
        .content-container ul, .content-container ol {
            margin-bottom: 1.5rem;
            padding-left: 20px;
        }
        
        .share-buttons {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
        }
        
        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .share-btn.facebook { background: #3b5998; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.whatsapp { background: #25d366; }
        .share-btn.link { background: var(--secondary-color); }
        .share-btn.telegram { background: #0088cc; }
        
        .share-btn:hover {
            transform: scale(1.1) rotate(5deg);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .related-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }
        
        .related-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .related-image {
            height: 150px;
            overflow: hidden;
        }
        
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .related-card:hover .related-image img {
            transform: scale(1.1);
        }
        
        .navigation-buttons {
            margin: 50px 0;
        }
        
        .nav-btn {
            padding: 15px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
            border: 2px solid;
        }
        
        .nav-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .badge-category {
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .author-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .author-box h5 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        @media print {
            .detail-hero {
                background: none !important;
                color: black !important;
                padding: 50px 0 !important;
            }
            
            .share-buttons, .navigation-buttons, .author-box, footer, header {
                display: none !important;
            }
            
            .content-container {
                max-width: 100% !important;
            }
            
            body {
                font-size: 12pt !important;
                line-height: 1.5 !important;
            }
        }
        
        @media (max-width: 768px) {
            .share-buttons {
                position: static;
                display: flex;
                justify-content: center;
                margin: 30px 0;
                transform: none;
            }
            
            .share-btn {
                margin: 0 5px;
            }
            
            .detail-hero {
                padding: 100px 0;
            }
            
            .meta-item {
                display: block;
                margin: 10px 0;
            }
        }
        
        /* Loading animation for images */
        .loading-image {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .hover-effect:hover {
            background-color: #f8f9fa;
            padding-left: 10px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <?php 
    // Check if header exists
    $header_path = '../includes/header.php';
    if (file_exists($header_path)) {
        include $header_path;
    }
    ?>
    
    <!-- Hero Section -->
    <section class="detail-hero" data-aos="fade-down">
        <div class="container position-relative">
            <div class="hero-content">
                <div class="category-badge mb-3">
                    <span class="badge bg-primary px-3 py-2 badge-category">
                        <?php echo !empty($news['kategori']) ? htmlspecialchars($news['kategori']) : 'Umum'; ?>
                    </span>
                </div>
                <h1 class="display-5 fw-bold mb-4"><?php echo htmlspecialchars($news['judul']); ?></h1>
                
                <div class="news-meta">
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <?php echo !empty($news['created_at']) ? format_date($news['created_at']) : '-'; ?>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-user"></i>
                        <?php echo !empty($news['penulis_nama']) ? htmlspecialchars($news['penulis_nama']) : (!empty($news['penulis']) ? htmlspecialchars($news['penulis']) : 'Admin'); ?>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-eye"></i>
                        <?php echo !empty($news['views']) ? ($news['views'] + 1) : '1'; ?> dilihat
                    </div>
                    <?php if (!empty($news['updated_at']) && $news['updated_at'] != $news['created_at']): ?>
                    <div class="meta-item">
                        <i class="fas fa-history"></i>
                        Diperbarui: <?php echo format_date($news['updated_at']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Share Buttons -->
    <div class="share-buttons d-none d-lg-block">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" 
           class="share-btn facebook" target="_blank" title="Share di Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($page_url); ?>&text=<?php echo urlencode($news['judul']); ?>" 
           class="share-btn twitter" target="_blank" title="Share di Twitter">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="https://wa.me/?text=<?php echo urlencode($news['judul'] . ' - ' . $page_url); ?>" 
           class="share-btn whatsapp" target="_blank" title="Share di WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://t.me/share/url?url=<?php echo urlencode($page_url); ?>&text=<?php echo urlencode($news['judul']); ?>" 
           class="share-btn telegram" target="_blank" title="Share di Telegram">
            <i class="fab fa-telegram"></i>
        </a>
        <button class="share-btn link" onclick="copyToClipboard()" title="Salin tautan">
            <i class="fas fa-link"></i>
        </button>
    </div>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row">
                <!-- Content -->
                <div class="col-lg-8">
                    <article data-aos="fade-up">
                        <div class="content-container">
                            <?php 
                            // Process and display content
                            $content = !empty($news['konten']) ? $news['konten'] : '';
                            
                            // Convert URLs to clickable links
                            $content = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank" class="text-primary">$1</a>', $content);
                            
                            // Replace line breaks with paragraphs
                            $content = nl2br($content);
                            
                            // Add responsive class to images
                            $content = preg_replace('/<img/', '<img class="img-fluid"', $content);
                            
                            echo $content;
                            ?>
                            
                            <?php if (!empty($news['sumber'])): ?>
                                <div class="alert alert-info mt-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Sumber:</strong> <?php echo htmlspecialchars($news['sumber']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Author Box -->
                        <?php if (!empty($news['penulis_nama'])): ?>
                        <div class="author-box" data-aos="fade-up" data-aos-delay="100">
                            <h5>Tentang Penulis</h5>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($news['penulis_nama']); ?></strong></p>
                            <?php 
                            // Get more author info
                            if (!empty($news['penulis'])) {
                                $author_query = "SELECT jabatan, foto FROM users WHERE id = ?";
                                $author_stmt = mysqli_prepare($conn, $author_query);
                                mysqli_stmt_bind_param($author_stmt, 'i', $news['penulis']);
                                mysqli_stmt_execute($author_stmt);
                                $author_result = mysqli_stmt_get_result($author_stmt);
                                $author_info = mysqli_fetch_assoc($author_result);
                                
                                if (!empty($author_info['jabatan'])):
                            ?>
                                <small class="text-muted"><?php echo htmlspecialchars($author_info['jabatan']); ?></small>
                            <?php 
                                endif;
                                if (isset($author_stmt)) mysqli_stmt_close($author_stmt);
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tags -->
                        <?php if (!empty($news['meta_keywords'])): ?>
                        <div class="mt-5" data-aos="fade-up" data-aos-delay="200">
                            <h5><i class="fas fa-tags me-2"></i>Tagar</h5>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php 
                                $tags = explode(',', $news['meta_keywords']);
                                foreach ($tags as $tag):
                                    $tag = trim($tag);
                                    if (!empty($tag)):
                                ?>
                                    <a href="berita.php?tag=<?php echo urlencode($tag); ?>" 
                                       class="badge bg-light text-dark border py-2 px-3 text-decoration-none">
                                        #<?php echo htmlspecialchars($tag); ?>
                                    </a>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </article>
                    
                    <!-- Navigation -->
                    <div class="navigation-buttons" data-aos="fade-up" data-aos-delay="300">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="berita.php" class="btn btn-outline-primary nav-btn w-100">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Berita
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-outline-secondary nav-btn w-100" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i> Cetak Berita
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Related News -->
                    <?php if (!empty($related_news)): ?>
                    <div class="mt-5" data-aos="fade-up" data-aos-delay="400">
                        <h3 class="mb-4">Berita Terkait</h3>
                        <div class="row">
                            <?php foreach ($related_news as $index => $related): ?>
                                <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                                    <div class="related-card animate-on-hover">
                                        <div class="related-image loading-image">
                                            <?php if (!empty($related['gambar'])): ?>
                                                <img src="../assets/uploads/berita/<?php echo htmlspecialchars($related['gambar']); ?>" 
                                                     alt="<?php echo htmlspecialchars($related['judul']); ?>"
                                                     onload="this.parentElement.classList.remove('loading-image')">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                    <i class="fas fa-newspaper fa-3x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <span class="badge bg-primary mb-2">
                                                <?php echo !empty($related['kategori']) ? htmlspecialchars($related['kategori']) : 'Umum'; ?>
                                            </span>
                                            <h6 class="card-title"><?php echo htmlspecialchars(mb_strimwidth($related['judul'], 0, 70, '...')); ?></h6>
                                            <p class="card-text text-muted small">
                                                <i class="far fa-calendar me-1"></i>
                                                <?php echo !empty($related['created_at']) ? format_date($related['created_at'], 'd M Y') : '-'; ?>
                                            </p>
                                            <a href="detail-berita.php?slug=<?php echo !empty($related['slug']) ? $related['slug'] : ''; ?>" 
                                               class="btn btn-sm btn-outline-primary mt-2">
                                                Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Recent News -->
                    <div class="mb-5" data-aos="fade-left">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="fas fa-history me-2"></i>Berita Terbaru</h4>
                            <a href="berita.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <?php if (!empty($recent_news)): ?>
                            <?php foreach ($recent_news as $item): ?>
                                <a href="detail-berita.php?slug=<?php echo !empty($item['slug']) ? $item['slug'] : ''; ?>" class="text-decoration-none text-dark">
                                    <div class="card mb-3 border-0 shadow-sm animate-on-hover">
                                        <div class="row g-0">
                                            <div class="col-4">
                                                <?php if (!empty($item['gambar'])): ?>
                                                    <img src="../assets/uploads/berita/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                                         class="img-fluid rounded-start loading-image" 
                                                         alt="<?php echo htmlspecialchars($item['judul']); ?>"
                                                         style="height: 80px; object-fit: cover;"
                                                         onload="this.classList.remove('loading-image')">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                        <i class="fas fa-newspaper text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-8">
                                                <div class="card-body p-2">
                                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars(mb_strimwidth($item['judul'], 0, 50, '...')); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar me-1"></i>
                                                        <?php echo !empty($item['created_at']) ? format_date($item['created_at'], 'd M Y') : '-'; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Belum ada berita lainnya.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="card mb-5" data-aos="fade-left" data-aos-delay="100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-folder me-2"></i>Kategori</h5>
                            <ul class="list-unstyled">
                                <?php foreach ($categories as $key => $name): ?>
                                    <li>
                                        <a href="berita.php?kategori=<?php echo urlencode($key); ?>" 
                                           class="text-decoration-none d-flex justify-content-between align-items-center py-2 border-bottom text-dark hover-effect">
                                            <span>
                                                <i class="fas fa-folder-open me-2 text-primary"></i>
                                                <?php echo htmlspecialchars($name); ?>
                                            </span>
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo !empty($category_counts[$key]) ? $category_counts[$key] : 0; ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share Box -->
                    <div class="card" data-aos="fade-left" data-aos-delay="200">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3"><i class="fas fa-share-alt me-2"></i>Bagikan Berita</h5>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" 
                                   class="btn btn-primary btn-sm" target="_blank" title="Facebook">
                                    <i class="fab fa-facebook-f me-1"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($page_url); ?>&text=<?php echo urlencode($news['judul']); ?>" 
                                   class="btn btn-info btn-sm text-white" target="_blank" title="Twitter">
                                    <i class="fab fa-twitter me-1"></i> Twitter
                                </a>
                                <a href="https://wa.me/?text=<?php echo urlencode($news['judul'] . ' - ' . $page_url); ?>" 
                                   class="btn btn-success btn-sm" target="_blank" title="WhatsApp">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <button class="btn btn-secondary btn-sm" onclick="copyToClipboard()" title="Salin Tautan">
                                    <i class="fas fa-link me-1"></i> Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php 
    // Check if footer exists
    $footer_path = '../includes/footer.php';
    if (file_exists($footer_path)) {
        include $footer_path;
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <?php 
    // Check if animasi.js exists
    $animasi_path = '../assets/js/animasi.js';
    if (file_exists($animasi_path)): ?>
    <script src="<?php echo $animasi_path; ?>"></script>
    <?php endif; ?>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Copy link to clipboard
        function copyToClipboard() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                // Show notification
                showNotification('Tautan berhasil disalin!', 'success');
            }).catch(err => {
                console.error('Failed to copy: ', err);
                showNotification('Gagal menyalin tautan', 'danger');
            });
        }
        
        // Show notification
        function showNotification(message, type) {
            // Remove existing notification
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} custom-alert position-fixed top-0 start-50 translate-middle-x mt-3`;
            alert.style.zIndex = '9999';
            alert.style.minWidth = '300px';
            alert.style.textAlign = 'center';
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                ${message}
            `;
            document.body.appendChild(alert);
            
            // Remove after 3 seconds
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
        
        // Image zoom effect
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.content-container img');
            images.forEach(img => {
                img.style.cursor = 'zoom-in';
                img.addEventListener('click', function() {
                    if (this.classList.contains('zoomed')) {
                        this.classList.remove('zoomed');
                        this.style.transform = 'scale(1)';
                        this.style.cursor = 'zoom-in';
                        this.style.zIndex = 'auto';
                    } else {
                        this.classList.add('zoomed');
                        this.style.transform = 'scale(1.8)';
                        this.style.transition = 'transform 0.3s ease';
                        this.style.cursor = 'zoom-out';
                        this.style.zIndex = '1000';
                        this.style.position = 'relative';
                    }
                });
            });
            
            // Share button animation
            const shareButtons = document.querySelectorAll('.share-btn, .btn-outline-primary, .btn-outline-secondary');
            shareButtons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.transition = 'transform 0.3s ease';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Lazy load images
            const lazyImages = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
            
            // Add smooth scroll to anchor links in content
            document.querySelectorAll('.content-container a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Handle print functionality
            const printButtons = document.querySelectorAll('[onclick*="print"]');
            printButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (window.innerWidth > 768) {
                        window.print();
                    } else {
                        showNotification('Fitur cetak lebih baik diakses dari desktop', 'info');
                    }
                });
            });
            
            // Add loading animation to images that haven't loaded yet
            const allImages = document.querySelectorAll('img:not([src=""])');
            allImages.forEach(img => {
                if (!img.complete) {
                    img.classList.add('loading-image');
                    img.addEventListener('load', function() {
                        this.classList.remove('loading-image');
                    });
                    img.addEventListener('error', function() {
                        this.classList.remove('loading-image');
                        this.src = '../assets/images/default-image.jpg';
                    });
                }
            });
        });
        
        // Handle page visibility change for better UX
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden
                console.log('Page is hidden');
            } else {
                // Page is visible
                console.log('Page is visible');
            }
        });
        
        // Handle mobile menu if exists
        const mobileMenuBtn = document.querySelector('.navbar-toggler');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                document.body.classList.toggle('menu-open');
            });
        }
    </script>
</body>
</html>
<?php
// Close database connections
if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($cat_result)) mysqli_free_result($cat_result);
mysqli_close($conn);
?>