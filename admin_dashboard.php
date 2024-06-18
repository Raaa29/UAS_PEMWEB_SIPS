<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sosial";

$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses memasukkan data ke tabel permohonan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajukan_bantuan'])) {
    $user_id = $_POST['user_id'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $email = $_POST['email'];
    $jumlah_anggota_keluarga = $_POST['jumlah_anggota_keluarga'];
    $kondisi_ekonomi = $_POST['kondisi_ekonomi'];
    $dokumen_pendukung = $_POST['dokumen_pendukung'];

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Insert ke tabel permohonan
        $sql_permohonan = "INSERT INTO permohonan (user_id, nama, alamat, no_telp, email, jumlah_anggota_keluarga, kondisi_ekonomi, dokumen_pendukung) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_permohonan = $conn->prepare($sql_permohonan);
        $stmt_permohonan->bind_param("issssiss", $user_id, $nama, $alamat, $no_telp, $email, $jumlah_anggota_keluarga, $kondisi_ekonomi, $dokumen_pendukung);
        $stmt_permohonan->execute();

        // Dapatkan ID permohonan yang baru saja dimasukkan
        $permohonan_id = $stmt_permohonan->insert_id;

        // Insert ke tabel penyaluran
        $sql_penyaluran = "INSERT INTO penyaluran (permohonan_id, user_id, nama, alamat, no_telp, email, jumlah_anggota_keluarga, kondisi_ekonomi, dokumen_pendukung, status, komentar) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Proses', 'Permohonan baru')";
        $stmt_penyaluran = $conn->prepare($sql_penyaluran);
        $stmt_penyaluran->bind_param("iissssiss", $permohonan_id, $user_id, $nama, $alamat, $no_telp, $email, $jumlah_anggota_keluarga, $kondisi_ekonomi, $dokumen_pendukung);
        $stmt_penyaluran->execute();

        // Commit transaksi
        $conn->commit();
        
        echo "Data berhasil dimasukkan.";
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Menutup statement
    $stmt_permohonan->close();
    $stmt_penyaluran->close();
}

// Query untuk mengambil data dari tabel penyaluran
$sql = "SELECT penyaluran.*, permohonan.nama AS nama_permohonan 
        FROM penyaluran 
        JOIN permohonan ON penyaluran.permohonan_id = permohonan.id";
$result = $conn->query($sql);

// Fungsi untuk mengirim notifikasi
function kirimNotifikasi($user_id, $permohonan_id, $pesan) {
    global $conn;
    $tanggal_kirim = date('Y-m-d H:i:s'); // Tanggal dan waktu pengiriman notifikasi
    $sql_notif = "INSERT INTO notifikasi (user_id, permohonan_id, pesan, created_at) VALUES ('$user_id', '$permohonan_id', '$pesan', '$tanggal_kirim')";
    return $conn->query($sql_notif);
}

// Proses pengiriman notifikasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_notifikasi'])) {
    $penyaluran_id = intval($_POST['penyaluran_id']);
    $permohonan_id = intval($_POST['permohonan_id']);
    $user_id = intval($_POST['user_id']);
    $pesan = "Bantuan sudah terkirim untuk Permohonan ID: $permohonan_id";

    if (kirimNotifikasi($user_id, $permohonan_id, $pesan)) {
        echo "<script>alert('Notifikasi berhasil dikirim');</script>";
    } else {
        echo "<script>alert('Gagal mengirim notifikasi');</script>";
    }
}
$sql_pengaduan = "SELECT * FROM pengaduan";
$result_pengaduan = $conn->query($sql_pengaduan);
// update_status.php

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $complaintId = $_POST["id"];

    // Simulasi pembaruan status
    // Anda harus menggantikan ini dengan logika yang sesuai dengan struktur aplikasi Anda
    // Misalnya, menggunakan query SQL UPDATE untuk mengubah status di database
    // Di sini kita menganggap jika currentStatus adalah 'Belum Dibaca', kita mengubahnya menjadi 'Sudah Dibaca' dan sebaliknya.
    $currentStatus = $_POST["currentStatus"];
    $newStatus = ($currentStatus === 'Belum Dibaca') ? 'Sudah Dibaca' : 'Belum Dibaca';

    // Contoh respon
    echo "Status pengaduan dengan ID $complaintId telah diubah menjadi $newStatus";
} else {
    // Jika tidak ada POST data yang valid
    http_response_code(400);
    echo "Invalid request";
}


