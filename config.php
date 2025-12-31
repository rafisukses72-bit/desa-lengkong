<?php
// config.php (di root folder)

// Hanya start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'desa_lengkong');

// Base URL
define('BASE_URL', 'http://localhost/Lengkong/');
define('SITE_NAME', 'Desa Lengkong');

// Database Connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fungsi untuk redirect - hanya deklarasi jika belum ada
if (!function_exists('redirect')) {
    function redirect($url, $message = '', $type = 'info') {
        if (!empty($message)) {
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = $type;
        }
        header("Location: $url");
        exit();
    }
}

// Fungsi untuk menampilkan pesan - hanya deklarasi jika belum ada
if (!function_exists('display_message')) {
    function display_message() {
        if (isset($_SESSION['message'])) {
            $type = $_SESSION['message_type'] ?? 'info';
            $class = '';
            switch($type) {
                case 'success': $class = 'alert-success'; break;
                case 'error': $class = 'alert-danger'; break;
                case 'warning': $class = 'alert-warning'; break;
                default: $class = 'alert-info';
            }
            
            echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($_SESSION['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
    }
}

// Fungsi untuk sanitasi input
if (!function_exists('clean_input')) {
    function clean_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}
?>