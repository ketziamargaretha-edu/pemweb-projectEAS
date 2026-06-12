<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireSupplier();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($koneksi, "SELECT * FROM produk_tekstil WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: tables.php");
    exit();
}

$pesan = '';

// Konversi RGB ke kategori warna
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

function warnaNameToHex($warna) {
    $map = [
        'Merah' => '#FF0000', 'Hijau' => '#00FF00', 'Biru' => '#0000FF',
        'Kuning' => '#FFFF00', 'Ungu' => '#FF00FF', 'Orange' => '#FF6600',
        'Pink' => '#FFC0CB', 'Hitam' => '#000000', 'Putih' => '#FFFFFF',
        'Abu-abu' => '#808080', 'Coklat' => '#8B4513', 'Emas' => '#FFD700',
        'Perak' => '#C0C0C0', 'Tosca' => '#40E0D0', 'Marun' => '#800000',
        'Navy' => '#000080', 'Mint' => '#98FB98', 'Salmon' => '#FA8072',
        'Lavender' => '#E6E6FA', 'Beige' => '#F5F5DC', 'Other' => '#CCCCCC'
    ];
    foreach ($map as $key => $hex) {
        if (stripos($warna, $key) !== false) {
            return $hex;
        }
    }
    return '#FF0000';
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

$jenis_kain_list = [
    'Katun', 'Sutra', 'Denim', 'Wolfis', 'Tile', 'Linen', 'Polyester',
    'Spandek', 'Baby Doll', 'Ceruti', 'Viscose', 'Chiffon', 'Satin'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $jenis_kain = mysqli_real_escape_string($koneksi, $_POST['jenis_kain']);
    $warna_hex = $_POST['warna_hex'];
    $ukuran = mysqli_real_escape_string($koneksi, $_POST['ukuran']);
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    $supplier = mysqli_real_escape_string($koneksi, $_POST['supplier']);
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $provinsi = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    
    $warna = hexToWarnaName($warna_hex);
    
    $gambar = $data['gambar'];
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $nama_file = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $nama_file;
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['gambar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                if (!empty($data['gambar']) && file_exists($data['gambar'])) {
                    unlink($data['gambar']);
                }
                $gambar = $target_file;
            }
        }
    }
    
    $stmt = mysqli_prepare($koneksi, "UPDATE produk_tekstil SET nama_produk=?, jenis_kain=?, warna=?, ukuran=?, stok=?, harga=?, supplier=?, tanggal_masuk=?, gambar=?, provinsi=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssiissssi", $nama_produk, $jenis_kain, $warna, $ukuran, $stok, $harga, $supplier, $tanggal_masuk, $gambar, $provinsi, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $pesan = '<div class="alert alert-success">Data berhasil diupdate!</div>';
        echo "<meta http-equiv='refresh' content='2;url=tables.php'>";
    } else {
        $pesan = '<div class="alert alert-danger">Error: ' . mysqli_error($koneksi) . '</div>';
    }
    mysqli_stmt_close($stmt);
}

$warna_hex_awal = warnaNameToHex($data['warna']);
$warna_kategori_awal = hexToWarnaName($warna_hex_awal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .preview-img { max-width: 150px; max-height: 150px; margin-top: 10px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h4><i class="fas fa-edit"></i> Edit Produk</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $pesan; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nama Produk</label>
                                    <input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($data['nama_produk']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Jenis Kain</label>
                                    <select name="jenis_kain" class="form-select" required>
                                        <?php foreach($jenis_kain_list as $jk): ?>
                                            <option value="<?php echo $jk; ?>" <?php echo ($data['jenis_kain'] == $jk) ? 'selected' : ''; ?>><?php echo $jk; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Pilih Warna -->
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">🎨 Pilih Warna <span class="text-danger">*</span></label>
                                    <div class="card bg-light p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <input type="color" name="warna_hex" id="colorPicker" class="form-control form-control-color" style="width: 100%; height: 50px; padding: 2px; cursor: pointer;" value="<?php echo $warna_hex_awal; ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <div id="warnaPreview" class="rounded shadow-sm" style="width: 50px; height: 50px; background-color: <?php echo $warna_hex_awal; ?>; border: 2px solid #fff;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="alert alert-success mb-0 py-2">
                                                    <i class="fas fa-tag"></i> <strong>Kategori Warna:</strong> 
                                                    <span id="warnaKategori" class="badge bg-primary fs-6 px-3 py-2"><?php echo $warna_kategori_awal; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2"><i class="fas fa-info-circle"></i> Pilih warna dari palette, sistem akan menentukan kategori warna secara otomatis</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Ukuran</label>
                                    <input type="text" name="ukuran" class="form-control" value="<?php echo htmlspecialchars($data['ukuran']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Stok</label>
                                    <input type="number" name="stok" class="form-control" value="<?php echo $data['stok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" value="<?php echo $data['harga']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Supplier</label>
                                    <input type="text" name="supplier" class="form-control" value="<?php echo htmlspecialchars($data['supplier']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Tanggal Masuk</label>
                                    <input type="date" name="tanggal_masuk" class="form-control" value="<?php echo $data['tanggal_masuk']; ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Provinsi Supplier</label>
                                    <select name="provinsi" class="form-select">
                                        <option value="">-- Pilih Provinsi --</option>
                                        <?php foreach($daftar_provinsi as $prov): ?>
                                            <option value="<?php echo $prov; ?>" <?php echo ($data['provinsi'] == $prov) ? 'selected' : ''; ?>><?php echo $prov; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Foto Produk Saat Ini</label><br>
                                    <?php if(!empty($data['gambar']) && file_exists($data['gambar'])): ?>
                                        <img src="<?php echo $data['gambar']; ?>" width="100" class="mb-2">
                                    <?php else: ?>
                                        <p class="text-muted">Tidak ada gambar</p>
                                    <?php endif; ?>
                                    <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event)">
                                    <small>Kosongkan jika tidak ingin mengubah gambar</small>
                                    <div id="preview" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save"></i> Update</button>
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
        
        if (colorPicker) {
            colorPicker.addEventListener('input', function() {
                const warna = hexToWarnaName(this.value);
                if (warnaPreview) warnaPreview.style.backgroundColor = this.value;
                if (warnaKategori) warnaKategori.textContent = warna;
            });
        }
        
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