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
$user_id = 1; // Ganti dengan $_SESSION['user_id'] setelah pengguna login



// Ambil data profil dari database
$sql = "SELECT nama, alamat, no_telp, email FROM pengguna WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $nama = $user['nama'];
    $alamat = $user['alamat'];
    $no_telp = $user['no_telp'];
    $email = $user['email'];
} else {
    // Ambil data dari session jika ada
    $nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
    $alamat = isset($_SESSION['alamat']) ? $_SESSION['alamat'] : '';
    $no_telp = isset($_SESSION['no_telp']) ? $_SESSION['no_telp'] : '';
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
}

// Cek apakah form profil telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profil"])) {
    $nama = $conn->real_escape_string($_POST["nama"]);
    $alamat = $conn->real_escape_string($_POST["alamat"]);
    $no_telp = $conn->real_escape_string($_POST["no_telp"]);
    $email = $conn->real_escape_string($_POST["email"]);

    // Update data profil di tabel pengguna dalam database sosial
    $sql = "UPDATE pengguna SET nama='$nama', alamat='$alamat', no_telp='$no_telp', email='$email' WHERE id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success' role='alert'>Profil berhasil diperbarui</div>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Cek apakah form permohonan bantuan telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ajukan_permohonan"])) {
    $nama = $conn->real_escape_string($_POST["nama"]);
    $alamat = $conn->real_escape_string($_POST["alamat"]);
    $no_telp = $conn->real_escape_string($_POST["no_telp"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $jumlah_anggota_keluarga = intval($_POST["jumlah_anggota_keluarga"]);
    $kondisi_ekonomi = $conn->real_escape_string($_POST["kondisi_ekonomi"]);

    // Simpan data input ke session
    $_SESSION["nama"] = $nama;
    $_SESSION["alamat"] = $alamat;
    $_SESSION["no_telp"] = $no_telp;
    $_SESSION["email"] = $email;
    $_SESSION["jumlah_anggota_keluarga"] = $jumlah_anggota_keluarga;
    $_SESSION["kondisi_ekonomi"] = $kondisi_ekonomi;

    
    $dokumen_dir = "dokumen/";
    if (!is_dir($dokumen_dir)) {
        mkdir($dokumen_dir, 0777, true);
    }

    // Upload dokumen pendukung
    if (isset($_FILES["dokumen_pendukung"]) && $_FILES["dokumen_pendukung"]["error"] == 0) {
        $dokumen_pendukung = $_FILES["dokumen_pendukung"];
        $dokumen_name = basename($dokumen_pendukung["name"]);
        $dokumen_tmp = $dokumen_pendukung["tmp_name"];
        $dokumen_path = "dokumen/" . $dokumen_name;

        if (move_uploaded_file($dokumen_tmp, $dokumen_path)) {
            // Simpan data permohonan ke database
            $sql = "INSERT INTO permohonan (user_id, nama, alamat, no_telp, email, jumlah_anggota_keluarga, kondisi_ekonomi, dokumen_pendukung, status) VALUES ('$user_id', '$nama', '$alamat', '$no_telp', '$email', '$jumlah_anggota_keluarga', '$kondisi_ekonomi', '$dokumen_path', 'Belum Diproses')";

            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success' role='alert'>Permohonan bantuan berhasil diajukan</div>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>Gagal mengunggah dokumen pendukung</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Dokumen pendukung tidak ditemukan atau ada kesalahan dalam pengiriman</div>";
    }
}

// Cek apakah permohonan perlu diupdate
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_permohonan"])) {
    $permohonan_id = intval($_POST["permohonan_id"]);

    // Cek status permohonan sebelum diupdate
    $sql = "SELECT status FROM permohonan WHERE id='$permohonan_id' AND user_id='$user_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $permohonan = $result->fetch_assoc();
        if ($permohonan['status'] === 'Belum Diproses') {
            $nama = $conn->real_escape_string($_POST["nama"]);
            $alamat = $conn->real_escape_string($_POST["alamat"]);
            $no_telp = $conn->real_escape_string($_POST["no_telp"]);
            $email = $conn->real_escape_string($_POST["email"]);
            $jumlah_anggota_keluarga = intval($_POST["jumlah_anggota_keluarga"]);
            $kondisi_ekonomi = $conn->real_escape_string($_POST["kondisi_ekonomi"]);

            // Update data permohonan di database
            $sql = "UPDATE permohonan SET nama='$nama', alamat='$alamat', no_telp='$no_telp', email='$email', jumlah_anggota_keluarga='$jumlah_anggota_keluarga', kondisi_ekonomi='$kondisi_ekonomi' WHERE id='$permohonan_id' AND user_id='$user_id'";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success' role='alert'>Permohonan berhasil diperbarui</div>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "<div class='alert alert-warning' role='alert'>Permohonan sudah diproses dan tidak dapat diubah</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Permohonan tidak ditemukan</div>";
    }
}

// Cek apakah permohonan perlu dihapus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_permohonan"])) {
    $permohonan_id = intval($_POST["permohonan_id"]);

    // Cek status permohonan sebelum dihapus
    $sql = "SELECT status FROM permohonan WHERE id='$permohonan_id' AND user_id='$user_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $permohonan = $result->fetch_assoc();
        if ($permohonan['status'] === 'Belum Diproses') {
            // Hapus data penyaluran yang terkait
            $sql = "DELETE FROM penyaluran WHERE permohonan_id='$permohonan_id'";
            $conn->query($sql);

            // Hapus data permohonan dari database
            $sql = "DELETE FROM permohonan WHERE id='$permohonan_id' AND user_id='$user_id'";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success' role='alert'>Permohonan berhasil dihapus</div>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "<div class='alert alert-warning' role='alert'>Permohonan sudah diproses dan tidak dapat dihapus</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Permohonan tidak ditemukan</div>";
    }
}

// Cek apakah formulir pengaduan telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["kirim_pengaduan"])) {
    $tanggal_pengaduan = $_POST["tanggal_pengaduan"];
    $nama_pengadu = $conn->real_escape_string($_POST["nama_pengadu"]);
    $deskripsi_pengaduan = $conn->real_escape_string($_POST["deskripsi_pengaduan"]);

    // Direktori tempat Anda ingin menyimpan gambar (menggunakan path relatif)
    $upload_dir = "uploads/";

    // Pastikan direktori upload sudah ada, jika belum buat
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Buat direktori secara rekursif
    }

    // Upload gambar pendukung
