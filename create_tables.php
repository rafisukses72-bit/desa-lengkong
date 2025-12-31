<?php
// create_tables.php
require_once 'config.php';

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

echo "<h1>Membuat Tabel Database</h1>";

// Query untuk membuat tabel
$queries = [
    "CREATE TABLE IF NOT EXISTS album (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        warna VARCHAR(20) DEFAULT '#667eea',
        warna2 VARCHAR(20) DEFAULT '#764ba2',
        ikon VARCHAR(50) DEFAULT 'images',
        status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS galeri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(200) NOT NULL,
        deskripsi TEXT,
        foto VARCHAR(255) NOT NULL,
        album_id INT DEFAULT NULL,
        status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (album_id) REFERENCES album(id) ON DELETE SET NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS pengaturan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_desa VARCHAR(100) NOT NULL DEFAULT 'Desa Lengkong',
        logo VARCHAR(255),
        favicon VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    "INSERT INTO pengaturan (nama_desa) 
     SELECT 'Desa Lengkong'
     WHERE NOT EXISTS (SELECT 1 FROM pengaturan LIMIT 1)",
    
    "INSERT INTO album (nama, deskripsi, warna, warna2, ikon, status) VALUES
     ('Kegiatan Desa', 'Berbagai kegiatan yang dilakukan di desa', '#FF6B6B', '#4ECDC4', 'users', 'aktif'),
     ('Infrastruktur', 'Fasilitas dan infrastruktur desa', '#45B7D1', '#96E6A1', 'road', 'aktif'),
     ('Alam', 'Pemandangan alam di Desa Lengkong', '#FECA57', '#FF9FF3', 'mountain', 'aktif'),
     ('Acara Adat', 'Tradisi dan acara adat desa', '#FF9FF3', '#F368E0', 'drum', 'aktif')",
    
    "INSERT INTO galeri (judul, deskripsi, foto, album_id, status) VALUES
     ('Gotong Royong', 'Kegiatan gotong royong membersihkan lingkungan', 'gotong-royong.jpg', 1, 'aktif'),
     ('Jembatan Baru', 'Jembatan baru yang dibangun di desa', 'jembatan.jpg', 2, 'aktif'),
     ('Sunset di Desa', 'Pemandangan sunset yang indah', 'sunset.jpg', 3, 'aktif'),
     ('Tari Tradisional', 'Pentas tari tradisional desa', 'tari.jpg', 4, 'aktif')"
];

foreach ($queries as $query) {
    echo "<p>Menjalankan: " . htmlspecialchars(substr($query, 0, 50)) . "...</p>";
    
    if (mysqli_query($conn, $query)) {
        echo "<p style='color: green;'>✓ Berhasil</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
    }
    echo "<hr>";
}

echo "<h2 style='color: green;'>Proses selesai!</h2>";
echo "<p><a href='modules/galeri.php'>Klik di sini untuk melihat galeri</a></p>";

mysqli_close($conn);
?>