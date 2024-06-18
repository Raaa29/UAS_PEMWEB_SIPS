<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sosial";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil user_id dari session
session_start();
$user_id = 1; // Ganti dengan $_SESSION['user_id'] setelah pengguna login

// Ambil notifikasi dari database
$sql = "SELECT * FROM notifikasi WHERE user_id = $user_id ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='alert alert-success' role='alert'>" . htmlspecialchars($row["pesan"], ENT_QUOTES, 'UTF-8') . "</div>";
    }
} else {
    echo "<p>Tidak ada notifikasi baru.</p>";
}

$conn->close();