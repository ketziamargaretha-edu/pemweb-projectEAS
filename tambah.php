<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireSupplier();

$pesan = '';

// Daftar kategori warnna
$kategori_warna = [
    'Merah', 'Hijau', 'Biru', 'Kuning', 'Ungu', 'Orange', 'Pink',
    'Hitam', 'Putih', 'Abu-abu', 'Coklat', 'Emas', 'Perak', 'Tosca',
    'Marun', 'Navy', 'Mint', 'Salmon', 'Lavender', 'Beige', 'Other'
];

function hexToWarnaName($hex) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    if ($r < 50 && $g < 50 && $b < 50) return 'Hitam';
    if ($r > 200 && $g > 200 && $b > 200) return 'Putih';
    
    if ($r > 100 && $r < 200 && $g > 100 && $g < 200 && $b > 100 && $b < 200) {
        if ($r > 150 && $g > 150 && $b > 150) return 'Perak';
        return 'Abu-abu';
    }
    
    if ($r > 200 && $g > 150 && $b < 100) return 'Emas';
    if ($r > 150 && $g > 80 && $g < 150 && $b < 100) return 'Coklat';
    if ($r < 100 && $g > 150 && $b > 150) return 'Tosca';
    if ($r < 100 && $g < 100 && $b > 100 && $b < 200) return 'Navy';
    if ($r > 100 && $r < 200 && $g < 80 && $b < 80) return 'Marun';
    if ($r < 100 && $g > 150 && $g < 220 && $b > 100 && $b < 180) return 'Mint';
    if ($r > 200 && $g > 100 && $g < 180 && $b > 80 && $b < 150) return 'Salmon';
    if ($r > 150 && $r < 220 && $g < 150 && $b > 180) return 'Lavender';
    if ($r > 200 && $g > 180 && $b > 150 && $b < 200) return 'Beige';
    
    if ($r > 200 && $g < 100 && $b < 100) {
        if ($g > 50 && $b > 50) return 'Pink';
        return 'Merah';
    }
    if ($g > 200 && $r < 100 && $b < 100) return 'Hijau';
    if ($b > 200 && $r < 100 && $g < 100) return 'Biru';
    if ($r > 200 && $g > 200 && $b < 100) return 'Kuning';
    if ($r > 200 && $g < 100 && $b > 200) return 'Ungu';
    if ($r > 200 && $g > 100 && $g < 200 && $b < 100) return 'Orange';
    if ($r > 200 && $g < 150 && $b > 150) return 'Pink';
    
    if ($r > $g && $r > $b) return 'Merah';
    if ($g > $r && $g > $b) return 'Hijau';
    if ($b > $r && $b > $g) return 'Biru';
    return 'Other';
}

