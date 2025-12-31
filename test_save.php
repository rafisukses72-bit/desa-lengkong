<?php
session_start();
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak");
}

echo "<h2>Test Save System</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h3>Data POST diterima:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test connection
    echo "<h3>Test Database Connection:</h3>";
    if ($conn) {
        echo "✓ Koneksi database OK<br>";
        
        // Test simple query
        $test_query = "SELECT 1 as test";
        $result = mysqli_query($conn, $test_query);
        if ($result) {
            echo "✓ Query test berhasil<br>";
        } else {
            echo "✗ Query test gagal: " . mysqli_error($conn) . "<br>";
        }
        
        // Test update
        $test_update = "UPDATE pengaturan SET nama_desa = 'TEST' WHERE id = 1";
        if (mysqli_query($conn, $test_update)) {
            echo "✓ Update test berhasil<br>";
            
            // Verify
            $verify = mysqli_query($conn, "SELECT nama_desa FROM pengaturan WHERE id = 1");
            $row = mysqli_fetch_assoc($verify);
            echo "✓ Data terverifikasi: " . $row['nama_desa'] . "<br>";
            
            // Restore
            mysqli_query($conn, "UPDATE pengaturan SET nama_desa = 'Desa Lengkong' WHERE id = 1");
        } else {
            echo "✗ Update test gagal: " . mysqli_error($conn) . "<br>";
        }
        
    } else {
        echo "✗ Koneksi database gagal<br>";
    }
}
?>

<form method="POST">
    <input type="text" name="test_field" value="Test Value" required>
    <button type="submit" class="btn btn-primary">Test Submit</button>
</form>