if (isset($_FILES["gambar_pendukung"]) && $_FILES["gambar_pendukung"]["error"] == 0) {
    $gambar_pendukung = $_FILES["gambar_pendukung"];
    $gambar_name = basename($gambar_pendukung["name"]);
    $gambar_tmp = $gambar_pendukung["tmp_name"];
    $gambar_path = "img/" . $gambar_name; // Menyimpan gambar di dalam direktori img/

    if (move_uploaded_file($gambar_tmp, $gambar_path)) {
        // Simpan data pengaduan ke database
        $sql = "INSERT INTO pengaduan (user_id, tanggal_pengaduan, nama_pengadu, deskripsi_pengaduan, gambar_pendukung) 
                VALUES ('$user_id', '$tanggal_pengaduan', '$nama_pengadu', '$deskripsi_pengaduan', '$gambar_path')";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success' role='alert'>Pengaduan berhasil dikirimkan</div>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>Gagal mengunggah gambar pendukung</div>";
    }
} else {
    echo "<div class='alert alert-danger' role='alert'>Gambar pendukung tidak ditemukan atau ada kesalahan dalam pengiriman</div>";
}
}


// Ambil status permohonan
$sql_status = "SELECT * FROM permohonan WHERE user_id = '$user_id'";
$status_result = $conn->query($sql_status);

