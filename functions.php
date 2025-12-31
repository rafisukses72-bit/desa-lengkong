<?php
// functions.php (di root folder)
// require_once 'config.php'; // Jangan require di sini, nanti circular reference


function format_date($date) {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;
    
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    return $hari[date('w', $timestamp)] . ', ' . 
           date('d', $timestamp) . ' ' . 
           $bulan[date('n', $timestamp) - 1] . ' ' . 
           date('Y', $timestamp);
}

function format_datetime($datetime) {
    if (empty($datetime)) return '-';
    
    $timestamp = strtotime($datetime);
    if ($timestamp === false) return $datetime;
    
    return date('d/m/Y H:i', $timestamp);
}

// Cek koneksi database sebelum menggunakan query
function get_settings() {
    global $conn;
    
    if (!$conn) {
        return [
            'nama_desa' => 'Desa Lengkong',
            'motto_desa' => 'Maju, Mandiri, Sejahtera',
            'theme_color' => '#2c3e50',
            'tentang_desa' => 'Desa Lengkong merupakan desa yang berada di Kecamatan Garawangi, Kabupaten Kuningan, Jawa Barat.',
            'alamat_kantor' => 'Jl. Raya Lengkong No. 1, Kuningan',
            'telepon' => '0265-123456',
            'email' => 'desa@lengkong.kuningankab.go.id',
            'whatsapp' => '6281234567890',
            'facebook' => '#',
            'instagram' => '#',
            'youtube' => '#',
            'logo' => ''
        ];
    }
    
    $result = mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return [
        'nama_desa' => 'Desa Lengkong',
        'motto_desa' => 'Maju, Mandiri, Sejahtera',
        'theme_color' => '#2c3e50',
        'tentang_desa' => 'Desa Lengkong merupakan desa yang berada di Kecamatan Garawangi, Kabupaten Kuningan, Jawa Barat.',
        'alamat_kantor' => 'Jl. Raya Lengkong No. 1, Kuningan',
        'telepon' => '0265-123456',
        'email' => 'desa@lengkong.kuningankab.go.id',
        'whatsapp' => '6281234567890',
        'facebook' => '#',
        'instagram' => '#',
        'youtube' => '#',
        'logo' => ''
    ];
}

function get_active_banners() {
    global $conn;
    if (!$conn) return [];
    
    $banners = [];
    $query = "SELECT * FROM banner WHERE aktif = 1 ORDER BY urutan ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $banners[] = $row;
        }
    }
    
    return $banners;
}

function get_recent_news($limit = 6) {
    global $conn;
    if (!$conn) return [];
    
    $news = [];
    $query = "SELECT * FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
    }
    
    return $news;
}

function get_featured_news($limit = 3) {
    global $conn;
    if (!$conn) return [];
    
    $news = [];
    $query = "SELECT * FROM berita WHERE status = 'published' AND is_featured = 1 ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = $row;
        }
    }
    
    return $news;
}

function get_upcoming_events($limit = 5) {
    global $conn;
    if (!$conn) return [];
    
    $events = [];
    $today = date('Y-m-d');
    $query = "SELECT * FROM agenda WHERE tanggal_mulai >= '$today' ORDER BY tanggal_mulai ASC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = $row;
        }
    }
    
    return $events;
}

function get_active_potentials($limit = 4) {
    global $conn;
    if (!$conn) return [];
    
    $potentials = [];
    $query = "SELECT * FROM potensi WHERE status = 'aktif' ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $potentials[] = $row;
        }
    }
    
    return $potentials;
}

function get_structure() {
    global $conn;
    if (!$conn) return [];
    
    $structure = [];
    $query = "SELECT * FROM struktur_desa WHERE status = 'aktif' ORDER BY urutan ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $structure[] = $row;
        }
    }
    
    return $structure;
}

function get_services() {
    global $conn;
    if (!$conn) return [];
    
    $services = [];
    $query = "SELECT * FROM layanan WHERE aktif = 1 ORDER BY urutan ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }
    }
    
    return $services;
}

function get_gallery($limit = 8) {
    global $conn;
    if (!$conn) return [];
    
    $gallery = [];
    $query = "SELECT * FROM galeri ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $gallery[] = $row;
        }
    }
    
    return $gallery;
}

function get_rt_rw() {
    global $conn;
    if (!$conn) return [];
    
    $rt_rw = [];
    $query = "SELECT * FROM rt_rw ORDER BY jenis DESC, nomor ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rt_rw[] = $row;
        }
    }
    
    return $rt_rw;
}

function count_total_news() {
    global $conn;
    if (!$conn) return 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM berita WHERE status = 'published'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    return 0;
}

function count_total_potentials() {
    global $conn;
    if (!$conn) return 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM potensi WHERE status = 'aktif'");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    return 0;
}

function count_total_services() {
    global $conn;
    if (!$conn) return 0;
    
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM layanan WHERE aktif = 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    return 0;
}

function get_total_residents() {
    global $conn;
    if (!$conn) return 3500;
    
    // Coba ambil dari tabel demografi jika ada
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'demografi'");
    if (mysqli_num_rows($result) > 0) {
        $result = mysqli_query($conn, "SELECT jumlah_penduduk FROM demografi ORDER BY tahun DESC LIMIT 1");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['jumlah_penduduk'] ?? 3500;
        }
    }
    
    return 3500; // Default
}

function get_total_families() {
    global $conn;
    if (!$conn) return 850;
    
    // Coba ambil dari tabel demografi jika ada
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'demografi'");
    if (mysqli_num_rows($result) > 0) {
        $result = mysqli_query($conn, "SELECT jumlah_kk FROM demografi ORDER BY tahun DESC LIMIT 1");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['jumlah_kk'] ?? 850;
        }
    }
    
    return 850; // Default
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function upload_file($file, $folder) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    
    // Pastikan folder ada
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $target_path = $folder . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $filename;
    }
    
    return false;
}

// Fungsi untuk generate slug
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Fungsi untuk menampilkan gambar default jika tidak ada
function get_image_path($path, $default = 'default.jpg') {
    if (empty($path) || !file_exists($path)) {
        return 'assets/images/' . $default;
    }
    return $path;
}

// Fungsi untuk memotong teks
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' ')) . $suffix;
    }
    return $text;
}
?>