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

// Mulai session
session_start();
$user_id = 3; // Ganti dengan $_SESSION['user_id'] setelah pengguna login

// Daftar permohonan
$sql = "SELECT * FROM permohonan";
$permohonan_result = $conn->query($sql);

// Verifikasi permohonan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verifikasi_permohonan"])) {
    $permohonan_id = intval($_POST["permohonan_id"]);
    $status = $conn->real_escape_string($_POST["status"]);
    $komentar = $conn->real_escape_string($_POST["komentar"]);

    $sql_update = "UPDATE permohonan SET status='$status', komentar='$komentar' WHERE id='$permohonan_id'";
    
    if ($conn->query($sql_update) === TRUE) {
        // Jika update berhasil, lakukan insert ke tabel penyaluran
        if ($status === 'Diterima') {
            $sql_insert_penyaluran = "INSERT INTO penyaluran (permohonan_id, user_id, nama, alamat, no_telp, email, jumlah_anggota_keluarga, kondisi_ekonomi, dokumen_pendukung, status, komentar) 
                                      SELECT id, user_id, nama, alamat, no_telp, email, jumlah_anggota_keluarga, kondisi_ekonomi, dokumen_pendukung, 'Proses', 'Permohonan baru diterima'
                                      FROM permohonan WHERE id='$permohonan_id'";
            if ($conn->query($sql_insert_penyaluran) === TRUE) {
                echo "<div class='alert alert-success' role='alert'>Permohonan berhasil diverifikasi dan data penyaluran berhasil ditambahkan</div>";
            } else {
                echo "Error: " . $sql_insert_penyaluran . "<br>" . $conn->error;
            }
        } else {
            echo "<div class='alert alert-success' role='alert'>Permohonan berhasil diverifikasi</div>";
        }
    } else {
        echo "Error: " . $sql_update . "<br>" . $conn->error;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dinas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #06BBCC;
        }
        .navbar .navbar-brand {
            font-weight: bold;
            color: #fff;
        }
        .navbar .btn-danger {
            background-color: #dc3545;
        }
        .sidebar {
            height: calc(100vh - 56px);
            padding-top: 20px;
            background-color: #fff;
            border-right: 1px solid #ddd;
        }
        .sidebar .nav-link {
            color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link.active {
            background-color: #06BBCC;
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
        }
        .tab-content {
            padding: 20px;
        }
        .table-heading th {
            background-color: #06BBCC;
            color: #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        .custom-h3 {
            padding-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">SIPS Dinas</a>
            <div class="d-flex">
                <a href="index.html" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#daftar-permohonan">Daftar Permohonan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#verifikasi">Verifikasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#evaluasi">Evaluasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#laporan">Laporan</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="daftar-permohonan">
                        <h3 class="text-center custom-h3">Daftar Permohonan</h3>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr class="table-heading">
                                        <th>ID</th>
                                        <th>No.</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>No. Telepon</th>
                                        <th>Email</th>
                                        <th>Jumlah Anggota Keluarga</th>
                                        <th>Kondisi Ekonomi</th>
                                        <th>Dokumen Pendukung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($permohonan_result->num_rows > 0) {
                                        $no = 1;
                                        while ($permohonan = $permohonan_result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($permohonan["id"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . $no++ . "</td>";
                                            echo "<td>" . htmlspecialchars($permohonan["nama"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($permohonan["alamat"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($permohonan["no_telp"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . htmlspecialchars($permohonan["email"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td>" . intval($permohonan["jumlah_anggota_keluarga"]) . "</td>";
                                            echo "<td>" . htmlspecialchars($permohonan["kondisi_ekonomi"], ENT_QUOTES, 'UTF-8') . "</td>";
                                            echo "<td><a href='" . htmlspecialchars($permohonan["dokumen_pendukung"], ENT_QUOTES, 'UTF-8') . "' target='_blank'>Lihat Dokumen</a></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9'>Tidak ada permohonan bantuan</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="verifikasi">
                        <h3 class="text-center custom-h3">Verifikasi Permohonan</h3>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="permohonan_id" class="form-label">ID Permohonan</label>
                                <select class="form-select" id="permohonan_id" name="permohonan_id" required>
                                    <option value="" disabled selected>Pilih ID Permohonan</option>
                                    <?php
                                    // Koneksi ulang ke database untuk mendapatkan daftar ID permohonan
                                    $conn = new mysqli($servername, $username, $password, $dbname);
                                    $sql = "SELECT id FROM permohonan";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . intval($row['id']) . "'>" . intval($row['id']) . "</option>";
                                        }
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Diterima">Diterima</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="komentar" class="form-label">Komentar</label>
                                <textarea class="form-control" id="komentar" name="komentar" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="verifikasi_permohonan" class="btn btn-primary">Verifikasi</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="evaluasi">
    <h3 class="text-center custom-h3">Evaluasi</h3>
    <div class="row">
        <div class="col-md-12">
            <h4>Catatan Evaluasi Dinas</h4>
            <form id="formCatatan">
                <div class="mb-3">
                    <label for="judulCatatan" class="form-label">Judul Catatan</label>
                    <input type="text" class="form-control" id="judulCatatan" placeholder="Masukkan judul catatan" required>
                </div>
                <div class="mb-3">
                    <label for="isiCatatan" class="form-label">Isi Catatan</label>
                    <textarea class="form-control" id="isiCatatan" rows="5" placeholder="Masukkan isi catatan" required></textarea>
                </div>
                <button type="button" class="btn btn-primary" onclick="simpanCatatan()">Simpan Catatan</button>
            </form>
        </div>
    </div>
    <div class="row mt-4" id="daftarCatatan">
        <!-- Area untuk menampilkan daftar evaluasi -->
    </div>
</div><div class="tab-pane fade" id="laporan">
<h3 class="text-center custom-h3 mb-4">Laporan</h3>
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" onclick="downloadPDF()">Download Laporan PDF</button>
    </div>
    <div id="laporanContent">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Ringkasan Permohonan</h5>
                        <div class="chart-container">
                            <canvas id="ringkasanPermohonanChart" width="800" height="400"></canvas>
                        </div>
                        <p class="text-center mt-3">Grafik ini menunjukkan total permohonan dan rata-rata waktu tanggapan.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Statistik Permohonan</h5>
                        <div class="chart-container">
                            <canvas id="statistikPermohonanChart" width="800" height="400"></canvas>
                        </div>
                        <p class="text-center mt-3">Grafik ini menunjukkan perbandingan jumlah permohonan yang diterima dan ditolak.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center">Permohonan Berdasarkan Kondisi Ekonomi</h5>
                        <div class="chart-container">
                            <canvas id="permohonanKondisiEkonomiChart" width="800" height="400"></canvas>
                        </div>
                        <p class="text-center mt-3">Grafik ini menunjukkan jumlah permohonan berdasarkan kondisi ekonomi pemohon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
// Initialization of charts with the given data and options
var ctx1 = document.getElementById('ringkasanPermohonanChart').getContext('2d');
var ringkasanPermohonanChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Total Permohonan', 'Rata-rata Waktu Tanggapan (hari)'],
        datasets: [{
            label: 'Ringkasan Permohonan',
            data: [100, 4.5],
            backgroundColor: ['rgba(54, 162, 235, 0.5)', 'rgba(255, 159, 64, 0.5)'],
            borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)'],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

var ctx2 = document.getElementById('statistikPermohonanChart').getContext('2d');
var statistikPermohonanChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['Diterima', 'Ditolak'],
        datasets: [{
            label: 'Statistik Permohonan',
            data: [80, 20],
            backgroundColor: ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'],
            borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

var ctx3 = document.getElementById('permohonanKondisiEkonomiChart').getContext('2d');
var permohonanKondisiEkonomiChart = new Chart(ctx3, {
    type: 'pie',
    data: {
        labels: ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Sangat Kurang'],
        datasets: [{
            label: 'Permohonan Berdasarkan Kondisi Ekonomi',
            data: [5, 10, 15, 25, 20],
            backgroundColor: [
                'rgba(75, 192, 192, 0.5)', 'rgba(54, 162, 235, 0.5)',
                'rgba(153, 102, 255, 0.5)', 'rgba(255, 159, 64, 0.5)',
                'rgba(255, 99, 132, 0.5)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)',
                'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    }
});

// Fungsi untuk mendownload laporan sebagai PDF
async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const laporanContent = document.getElementById('laporanContent');

        // Mendapatkan elemen canvas
        const ringkasanCanvas = document.getElementById('ringkasanPermohonanChart');
        const statistikCanvas = document.getElementById('statistikPermohonanChart');
        const ekonomiCanvas = document.getElementById('permohonanKondisiEkonomiChart');

        // Tambahkan judul laporan
        doc.setFontSize(18);
        doc.text("Laporan Permohonan", 105, 20, null, null, "center");

        // Tambahkan gambar canvas ke PDF
        const ringkasanImgData = ringkasanCanvas.toDataURL('image/png');
        doc.addImage(ringkasanImgData, 'PNG', 15, 40, 180, 100);
        doc.text("Ringkasan Permohonan", 105, 150, null, null, "center");

        const statistikImgData = statistikCanvas.toDataURL('image/png');
        doc.addImage(statistikImgData, 'PNG', 15, 160, 180, 100);
        doc.text("Statistik Permohonan", 105, 270, null, null, "center");

        doc.addPage();
        const ekonomiImgData = ekonomiCanvas.toDataURL('image/png');
        doc.addImage(ekonomiImgData, 'PNG', 15, 40, 180, 100);
        doc.text("Permohonan Berdasarkan Kondisi Ekonomi", 105, 150, null, null, "center");

        // Simpan dokumen PDF
        doc.save('Laporan_Permohonan.pdf');
    }




// Array untuk menyimpan catatan evaluasi
let catatanEvaluasi = [];

// Fungsi untuk menyimpan catatan evaluasi
function simpanCatatan() {
    // Mengambil nilai dari input form
    let judul = document.getElementById('judulCatatan').value;
    let isi = document.getElementById('isiCatatan').value;

    // Validasi judul dan isi catatan tidak boleh kosong
    if (judul.trim() === '' || isi.trim() === '') {
        alert('Judul dan Isi Catatan harus diisi.');
        return;
    }

    // Membuat objek baru untuk catatan evaluasi
    let evaluasi = {
        judul: judul,
        isi: isi,
        waktu: new Date().toLocaleString() // Menambahkan waktu simpan
    };

    // Menambahkan evaluasi ke array
    catatanEvaluasi.push(evaluasi);

    // Memanggil fungsi untuk menampilkan ulang daftar evaluasi
    tampilkanDaftarEvaluasi();

    // Reset nilai input setelah menyimpan catatan
    document.getElementById('judulCatatan').value = '';
    document.getElementById('isiCatatan').value = '';

    // Memberi pesan berhasil disimpan (opsional)
    alert('Catatan evaluasi berhasil disimpan.');
}

// Fungsi untuk menampilkan daftar evaluasi
function tampilkanDaftarEvaluasi() {
    let daftarCatatanElem = document.getElementById('daftarCatatan');
    daftarCatatanElem.innerHTML = ''; // Kosongkan terlebih dahulu

    // Looping melalui array catatanEvaluasi untuk menampilkan
    catatanEvaluasi.forEach((evaluasi, index) => {
        let cardHTML = `
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">${evaluasi.judul}</h5>
                        <p class="card-text">${evaluasi.isi}</p>
                        <p class="card-text"><small class="text-muted">Waktu: ${evaluasi.waktu}</small></p>
                    </div>
                </div>
            </div>
        `;
        daftarCatatanElem.innerHTML += cardHTML;
    });
}

// Panggil fungsi tampilkanDaftarEvaluasi untuk pertama kali
tampilkanDaftarEvaluasi();
    </script>
</body>
</html>