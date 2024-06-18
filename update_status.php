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

// Ambil ID dari permintaan AJAX
$id = $_POST["id"];

// Perbarui status penyaluran menjadi "Selesai"
$sql = "UPDATE penyaluran SET status = 'Selesai' WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    // Kirim notifikasi ke user
    $user_id = getUserIdFromPenyaluranId($conn, $id);
    $notifikasi = "Bantuan dengan ID $id telah dikirim.";
    $sql_notifikasi = "INSERT INTO notifikasi (user_id, pesan) VALUES ($user_id, '$notifikasi')";
    $conn->query($sql_notifikasi);
    echo "Bantuan dengan ID $id telah dikirim.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

function getUserIdFromPenyaluranId($conn, $penyaluran_id) {
    $sql = "SELECT user_id FROM penyaluran WHERE id = $penyaluran_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["user_id"];
    }
    return null;
}