// Ambil notifikasi
$sql_notif = "SELECT pesan, permohonan_id, created_at FROM notifikasi WHERE user_id = '$user_id'";
$notif_result = $conn->query($sql_notif);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
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
        .status-card {
            margin-top: 20px;
        }
        .status-card .card {
            border-left-width: 5px;
        }
        .status-accepted {
            border-left-color: #28a745;
        }
        .status-rejected {
            border-left-color: #dc3545;
        }
        .status-pending {
            border-left-color: #ffc107;
        }
        .profile-img {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-img img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #06BBCC;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">SIPS</a>
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
                        <a class="nav-link active" href="#" data-bs-toggle="tab" data-bs-target="#profil">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#permohonan">Permohonan Bantuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#status">Status Permohonan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#notifikasi">Notifikasi</a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link" href="#" data-bs-toggle="tab" data-bs-target="#pengaduan">Pengaduan</a>
</li>

                </ul>
            </div>
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="profil">
                        <h3>Profil</h3>
                        <div class="profile-img">
                            <!-- Tambahkan gambar profil di sini -->
                           
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Profil</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_telp" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($no_telp, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <button type="submit" name="update_profil" class="btn btn-primary">Perbarui Profil</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="permohonan">
                        <h3>Permohonan Bantuan</h3>
                        <p>Silakan lengkapi formulir berikut untuk mengajukan permohonan bantuan.</p>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="no_telp" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($no_telp, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_anggota_keluarga" class="form-label">Jumlah Anggota Keluarga</label>
                                <input type="number" class="form-control" id="jumlah_anggota_keluarga" name="jumlah_anggota_keluarga" value="<?php echo isset($_SESSION["jumlah_anggota_keluarga"]) ? intval($_SESSION["jumlah_anggota_keluarga"]) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="kondisi_ekonomi" class="form-label">Kondisi Ekonomi</label>
                                <textarea class="form-control" id="kondisi_ekonomi" name="kondisi_ekonomi" rows="3" required><?php echo isset($_SESSION["kondisi_ekonomi"]) ? htmlspecialchars($_SESSION["kondisi_ekonomi"], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="dokumen_pendukung" class="form-label">Dokumen Pendukung (Kartu Keluarga, Surat Keterangan Miskin, dll.)</label>
                                <input type="file" class="form-control" id="dokumen_pendukung" name="dokumen_pendukung" required>
                            </div>
                            <button type="submit" name="ajukan_permohonan" class="btn btn-primary">Ajukan Permohonan</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="status">
                        <h3>Status Permohonan</h3>
                        <?php
                        if ($status_result->num_rows > 0) {
                            while ($status = $status_result->fetch_assoc()) {
                                $status_class = '';
                                switch ($status['status']) {
                                    case 'Diterima':
                                        $status_class = 'status-accepted';
                                        break;
                                    case 'Ditolak':
                                        $status_class = 'status-rejected';
                                        break;
                                    default:
                                        $status_class = 'status-pending';
                                        break;
                                }
                                echo "<div class='status-card'>";
                                echo "<div class='card $status_class'>";
                                echo "<div class='card-body'>";
                                echo "<h5 class='card-title'>Permohonan No. " . $status['id'] . "</h5>";
                                echo "<p class='card-text'>Status: " . htmlspecialchars($status['status'], ENT_QUOTES, 'UTF-8') . "</p>";
                                echo "<p class='card-text'>Komentar: " . htmlspecialchars($status['komentar'], ENT_QUOTES, 'UTF-8') . "</p>";
                                echo "<button class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#updateModal" . $status['id'] . "'>Update</button> ";
                                echo "<button class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal" . $status['id'] . "'>Delete</button>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";

                                // Modal Update
                                echo "<div class='modal fade' id='updateModal" . $status['id'] . "' tabindex='-1' aria-labelledby='updateModalLabel' aria-hidden='true'>";
                                echo "<div class='modal-dialog'>";
                                echo "<div class='modal-content'>";
                                echo "<div class='modal-header'>";
                                echo "<h5 class='modal-title' id='updateModalLabel'>Update Permohonan No. " . $status['id'] . "</h5>";
                                echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                                echo "</div>";
                                echo "<div class='modal-body'>";
                                echo "<form method='post' action=''>";
                                echo "<input type='hidden' name='permohonan_id' value='" . $status['id'] . "'>";
                                echo "<div class='mb-3'>";
                                echo "<label for='nama' class='form-label'>Nama</label>";
                                echo "<input type='text' class='form-control' id='nama' name='nama' value='" . htmlspecialchars($status['nama'], ENT_QUOTES, 'UTF-8') . "' required>";
                                echo "</div>";
                                echo "<div class='mb-3'>";
                                echo "<label for='alamat' class='form-label'>Alamat</label>";
                                echo "<textarea class='form-control' id='alamat' name='alamat' rows='3' required>" . htmlspecialchars($status['alamat'], ENT_QUOTES, 'UTF-8') . "</textarea>";
                                echo "</div>";
                                echo "<div class='mb-3'>";
                                echo "<label for='no_telp' class='form-label'>No. Telepon</label>";
                                echo "<input type='text' class='form-control' id='no_telp' name='no_telp' value='" . htmlspecialchars($status['no_telp'], ENT_QUOTES, 'UTF-8') . "' required>";
                                echo "</div>";
                                echo "<div class='mb-3'>";
                                echo "<label for='email' class='form-label'>Email</label>";
                                echo "<input type='email' class='form-control' id='email' name='email' value='" . htmlspecialchars($status['email'], ENT_QUOTES, 'UTF-8') . "' required>";
                                echo "</div>";
                                echo "<div class='mb-3'>";
                                echo "<label for='jumlah_anggota_keluarga' class='form-label'>Jumlah Anggota Keluarga</label>";
                                echo "<input type='number' class='form-control' id='jumlah_anggota_keluarga' name='jumlah_anggota_keluarga' value='" . intval($status['jumlah_anggota_keluarga']) . "' required>";
                                echo "</div>";
                                echo "<div class='mb-3'>";
                                echo "<label for='kondisi_ekonomi' class='form-label'>Kondisi Ekonomi</label>";
                                echo "<textarea class='form-control' id='kondisi_ekonomi' name='kondisi_ekonomi' rows='3' required>" . htmlspecialchars($status['kondisi_ekonomi'], ENT_QUOTES, 'UTF-8') . "</textarea>";
                                echo "</div>";
                                echo "<button type='submit' name='update_permohonan' class='btn btn-primary'>Update</button>";
                                echo "</form>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";

                                // Modal Delete
                                echo "<div class='modal fade' id='deleteModal" . $status['id'] . "' tabindex='-1' aria-labelledby='deleteModalLabel' aria-hidden='true'>";
                                echo "<div class='modal-dialog'>";
                                echo "<div class='modal-content'>";
                                echo "<div class='modal-header'>";
                                echo "<h5 class='modal-title' id='deleteModalLabel'>Delete Permohonan No. " . $status['id'] . "</h5>";
                                echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                                echo "</div>";
                                echo "<div class='modal-body'>";
                                echo "<p>Apakah Anda yakin ingin menghapus permohonan ini?</p>";
                                echo "<form method='post' action=''>";
                                echo "<input type='hidden' name='permohonan_id' value='" . $status['id'] . "'>";
                                echo "<button type='submit' name='delete_permohonan' class='btn btn-danger'>Delete</button>";
                                echo "</form>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>Anda belum mengajukan permohonan.</p>";
                        }
                        ?>
                    </div>

                    <div class="tab-pane fade" id="notifikasi">
                        <h3>Notifikasi</h3>
                        <?php
                        if ($notif_result->num_rows > 0) {
                            while ($notif = $notif_result->fetch_assoc()) {
                                $tanggal_pengiriman = date('d M Y H:i:s', strtotime($notif['created_at']));
                                echo "<div class='alert alert-success' role='alert'>";
                                echo "<strong>Notifikasi:</strong> " . htmlspecialchars($notif['pesan'], ENT_QUOTES, 'UTF-8');
                                echo "<br>";
                                echo "<small class='text-muted'>Bantuan telah dikirimkan pada: " . $tanggal_pengiriman . "</small>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>Tidak ada notifikasi.</p>";
                        }
                        ?>
                    </div>
                    <div class="tab-pane fade" id="pengaduan">
    <h3>Pengaduan</h3>
    <p>Silakan isi formulir pengaduan di bawah ini.</p>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="tanggal_pengaduan" class="form-label">Tanggal Pengaduan</label>
            <input type="date" class="form-control" id="tanggal_pengaduan" name="tanggal_pengaduan" required>
        </div>
        <div class="mb-3">
            <label for="nama_pengadu" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama_pengadu" name="nama_pengadu" value="<?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi_pengaduan" class="form-label">Deskripsi Pengaduan</label>
            <textarea class="form-control" id="deskripsi_pengaduan" name="deskripsi_pengaduan" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="gambar_pendukung" class="form-label">Gambar Pendukung</label>
            <input type="file" class="form-control" id="gambar_pendukung" name="gambar_pendukung" required>
        </div>
        <button type="submit" name="kirim_pengaduan" class="btn btn-primary">Kirim Pengaduan</button>
    </form>
</div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>