<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requirePembeli();

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pesan = '';

$query = "SELECT * FROM produk_tekstil WHERE id = $id_produk";
$result = mysqli_query($koneksi, $query);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    header("Location: tables.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = (int)$_POST['jumlah'];
    $id_pembeli = $_SESSION['user_id'];
    
    if ($jumlah <= $produk['stok']) {

        $stok_baru = $produk['stok'] - $jumlah;
        mysqli_query($koneksi, "UPDATE produk_tekstil SET stok = $stok_baru WHERE id = $id_produk");

        $total_harga = $jumlah * $produk['harga'];
        
        $query_transaksi = "INSERT INTO transaksi (id_produk, id_pembeli, jumlah, harga_satuan, total_harga, tanggal_transaksi) 
                            VALUES ($id_produk, $id_pembeli, $jumlah, {$produk['harga']}, $total_harga, NOW())";
        mysqli_query($koneksi, $query_transaksi);
        $id_transaksi = mysqli_insert_id($koneksi);

        $query_supplier = "SELECT id FROM users WHERE nama_lengkap = '{$produk['supplier']}' AND role = 'supplier' LIMIT 1";
        $result_supplier = mysqli_query($koneksi, $query_supplier);
        
        if (mysqli_num_rows($result_supplier) > 0) {
            $supplier = mysqli_fetch_assoc($result_supplier);
            $id_supplier = $supplier['id'];
            
            $query_pengiriman = "INSERT INTO pengiriman (id_transaksi, id_supplier, status, updated_at) 
                                 VALUES ($id_transaksi, $id_supplier, 'pending', NOW())";
            mysqli_query($koneksi, $query_pengiriman);
        }

        $pesan = '<div class="alert alert-success">✅ Pembelian berhasil! Terima kasih.</div>';
        echo "<meta http-equiv='refresh' content='2;url=tables.php'>";
    } else {
        $pesan = '<div class="alert alert-danger">Stok tidak mencukupi!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Beli Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>Form Pembelian</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $pesan; ?>
                        
                        <h5><?php echo $produk['nama_produk']; ?></h5>
                        <p>Harga: Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
                        <p>Stok tersedia: <?php echo $produk['stok']; ?></p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Jumlah Beli</label>
                                <input type="number" name="jumlah" class="form-control" min="1" max="<?php echo $produk['stok']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success">Beli Sekarang</button>
                            <a href="tables.php" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>