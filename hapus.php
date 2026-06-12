<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireSupplier();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $query_cek_pesanan = "SELECT t.id, peng.status 
                          FROM transaksi t 
                          LEFT JOIN pengiriman peng ON t.id = peng.id_transaksi
                          WHERE t.id_produk = $id 
                          AND peng.status NOT IN ('selesai', 'batal')
                          AND peng.status IS NOT NULL";
    $result_cek = mysqli_query($koneksi, $query_cek_pesanan);
    
    if (mysqli_num_rows($result_cek) > 0) {
        // Ada pesanan yang belum selesai
        echo "<script>
            alert('Tidak dapat menghapus produk! Masih ada pesanan yang belum selesai untuk produk ini.');
            window.location.href = 'tables.php';
        </script>";
        exit();
    }
    
    $query_gambar = "SELECT gambar FROM produk_tekstil WHERE id = $id";
    $result = mysqli_query($koneksi, $query_gambar);
    $data = mysqli_fetch_assoc($result);
    
    // Hapus file gambar
    if (!empty($data['gambar']) && file_exists($data['gambar'])) {
        unlink($data['gambar']);
    }
    
    // Mencari id_transaksi dari produk yang akan dihapus
    $query_cari_transaksi = "SELECT id FROM transaksi WHERE id_produk = $id";
    $result_transaksi = mysqli_query($koneksi, $query_cari_transaksi);
    
    while($row_transaksi = mysqli_fetch_assoc($result_transaksi)) {
        $id_transaksi = $row_transaksi['id'];
        
        // Menghapus data pengiriman yang terkait
        $query_hapus_pengiriman = "DELETE FROM pengiriman WHERE id_transaksi = $id_transaksi";
        mysqli_query($koneksi, $query_hapus_pengiriman);
    }
    
    // Menghapus data transaksi
    $query_hapus_transaksi = "DELETE FROM transaksi WHERE id_produk = $id";
    mysqli_query($koneksi, $query_hapus_transaksi);
    
    // Menghapus produk
    $stmt = mysqli_prepare($koneksi, "DELETE FROM produk_tekstil WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: tables.php");
exit();
?>