$daftar_provinsi = [
    'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau',
    'Jambi', 'Bengkulu', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Lampung',
    'DKI Jakarta', 'Jawa Barat', 'Banten', 'Jawa Tengah', 'DI Yogyakarta',
    'Jawa Timur', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
    'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
    'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Sulawesi Barat',
    'Gorontalo', 'Maluku', 'Maluku Utara', 'Papua Barat', 'Papua', 'Papua Tengah', 'Papua Pegunungan', 'Papua Selatan'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $jenis_kain = mysqli_real_escape_string($koneksi, $_POST['jenis_kain']);
    $warna_hex = $_POST['warna_hex'];
    $ukuran = mysqli_real_escape_string($koneksi, $_POST['ukuran']);
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $provinsi = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    
    $warna = hexToWarnaName($warna_hex);
    $supplier = $_SESSION['nama_lengkap'];
    
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        $nama_file = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $nama_file;
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $target_file;
            }
        }
    }
    
    $stmt = mysqli_prepare($koneksi, "INSERT INTO produk_tekstil (nama_produk, jenis_kain, warna, ukuran, stok, harga, supplier, tanggal_masuk, gambar, provinsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssiissss", $nama_produk, $jenis_kain, $warna, $ukuran, $stok, $harga, $supplier, $tanggal_masuk, $gambar, $provinsi);
    
    if (mysqli_stmt_execute($stmt)) {
        $pesan = '<div class="alert alert-success">Data berhasil ditambahkan!</div>';
        echo "<meta http-equiv='refresh' content='2;url=tables.php'>";
    } else {
        $pesan = '<div class="alert alert-danger">Error: ' . mysqli_error($koneksi) . '</div>';
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card-form { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .preview-img { max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-form">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-plus-circle"></i> Tambah Produk Tekstil Baru</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $pesan; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Produk *</label>
                                    <input type="text" name="nama_produk" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Jenis Kain *</label>
                                    <select name="jenis_kain" class="form-select" required>
                                        <option value="">Pilih Jenis Kain</option>
                                        <option value="Katun">Katun</option>
                                        <option value="Sutra">Sutra</option>
                                        <option value="Denim">Denim</option>
                                        <option value="Wolfis">Wolfis</option>
                                        <option value="Tile">Tile</option>
                                        <option value="Linen">Linen</option>
                                        <option value="Polyester">Polyester</option>
                                        <option value="Spandek">Spandek</option>
                                        <option value="Baby Doll">Baby Doll</option>
                                        <option value="Ceruti">Ceruti</option>
                                        <option value="Viscose">Viscose</option>
                                        <option value="Chiffon">Chiffon</option>
                                        <option value="Satin">Satin</option>
                                    </select>
                                </div>
                                
                                <!-- Pilih Warna -->
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Pilih Warna <span class="text-danger">*</span></label>
                                    <div class="card bg-light p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <input type="color" name="warna_hex" id="colorPicker" class="form-control form-control-color" style="width: 100%; height: 50px; padding: 2px; cursor: pointer;" value="#FF0000" required>
                                            </div>
                                            <div class="col-md-3">
                                                <div id="warnaPreview" class="rounded shadow-sm" style="width: 50px; height: 50px; background-color: #FF0000; border: 2px solid #fff;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-success mb-0 py-2">
                                                    <i class="fas fa-tag"></i> <strong>Kategori Warna:</strong> 
                                                    <span id="warnaKategori" class="badge bg-primary fs-6 px-3 py-2">Merah</span>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2"><i class="fas fa-info-circle"></i> Pilih warna dari palette, sistem akan menentukan kategori warna secara otomatis</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Ukuran</label>
                                    <input type="text" name="ukuran" class="form-control" placeholder="Contoh: 100 meter, Roll, L, XL">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stok *</label>
                                    <input type="number" name="stok" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Harga (Rp) *</label>
                                    <input type="number" name="harga" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Tanggal Masuk *</label>
                                    <input type="date" name="tanggal_masuk" class="form-control" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Provinsi Supplier</label>
                                    <select name="provinsi" class="form-select">
                                        <option value="">-- Pilih Provinsi --</option>
                                        <?php foreach($daftar_provinsi as $prov): ?>
                                            <option value="<?php echo $prov; ?>"><?php echo $prov; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Foto Produk</label>
                                    <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event)">
                                    <small>Format: JPG, PNG, GIF</small>
                                    <div id="preview" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> Simpan</button>
                                <a href="tables.php" class="btn btn-secondary btn-lg">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function hexToWarnaName(hex) {
            hex = hex.replace('#', '');
            let r = parseInt(hex.substring(0, 2), 16);
            let g = parseInt(hex.substring(2, 4), 16);
            let b = parseInt(hex.substring(4, 6), 16);
            
            if (r < 50 && g < 50 && b < 50) return 'Hitam';
            if (r > 200 && g > 200 && b > 200) return 'Putih';
            
            if (r > 100 && r < 200 && g > 100 && g < 200 && b > 100 && b < 200) {
                if (r > 150 && g > 150 && b > 150) return 'Perak';
                return 'Abu-abu';
            }
            
            if (r > 200 && g > 150 && b < 100) return 'Emas';
            if (r > 150 && g > 80 && g < 150 && b < 100) return 'Coklat';
            if (r < 100 && g > 150 && b > 150) return 'Tosca';
            if (r < 100 && g < 100 && b > 100 && b < 200) return 'Navy';
            if (r > 100 && r < 200 && g < 80 && b < 80) return 'Marun';
            if (r < 100 && g > 150 && g < 220 && b > 100 && b < 180) return 'Mint';
            if (r > 200 && g > 100 && g < 180 && b > 80 && b < 150) return 'Salmon';
            if (r > 150 && r < 220 && g < 150 && b > 180) return 'Lavender';
            if (r > 200 && g > 180 && b > 150 && b < 200) return 'Beige';
            
            if (r > 200 && g < 100 && b < 100) {
                if (g > 50 && b > 50) return 'Pink';
                return 'Merah';
            }
            if (g > 200 && r < 100 && b < 100) return 'Hijau';
            if (b > 200 && r < 100 && g < 100) return 'Biru';
            if (r > 200 && g > 200 && b < 100) return 'Kuning';
            if (r > 200 && g < 100 && b > 200) return 'Ungu';
            if (r > 200 && g > 100 && g < 200 && b < 100) return 'Orange';
            if (r > 200 && g < 150 && b > 150) return 'Pink';
            
            if (r > g && r > b) return 'Merah';
            if (g > r && g > b) return 'Hijau';
            if (b > r && b > g) return 'Biru';
            return 'Other';
        }
        
        const colorPicker = document.getElementById('colorPicker');
        const warnaPreview = document.getElementById('warnaPreview');
        const warnaKategori = document.getElementById('warnaKategori');
        
        colorPicker.addEventListener('input', function() {
            const warna = hexToWarnaName(this.value);
            warnaPreview.style.backgroundColor = this.value;
            warnaKategori.textContent = warna;
        });
        
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview').innerHTML = '<img src="' + reader.result + '" class="preview-img">';
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>