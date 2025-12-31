<?php
// modules/kontak.php - FIXED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once '../functions.php'; // Gunakan fungsi dari functions.php

// HAPUS fungsi clean_input dari sini karena sudah ada di functions.php
// Hanya gunakan require_once functions.php

// Fungsi khusus untuk kontak (untuk menghindari konflik)
function get_kontak_settings($conn) {
    // Default settings untuk kontak
    $default_settings = [
        'nama_desa' => 'Desa Lengkong',
        'alamat' => 'Jl. Desa No. 1, Kecamatan, Kabupaten',
        'telepon' => '(021) 12345678',
        'telepon2' => '',
        'email' => 'desa@example.com',
        'email2' => '',
        'facebook' => '#',
        'instagram' => '#',
        'youtube' => '#',
        'twitter' => '',
        'maps_embed' => '',
        'jam_kerja' => 'Senin - Jumat: 08:00 - 16:00, Sabtu: 08:00 - 12:00'
    ];
    
    // Cek apakah tabel pengaturan ada
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'pengaturan'");
    if ($result && mysqli_num_rows($result) > 0) {
        $settings_result = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $db_settings = mysqli_fetch_assoc($settings_result);
            return array_merge($default_settings, $db_settings);
        }
    }
    
    // Cek tabel settings alternatif
    $result = @mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
    if ($result && mysqli_num_rows($result) > 0) {
        $settings_result = mysqli_query($conn, "SELECT * FROM settings LIMIT 1");
        if ($settings_result && mysqli_num_rows($settings_result) > 0) {
            $db_settings = mysqli_fetch_assoc($settings_result);
            return array_merge($default_settings, $db_settings);
        }
    }
    
    return $default_settings;
}

// Ambil pengaturan
$settings = get_kontak_settings($conn);

// Handle form submission
$message = '';
$success = false;
$errors = [];

