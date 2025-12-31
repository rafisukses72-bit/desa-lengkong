<?php
// admin/dashboard.php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Only allow admin and operator
if (!in_array($user_role, ['admin', 'operator'])) {
    header("Location: ../index.php");
    exit();
}

// Get user data
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get statistics
$stats = [
    'total_users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE status = 'active'"))['count'],
    'total_news' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM berita WHERE status = 'published'"))['count'],
    'total_pengajuan' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pengajuan_layanan"))['count'],
    'pending_pengajuan' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pengajuan_layanan WHERE status = 'pending'"))['count'],
    'total_potensi' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM potensi WHERE status = 'aktif'"))['count'],
    'total_layanan' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM layanan WHERE aktif = 1"))['count']
];

// Get recent activities
$activities = [];
$query = "SELECT * FROM pengajuan_layanan ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $activities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Desa Lengkong</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            min-height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            color: white;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .user-profile {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--secondary), var(--success));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 15px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--secondary);
        }
        
        .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--success);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: static;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
            <p class="mb-0 text-muted">
                <span class="badge bg-<?php echo $user_role == 'admin' ? 'danger' : ($user_role == 'operator' ? 'warning' : 'success'); ?>">
                    <?php echo ucfirst($user_role); ?>
                </span>
            </p>
        </div>
        
        <nav class="nav flex-column mt-3">
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a class="nav-link" href="manage-berita.php">
                <i class="fas fa-newspaper me-2"></i> Kelola Berita
            </a>
            <a class="nav-link" href="manage-pengajuan.php">
                <i class="fas fa-tasks me-2"></i> Pengajuan Layanan
            </a>
            <a class="nav-link" href="manage-potensi.php">
                <i class="fas fa-store me-2"></i> Potensi Desa
            </a>
            <?php if($user_role == 'admin'): ?>
            <a class="nav-link" href="manage-users.php">
                <i class="fas fa-users me-2"></i> Kelola Pengguna
            </a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-cog me-2"></i> Pengaturan
            </a>
            <?php endif; ?>
            <a class="nav-link" href="edit-profil.php">
                <i class="fas fa-user-edit me-2"></i> Edit Profil
            </a>
            <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard Admin</h1>
            <span class="text-muted"><?php echo date('d F Y, H:i'); ?></span>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(45deg, var(--primary), var(--secondary));">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                            <p class="mb-0 text-muted">Total Pengguna</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(45deg, var(--success), #2ecc71);">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_news']; ?></h3>
                            <p class="mb-0 text-muted">Berita</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(45deg, var(--warning), #e67e22);">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['pending_pengajuan']; ?></h3>
                            <p class="mb-0 text-muted">Pengajuan Menunggu</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background: linear-gradient(45deg, var(--danger), #c0392b);">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $stats['total_potensi']; ?></h3>
                            <p class="mb-0 text-muted">Potensi Desa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Aktivitas Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($activities)): ?>
                            <div class="list-group">
                                <?php foreach($activities as $activity): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Pengajuan Layanan Baru</h6>
                                        <small><?php echo date('d M Y, H:i', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1">ID Pengajuan: <?php echo $activity['id']; ?></p>
                                    <span class="badge bg-<?php echo $activity['status'] == 'pending' ? 'warning' : ($activity['status'] == 'selesai' ? 'success' : 'info'); ?>">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Belum ada aktivitas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Statistik Singkat
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Layanan
                                <span class="badge bg-primary rounded-pill"><?php echo $stats['total_layanan']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Pengajuan
                                <span class="badge bg-success rounded-pill"><?php echo $stats['total_pengajuan']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Pengajuan Selesai
                                <span class="badge bg-info rounded-pill">
                                    <?php 
                                    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pengajuan_layanan WHERE status = 'selesai'"))['count'];
                                    echo $selesai;
                                    ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h5>Quick Actions</h5>
                        <div class="d-grid gap-2 mt-3">
                            <a href="manage-berita.php?action=create" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Berita
                            </a>
                            <a href="manage-pengajuan.php" class="btn btn-success">
                                <i class="fas fa-tasks me-2"></i>Kelola Pengajuan
                            </a>
                            <?php if($user_role == 'admin'): ?>
                            <a href="manage-users.php?action=create" class="btn btn-warning">
                                <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto refresh stats every 60 seconds
        setInterval(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>