// Menutup koneksi
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
    width: 250px;
    height: 100vh;
    background-color: #fff;
    color: black;
    position: fixed;
    top: 50px;
    left: 0;
    padding-top: 20px;
}
        .profile {
            text-align: center;
            margin-bottom: 20px;
        }
        /* Gaya untuk navigasi */
.menu {
    padding-left: 20px;
}

.menu a {
    display: block;
    color: black;
    text-decoration: none;
    margin: 15px 0;
    font-size: 18px;
    padding: 10px;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.menu a:hover {
    background-color: #06BBCC;
    color: white;
}

.menu a.active {
    background-color: #06BBCC;
    color: white;
}
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #06BBCC;
            color: white;
            position: fixed;
            width: calc(100% - 0px);
            top: 0;
            left: 0;
            box-sizing: border-box;
        }
        .header h2 {
            margin: 0;
        }
        .header .logout a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid white;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .header .logout a:hover {
            background-color: #fff;
            color: #2980b9;
        }
        .main-content {
            margin-left: 250px;
            padding: 80px 20px 20px 20px;
            background-color: #ecf0f1;
            height: 100vh;
            overflow-y: auto;
        }
        #chart-container, #complaint-container, #detail-page {
            display: none;
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #penyaluran-container {
    display: none;
    width: 100%;
    padding: 20px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    overflow: auto; /* Menambahkan overflow agar bisa di-scroll jika melebihi ukuran */
}
#penyaluran-container h3 {
    margin-bottom: 10px;
    color: #333;
}
        canvas {
            width: 100% !important;
            height: auto !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #06BBCC;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-proses {
            color: #f39c12;
        }
        .status-selesai {
            color: #27ae60;
        }
        .action-buttons a {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        .action-buttons a:hover {
            background-color: #f2f2f2;
        }
        .acc-button, .tolak-button, .kirim-button {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .acc-button {
            background-color: #4CAF50;
            color: white;
        }
        .acc-button:hover {
            background-color: #388E3C;
        }
        .tolak-button {
            background-color: #f44336;
            color: white;
        }
        .tolak-button:hover {
            background-color: #d32f2f;
        }
        .kirim-button {
            background-color: #008CBA;
            color: white;
        }
        .kirim-button:hover {
            background-color: #005f6b;
        }

.status-unread {
    color: red; /* Warna merah untuk status "Belum Dibaca" */
}

.status-read {
    color: green; /* Warna hijau untuk status "Sudah Dibaca" */
}

    </style>
</head>
<body>
    <div class="sidebar">
        <div class="profile">
        </div>
        <div class="menu">
    <a href="#" id="penyaluran-bantuan" class="nav-link">Penyaluran Bantuan</a>
    <a href="#" id="pengaduan" class="nav-link">Daftar Pengaduan</a>
    <a href="#" id="laporan" class="nav-link">Laporan</a>
</div>
    </div>
    <div class="header">
        <h2>SIPS</h2>
        <div class="logout">
            <a href="index.html">Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div id="chart-container">
            <canvas id="myChart" width="800" height="400"></canvas>
        </div>
        <div id="penyaluran-container">
        <h3>Daftar Penyaluran Bantuan</h3>
            <table id="penyaluran-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Permohonan ID</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No Telp</th>
                        <th>Email</th>
                        <th>Jumlah Anggota Keluarga</th>
                        <th>Kondisi Ekonomi</th>
                        <th>Dokumen Pendukung</th>
                        <th>Status</th>
                        <th>Komentar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["permohonan_id"] . "</td>";
                        echo "<td>" . $row["nama"] . "</td>";
                        echo "<td>" . $row["alamat"] . "</td>";
                        echo "<td>" . $row["no_telp"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["jumlah_anggota_keluarga"] . "</td>";
                        echo "<td>" . $row["kondisi_ekonomi"] . "</td>";
                        echo "<td><a href='" . $row["dokumen_pendukung"] . "' target='_blank'>Lihat Dokumen</a></td>";
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>" . $row["komentar"] . "</td>";
                        echo "<td class='action-buttons'>";
                        echo "<form method='POST' action=''>";
                        echo "<input type='hidden' name='penyaluran_id' value='" . $row["id"] . "'>";
                        echo "<input type='hidden' name='permohonan_id' value='" . $row["permohonan_id"] . "'>";
                        echo "<input type='hidden' name='user_id' value='" . $row["user_id"] . "'>";
                        echo "<button type='submit' name='kirim_notifikasi' class='kirim-button'>Kirim Bantuan</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>Tidak ada data penyaluran.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <div id="complaint-container">
    <h3>Data Pengaduan</h3>
    <table>
        <thead>
            <tr>
                <th>ID Pengaduan</th>
                <th>Nama</th>
                <th>Tanggal Pengaduan</th>
                <th>Deskripsi Pengaduan</th>
                <th>Gambar Pendukung</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
if ($result_pengaduan->num_rows > 0) {
    while ($row = $result_pengaduan->fetch_assoc()) {
        $gambar_url = htmlspecialchars($row['gambar_pendukung'], ENT_QUOTES, 'UTF-8');
        
        // Ubah status menjadi huruf kecil
        $status = isset($row['status']) ? strtolower($row['status']) : 'Belum Dibaca';
        
        // Tentukan kelas berdasarkan status
        $status_class = ($status === 'sudah dibaca') ? 'status-read' : 'status-unread';

        echo "<tr data-id='{$row['id']}'>
                <td>{$row['id']}</td>
                <td>{$row['nama_pengadu']}</td>
                <td>{$row['tanggal_pengaduan']}</td>
                <td>{$row['deskripsi_pengaduan']}</td>
                <td><a href='$gambar_url' target='_blank'>Lihat Gambar</a></td>
                <td class='status-column'><span class='status-text $status_class'>$status</span></td>
                <td><button class='view-btn' data-id='{$row['id']}' data-status='$status'>View</button></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>Tidak ada data pengaduan</td></tr>";
}
?>
        </tbody>
    </table>
</div>



<div id="detail-page">
    <h3>Detail Pengaduan</h3>
    <p><strong>Tanggal:</strong> <span id="detail-tanggal"></span></p>
    <p><strong>Nama:</strong> <span id="detail-nama"></span></p>
    <p><strong>Deskripsi:</strong> <span id="detail-deskripsi"></span></p>
    <p><strong>Status:</strong> <span id="detail-status"></span></p>
    <p><strong>Respon:</strong></p>
    <p id="detail-content"></p>
    <form id="response-form">
        <textarea id="response" name="response" rows="4" cols="50"></textarea>
        <button type="submit">Kirim</button>
    </form>
    <button id="back-button">Kembali</button>
</div>

    <script>
        const penyaluranBtn = document.getElementById("penyaluran-bantuan");
        const pengaduanBtn = document.getElementById("pengaduan");
        const laporanBtn = document.getElementById("laporan");
        const chartContainer = document.getElementById("chart-container");
        const complaintContainer = document.getElementById("complaint-container");
        const penyaluranContainer = document.getElementById("penyaluran-container");
        const detailPage = document.getElementById("detail-page");
        const detailTanggal = document.getElementById("detail-tanggal");
        const detailNama = document.getElementById("detail-nama");
        const detailDeskripsi = document.getElementById("detail-deskripsi");
        const detailStatus = document.getElementById("detail-status");
        const detailContent = document.getElementById("detail-content");
        const responseForm = document.getElementById("response-form");
        const backButton = document.getElementById("back-button");

        penyaluranBtn.addEventListener("click", () => {
            hideAll();
            penyaluranContainer.style.display = "block";
        });

        pengaduanBtn.addEventListener("click", () => {
            hideAll();
            complaintContainer.style.display = "block";
        });

        laporanBtn.addEventListener("click", () => {
            hideAll();
            chartContainer.style.display = "block";
        });

        document.querySelectorAll(".detail-button").forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                showDetail(button.closest("tr"));
            });
        });

        backButton.addEventListener("click", () => {
            detailPage.style.display = "none";
            complaintContainer.style.display = "block";
        });

        responseForm.addEventListener("submit", (e) => {
            e.preventDefault();
            // Process form submission here
            alert("Response submitted!");
        });

        function hideAll() {
            chartContainer.style.display = "none";
            complaintContainer.style.display = "none";
            penyaluranContainer.style.display = "none";
            detailPage.style.display = "none";
        }

        function showDetail(row) {
            detailTanggal.innerText = row.children[0].innerText;
            detailNama.innerText = row.children[1].innerText;
            detailDeskripsi.innerText = row.children[2].innerText;
            detailStatus.innerText = row.children[3].innerText;
            // You can add content to detailContent as needed
            hideAll();
            detailPage.style.display = "block";
        }

        // Sample chart code
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    'Dukuh Pakis', 'Gayungan', 'Wiyung', 'Wonocolo', 'Wonokromo', 
                    'Karang Pilang', 'Rungkut', 'Gunung Anyar', 'Sukolilo', 
                    'Tambaksari', 'Mulyorejo', 'Tenggilis Mejoyo', 'Asem Rowo', 
                    'Lakarsantri', 'Benowo', 'Tandes', 'Sambikerep', 'Bubutan', 
                    'Genteng', 'Simokerto', 'Tegalsari', 'Bulak', 'Kenjeran', 
                    'Krembangan', 'Semampir', 'Pabean Cantian'
                ],
                datasets: [{
                    label: 'Penerima Bantuan 2023',
                    data: [
                        120, 160, 50, 95, 130, 
                        150, 140, 100, 70, 
                        30, 80, 170, 145, 
                        100, 70, 90, 50, 130, 
                        150, 90, 330, 240, 170, 
                        295, 230, 100
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(199, 199, 199, 0.2)',
                        'rgba(83, 102, 255, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(199, 199, 199, 0.2)',
                        'rgba(83, 102, 255, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
    const navLinks = document.querySelectorAll('.nav-link');

    // Menambahkan event listener untuk setiap tautan navigasi
    navLinks.forEach(function(navLink) {
        navLink.addEventListener('click', function(event) {
            // Menghapus kelas active dari semua tautan navigasi
            navLinks.forEach(function(link) {
                link.classList.remove('active');
            });

            // Menambahkan kelas active pada tautan yang sedang diklik
            this.classList.add('active');
        });
    });
    var viewButtons = document.querySelectorAll(".view-btn");

viewButtons.forEach(function(button) {
    button.addEventListener("click", function() {
        var complaintId = this.getAttribute("data-id");
        var currentStatus = this.getAttribute("data-status");

        // Cek apakah status saat ini sudah dibaca
        if (currentStatus === 'Belum Dibaca') {
            // Ubah status menjadi Sudah Dibaca di UI
            var statusColumn = document.querySelector("tr[data-id='" + complaintId + "'] .status-column");
            statusColumn.innerHTML = '<span class="status-read">Sudah Dibaca</span>';

            // Perbarui status pada tombol dengan atribut data-status
            this.setAttribute("data-status", "Sudah Dibaca");

            // Kirim permintaan AJAX untuk memperbarui status di server (opsional)
            markAsRead(complaintId);
        }
    });
});

function markAsRead(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Handle response if needed
                var response = xhr.responseText;
                console.log(response); // Log response for debugging
            } else {
                console.error("Failed to update status");
            }
        }
    };
    xhr.send("id=" + encodeURIComponent(id));
}

});

    </script>
</body>
</html>