// Cek apakah tabel kontak_pesan ada
$table_exists = false;
$check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'kontak_pesan'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $table_exists = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $telepon = clean_input($_POST['telepon'] ?? '');
    $subjek = clean_input($_POST['subjek'] ?? '');
    $pesan = clean_input($_POST['pesan'] ?? '');
    
    // Validation
    if (empty($nama)) $errors[] = 'Nama harus diisi';
    if (empty($email)) $errors[] = 'Email harus diisi';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
    if (empty($subjek)) $errors[] = 'Subjek harus diisi';
    if (empty($pesan)) $errors[] = 'Pesan harus diisi';
    
    if (empty($errors)) {
        if ($table_exists) {
            // Gunakan prepared statements untuk keamanan
            $query = "INSERT INTO kontak_pesan (nama, email, telepon, subjek, pesan, status, created_at) 
                      VALUES (?, ?, ?, ?, ?, 'baru', NOW())";
            
            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $telepon, $subjek, $pesan);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = true;
                    $message = 'Pesan Anda berhasil dikirim. Terima kasih!';
                    
                    // Clear form
                    $_POST = [];
                } else {
                    $errors[] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        } else {
            // Jika tabel tidak ada, simpan ke file atau hanya tampilkan pesan sukses
            $success = true;
            $message = 'Pesan Anda berhasil dikirim. Terima kasih atas kontribusi Anda!';
            
            // Optional: Simpan ke file log
            $log_message = date('Y-m-d H:i:s') . " - Nama: $nama, Email: $email, Telepon: $telepon, Subjek: $subjek, Pesan: $pesan\n";
            file_put_contents('../logs/kontak.log', $log_message, FILE_APPEND);
            
            // Clear form
            $_POST = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - <?php echo htmlspecialchars($settings['nama_desa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #7209b7;
            --success: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
        }
        
        .contact-hero {
            background: linear-gradient(rgba(67, 97, 238, 0.9), rgba(58, 12, 163, 0.8));
            color: white;
            padding: 100px 0 50px;
            text-align: center;
            margin-top: 80px;
        }
        
        .contact-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 30px;
            height: 100%;
            transition: all 0.3s ease;
            background: white;
            text-align: center;
        }
        
        .contact-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .contact-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.8rem;
        }
        
        .contact-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 400px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
            background: linear-gradient(135deg, var(--secondary), var(--accent));
        }
        
        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            margin: 0 8px;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            transform: translateY(-5px) scale(1.1);
        }
        
        .office-hours {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        
        .alert-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        
        @media (max-width: 768px) {
            .contact-hero {
                padding: 80px 0 40px;
                margin-top: 70px;
            }
            
            .contact-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .social-icon {
                width: 45px;
                height: 45px;
                margin: 0 5px;
            }
            
            .map-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Simple header jika tidak ada file header
    if (!file_exists('../includes/header.php')): ?>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: var(--primary);">
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
                    <li class="nav-item"><a class="nav-link" href="potensi.php">Potensi</a></li>
                    <li class="nav-item"><a class="nav-link" href="galeri.php">Galeri</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <?php else: ?>
        <?php include '../includes/header.php'; ?>
    <?php endif; ?>
    
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Hubungi Kami</h1>
            <p class="lead">Silakan hubungi kami untuk informasi lebih lanjut mengenai Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
        </div>
    </section>
    
    <!-- Messages (Toast Style) -->
    <?php if (!empty($errors) || $success): ?>
    <div class="alert-container">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-lg" role="alert">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-2">Terjadi Kesalahan</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-lg" role="alert">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-2">Berhasil!</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($message); ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Contact Info -->
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4 class="mb-3">Alamat Kantor</h4>
                        <p class="text-muted mb-0">
                            <?php echo htmlspecialchars($settings['alamat']); ?>
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4 class="mb-3">Telepon</h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <?php echo htmlspecialchars($settings['telepon']); ?>
                        </p>
                        <?php if (!empty($settings['telepon2'])): ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone-alt me-2 text-primary"></i>
                            <?php echo htmlspecialchars($settings['telepon2']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4 class="mb-3">Email</h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <?php echo htmlspecialchars($settings['email']); ?>
                        </p>
                        <?php if (!empty($settings['email2'])): ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope-open me-2 text-primary"></i>
                            <?php echo htmlspecialchars($settings['email2']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Map & Form -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4">
                    <h3 class="mb-4"><i class="fas fa-map-marked-alt me-2"></i>Lokasi Kantor</h3>
                    <div class="map-container">
                        <?php if (!empty($settings['maps_embed'])): ?>
                            <?php echo $settings['maps_embed']; ?>
                        <?php else: ?>
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322283!2d106.8195613507824!3d-6.194741395493371!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5390917b759%3A0x6b45e67356080477!2sJakarta%2C+Daerah+Khusus+Ibukota+Jakarta!5e0!3m2!1sid!2sid!4v1435246472616" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy"
                                title="Lokasi Kantor Desa">
                            </iframe>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="contact-form">
                        <h3 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Kirim Pesan</h3>
                        <form method="POST" action="" id="contactForm" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" 
                                           required
                                           placeholder="Masukkan nama lengkap">
                                    <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           required
                                           placeholder="contoh@email.com">
                                    <div class="invalid-feedback">Email harus valid</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nomor Telepon</label>
                                    <input type="tel" name="telepon" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>"
                                           placeholder="0812-3456-7890">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Subjek <span class="text-danger">*</span></label>
                                    <input type="text" name="subjek" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['subjek'] ?? ''); ?>" 
                                           required
                                           placeholder="Subjek pesan">
                                    <div class="invalid-feedback">Subjek harus diisi</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Pesan <span class="text-danger">*</span></label>
                                <textarea name="pesan" class="form-control" rows="5" 
                                          required
                                          placeholder="Tulis pesan Anda di sini..."><?php echo htmlspecialchars($_POST['pesan'] ?? ''); ?></textarea>
                                <div class="invalid-feedback">Pesan harus diisi</div>
                            </div>
                            
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Social Media -->
            <?php if (!empty($settings['facebook']) || !empty($settings['instagram']) || !empty($settings['youtube']) || !empty($settings['twitter'])): ?>
            <div class="row mb-5">
                <div class="col-12">
                    <div class="text-center">
                        <h3 class="mb-4"><i class="fas fa-share-alt me-2"></i>Ikuti Kami di Media Sosial</h3>
                        <p class="text-muted mb-4">Ikuti akun media sosial kami untuk mendapatkan update terbaru</p>
                        <div>
                            <?php if (!empty($settings['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['facebook']); ?>" target="_blank" 
                               class="social-icon" style="background: #3b5998;" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['instagram']); ?>" target="_blank" 
                               class="social-icon" style="background: #e4405f;" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['youtube']); ?>" target="_blank" 
                               class="social-icon" style="background: #cd201f;" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($settings['twitter']); ?>" target="_blank" 
                               class="social-icon" style="background: #1da1f2;" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Office Hours -->
            <div class="row">
                <div class="col-12">
                    <div class="office-hours">
                        <h3 class="mb-4"><i class="far fa-clock me-2"></i>Jam Kerja Kantor</h3>
                        <p class="lead mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            <?php echo htmlspecialchars($settings['jam_kerja']); ?>
                        </p>
                        <p class="text-muted mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Hari libur nasional: Tutup
                        </p>
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
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo htmlspecialchars($settings['alamat']); ?>
                    </p>
                    <?php if (isset($settings['telepon'])): ?>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <?php echo htmlspecialchars($settings['telepon']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if (isset($settings['email'])): ?>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($settings['email']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['nama_desa']); ?>. All rights reserved.</p>
                    <p class="text-muted mb-0 mt-2">Website Resmi Desa <?php echo htmlspecialchars($settings['nama_desa']); ?></p>
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
        // Form validation dengan Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const fields = form.querySelectorAll('input[required], textarea[required]');
            
            // Real-time validation
            fields.forEach(field => {
                field.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                });
                
                field.addEventListener('blur', function() {
                    if (!this.checkValidity()) {
                        this.classList.add('is-invalid');
                    }
                });
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                
                // Reset validation
                fields.forEach(field => {
                    field.classList.remove('is-invalid', 'is-valid');
                });
                
                // Check each field
                fields.forEach(field => {
                    if (!field.checkValidity()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                });
                
                // Special email validation
                const emailField = form.querySelector('input[type="email"]');
                if (emailField && !validateEmail(emailField.value)) {
                    emailField.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (isValid) {
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
                    submitBtn.disabled = true;
                    
                    // Submit form
                    this.submit();
                }
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Email validation function
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            // Social media icon hover effects
            const socialIcons = document.querySelectorAll('.social-icon');
            socialIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.1)';
                });
                
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>