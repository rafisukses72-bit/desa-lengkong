<?php

$current_page = basename($_SERVER['PHP_SELF']);

// Cek status login
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? '';

// Fungsi untuk mendapatkan role display name
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'operator' => 'Operator',
        'warga' => 'Warga',
        'guest' => 'Pengunjung'
    ];
    return $roles[$role] ?? 'Pengguna';
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Desa Lengkong'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Lightbox -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animasi.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #f39c12;
            --success: #27ae60;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        /* Animasi Kustom */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        .rotate {
            animation: rotate 20s linear infinite;
        }
        
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        
        .bounce {
            animation: bounce 2s infinite;
        }
        
        /* Styling untuk dropdown user */
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--accent), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .role-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
        }
        
        .badge-admin {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .badge-operator {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }
        
        .badge-warga {
            background: linear-gradient(45deg, #27ae60, #219653);
            color: white;
        }
        
        .login-btn {
            position: relative;
            overflow: hidden;
            background: linear-gradient(45deg, var(--accent), #e67e22);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
            color: white;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .dashboard-btn {
            background: linear-gradient(45deg, var(--success), #2ecc71);
            border: none;
            color: white;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .dashboard-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
            color: white;
        }
        
        /* Styling untuk mobile */
        @media (max-width: 991.98px) {
            .navbar-nav .nav-item:last-child {
                margin-top: 10px;
            }
            
            .user-dropdown .dropdown-menu {
                position: static !important;
                transform: none !important;
                margin-top: 10px;
                border: none;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
            }
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3">
            <div class="loading-text">Menginisialisasi Sistem</div>
            <div class="progress" style="width: 200px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
            </div>
        </div>
    </div>
    
    <!-- Floating Particles -->
    <div class="particles-container">
        <?php for($i = 0; $i < 20; $i++): ?>
        <div class="particle" style="
            left: <?php echo rand(0, 100); ?>%;
            top: <?php echo rand(0, 100); ?>%;
            width: <?php echo rand(2, 10); ?>px;
            height: <?php echo rand(2, 10); ?>px;
            animation-delay: <?php echo rand(0, 5); ?>s;
            animation-duration: <?php echo rand(10, 30); ?>s;
            background: hsl(<?php echo rand(0, 360); ?>, 70%, 60%);
        "></div>
        <?php endfor; ?>
    </div>
    
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-lg" style="background: linear-gradient(135deg, var(--primary), #1a2530);">
        <div class="container">
            <!-- Logo dengan Animasi -->
            <a class="navbar-brand d-flex align-items-center animate__animated animate__fadeInLeft" href="index.php">
                <div class="logo-icon me-2 pulse" style="font-size: 1.5rem;">
                    <i class="fas fa-village text-warning"></i>
                </div>
                <div>
                    <span class="fw-bold fs-4 text-white">Desa</span>
                    <span class="fw-bold fs-4 text-warning">Lengkong</span>
                    <small class="d-block text-light" style="font-size: 0.7rem; margin-top: -5px;">Kabupaten Kuningan</small>
                </div>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler animate__animated animate__fadeInRight" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menu -->
            <div class="collapse navbar-collapse animate__animated animate__fadeInDown" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Menu Publik -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?> hover-effect" href="index.php">
                            <i class="fas fa-home me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'profil') !== false ? 'active' : ''; ?> hover-effect" href="modules/profil.php">
                            <i class="fas fa-info-circle me-1"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'berita') !== false ? 'active' : ''; ?> hover-effect" href="modules/berita.php">
                            <i class="fas fa-newspaper me-1"></i> Berita
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'potensi') !== false ? 'active' : ''; ?> hover-effect" href="modules/potensi.php">
                            <i class="fas fa-store me-1"></i> Potensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'layanan') !== false ? 'active' : ''; ?> hover-effect" href="modules/layanan.php">
                            <i class="fas fa-handshake me-1"></i> Layanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'galeri') !== false ? 'active' : ''; ?> hover-effect" href="modules/galeri.php">
                            <i class="fas fa-images me-1"></i> Galeri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo strpos($current_page, 'kontak') !== false ? 'active' : ''; ?> hover-effect" href="modules/kontak.php">
                            <i class="fas fa-address-book me-1"></i> Kontak
                        </a>
                    </li>
                    
                    <!-- Menu Login/User -->
                    <?php if($isLoggedIn): ?>
                        <!-- User Dropdown untuk yang sudah login -->
                        <li class="nav-item dropdown user-dropdown ms-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar me-2">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <div class="d-none d-lg-block">
                                    <small class="d-block text-light" style="font-size: 0.8rem;"><?php echo htmlspecialchars($username); ?></small>
                                    <span class="badge role-badge badge-<?php echo $userRole; ?>">
                                        <?php echo getRoleDisplayName($userRole); ?>
                                    </span>
                                </div>
                                <?php 
                                // Hitung notifikasi (contoh: jika ada pengajuan layanan yang belum dilihat)
                                // $notifCount = 0;
                                // if($userRole === 'admin' || $userRole === 'operator') {
                                //     $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM pengajuan_layanan WHERE status = 'pending'");
                                //     if($row = mysqli_fetch_assoc($result)) {
                                //         $notifCount = $row['count'];
                                //     }
                                // }
                                ?>
                                <!-- <?php if($notifCount > 0): ?>
                                    <span class="notification-badge"><?php echo $notifCount; ?></span>
                                <?php endif; ?> -->
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                                <li>
                                    <div class="dropdown-header">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2">
                                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($username); ?></h6>
                                                <small class="text-muted"><?php echo getRoleDisplayName($userRole); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="modules/login.php">
                                        <i class="fas fa-tachometer-alt me-2 text-primary"></i> login
                                    </a>
                                </li>
                                
                                <?php if($userRole === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="admin/pengajuan.php">
                                        <i class="fas fa-tasks me-2 text-success"></i> Pengajuan
                                        <!-- <?php if($notifCount > 0): ?>
                                            <span class="badge bg-danger float-end"><?php echo $notifCount; ?></span>
                                        <?php endif; ?> -->
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="admin/manage-berita.php">
                                        <i class="fas fa-newspaper me-2 text-info"></i> Kelola Berita
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="admin/manage-users.php">
                                        <i class="fas fa-users me-2 text-warning"></i> Kelola Pengguna
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php if($userRole === 'warga'): ?>
                                <li>
                                    <a class="dropdown-item" href="warga/dashboard.php">
                                        <i class="fas fa-user me-2 text-info"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="warga/pengajuan-saya.php">
                                        <i class="fas fa-file-alt me-2 text-success"></i> Pengajuan Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="warga/edit-profil.php">
                                        <i class="fas fa-edit me-2 text-warning"></i> Edit Profil
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="admin/edit-profil.php">
                                        <i class="fas fa-cog me-2 text-secondary"></i> Pengaturan
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="admin/logout.php" onclick="return confirm('Yakin ingin logout?')">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Tombol Dashboard Utama (Untuk desktop) -->
                        <li class="nav-item d-none d-lg-block ms-2">
                            <a class="nav-link dashboard-btn animate__animated animate__pulse" href="admin/dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                        
                    <?php else: ?>
                        <!-- Tombol Login untuk yang belum login -->
                        <li class="nav-item ms-2">
                            <a class="nav-link login-btn animate__animated animate__pulse animate__infinite" href="admin/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login / Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/6281234567890" class="whatsapp-float animate__animated animate__bounceInUp animate__delay-2s" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Theme Toggle -->
    <button class="theme-toggle animate__animated animate__fadeInUp">
        <i class="fas fa-moon"></i>
    </button>
    
    <!-- Notification Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Sukses</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php echo $_SESSION['success']; ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php echo $_SESSION['error']; ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="content-wrapper">