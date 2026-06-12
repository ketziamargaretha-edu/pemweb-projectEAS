<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$query_total_produk = "SELECT COUNT(*) as total FROM produk_tekstil";
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total_produk))['total'];

$query_total_stok = "SELECT SUM(stok) as total_stok FROM produk_tekstil";
$total_stok = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total_stok))['total_stok'];

$query_total_supplier = "SELECT COUNT(DISTINCT supplier) as total FROM produk_tekstil WHERE supplier IS NOT NULL";
$total_supplier = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total_supplier))['total'];

$query_top = "SELECT p.nama_produk, p.jenis_kain, SUM(t.jumlah) as total_terjual 
              FROM transaksi t 
              JOIN produk_tekstil p ON t.id_produk = p.id 
              GROUP BY t.id_produk 
              ORDER BY total_terjual DESC LIMIT 5";
$top_produk = mysqli_query($koneksi, $query_top);

$query_kain = "SELECT p.jenis_kain, SUM(t.jumlah) as total_terjual 
               FROM transaksi t 
               JOIN produk_tekstil p ON t.id_produk = p.id 
               GROUP BY p.jenis_kain";
$penjualan_per_kain = mysqli_query($koneksi, $query_kain);

$labels_kain = [];
$data_terjual = [];
if (mysqli_num_rows($penjualan_per_kain) > 0) {
    mysqli_data_seek($penjualan_per_kain, 0);
    while($row = mysqli_fetch_assoc($penjualan_per_kain)) {
        $labels_kain[] = $row['jenis_kain'];
        $data_terjual[] = $row['total_terjual'];
    }
}

$query_provinsi = "SELECT provinsi, COUNT(*) as jumlah_supplier 
                   FROM produk_tekstil 
                   WHERE provinsi IS NOT NULL AND provinsi != ''
                   GROUP BY provinsi 
                   ORDER BY jumlah_supplier DESC 
                   LIMIT 5";
$top_provinsi = mysqli_query($koneksi, $query_provinsi);

$labels_provinsi = [];
$data_provinsi = [];
if (mysqli_num_rows($top_provinsi) > 0) {
    while($row = mysqli_fetch_assoc($top_provinsi)) {
        $labels_provinsi[] = $row['provinsi'];
        $data_provinsi[] = $row['jumlah_supplier'];
    }
}

$produk_menipis = [];
if (isSupplier()) {
    $query_menipis = "SELECT * FROM produk_tekstil WHERE stok < 10 ORDER BY stok ASC LIMIT 5";
    $produk_menipis = mysqli_query($koneksi, $query_menipis);
}

$produk_terbaru = [];
if (isPembeli()) {
    $query_terbaru = "SELECT * FROM produk_tekstil ORDER BY id DESC LIMIT 6";
    $produk_terbaru = mysqli_query($koneksi, $query_terbaru);
}

$stat_per_provinsi = [];

$query_cek_transaksi = "SELECT COUNT(*) as total FROM transaksi";
$result_cek = mysqli_query($koneksi, $query_cek_transaksi);
$cek_data = mysqli_fetch_assoc($result_cek);

if ($cek_data && $cek_data['total'] > 0) {
    $query_stat_provinsi = "SELECT 
                                p.provinsi,
                                p.nama_produk,
                                p.jenis_kain,
                                SUM(t.jumlah) as total_terjual
                            FROM transaksi t
                            JOIN produk_tekstil p ON t.id_produk = p.id
                            WHERE p.provinsi IS NOT NULL AND p.provinsi != ''
                            GROUP BY p.provinsi, p.id
                            ORDER BY p.provinsi, total_terjual DESC";
    
    $result_stat_provinsi = mysqli_query($koneksi, $query_stat_provinsi);
    
    if ($result_stat_provinsi && mysqli_num_rows($result_stat_provinsi) > 0) {
        while ($row = mysqli_fetch_assoc($result_stat_provinsi)) {
            $provinsi = $row['provinsi'];
            if (!isset($stat_per_provinsi[$provinsi])) {
                $stat_per_provinsi[$provinsi] = [];
            }
            if (count($stat_per_provinsi[$provinsi]) < 1) {
                $stat_per_provinsi[$provinsi][] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Tekstil</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            border-left: 4px solid;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-title { font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .stat-number { font-size: 28px; font-weight: bold; }
        .border-left-primary { border-left-color: #4e73df; }
        .border-left-success { border-left-color: #1cc88a; }
        .border-left-info { border-left-color: #36b9cc; }
        .border-left-warning { border-left-color: #f6c23e; }
        .text-primary { color: #4e73df; }
        .text-success { color: #1cc88a; }
        .text-info { color: #36b9cc; }
        .text-warning { color: #f6c23e; }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            height: 100%;
        }
        .product-card:hover { transform: translateY(-5px); }
        .product-img { height: 180px; background-color: #f8f9fc; display: flex; align-items: center; justify-content: center; }
        .product-img i { font-size: 50px; color: #bdc3c7; }
        .product-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-body { padding: 15px; }
        .product-title { font-weight: 600; margin-bottom: 10px; }
        .product-price { color: #1cc88a; font-weight: bold; font-size: 18px; }
        .btn-beli { background-color: #1cc88a; color: white; width: 100%; padding: 8px; border: none; border-radius: 5px; margin-top: 10px; }
        .btn-beli:hover { background-color: #169b6b; }
        .table-stats { font-size: 14px; }
        .table-stats th, .table-stats td { padding: 10px; }
        
        .stat-provinsi-card {
            transition: transform 0.2s;
            border-radius: 12px;
        }
        .stat-provinsi-card:hover {
            transform: translateY(-5px);
        }
        .stat-provinsi-card .card-footer {
            background: #f8f9fc;
        }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-industry"></i></div>
            <div class="sidebar-brand-text mx-3">Tekstil <sup>App</sup></div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item active"><a class="nav-link" href="dashboard.php"><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="tables.php"><span>Data Produk</span></a></li>
        <?php if(isSupplier()): ?>
        <li class="nav-item"><a class="nav-link" href="tambah.php"><span>Tambah Produk</span></a></li>
        <li class="nav-item"><a class="nav-link" href="pesanan_masuk.php"><span>Pesanan Masuk</span>
            <?php $id_supplier = $_SESSION['user_id']; $count_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengiriman WHERE id_supplier = $id_supplier AND status = 'pending'"); $pending = mysqli_fetch_assoc($count_pending); if($pending['total'] > 0): ?>
            <span class="badge badge-danger ml-2"><?php echo $pending['total']; ?></span>
            <?php endif; ?>
        </a></li>
        <?php endif; ?>
        <?php if(isPembeli()): ?>
        <li class="nav-item"><a class="nav-link" href="riwayat.php"><span>Riwayat Belanja</span></a></li>
        <?php endif; ?>
        <hr class="sidebar-divider d-none d-md-block">
        <li class="nav-item"><a class="nav-link" href="logout.php"><span>Logout</span></a></li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
                
                <!-- 🔥 FORM PENCARIAN SUDAH DIHAPUS, DIGANTI DENGAN TEKS BIASA -->
                <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0">
                    <span class="text-muted small">Sistem Manajemen Tekstil</span>
                </div>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?><br><small class="text-primary">(<?php echo $_SESSION['role']; ?>)</small></span>
                            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard <?php echo ($_SESSION['role'] == 'supplier') ? 'Supplier' : 'Pembeli'; ?></h1>
                    <?php if(isSupplier()): ?>
                        <a href="tambah.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah Produk Baru</a>
                    <?php else: ?>
                        <a href="tables.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-shopping-cart fa-sm text-white-50"></i> Belanja Sekarang</a>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4"><div class="stat-card border-left-primary"><div class="stat-title text-primary">Total Produk</div><div class="stat-number"><?php echo number_format($total_produk); ?></div></div></div>
                    <div class="col-xl-3 col-md-6 mb-4"><div class="stat-card border-left-success"><div class="stat-title text-success">Total Stok</div><div class="stat-number"><?php echo number_format($total_stok); ?></div></div></div>
                    <div class="col-xl-3 col-md-6 mb-4"><div class="stat-card border-left-info"><div class="stat-title text-info">Total Supplier</div><div class="stat-number"><?php echo number_format($total_supplier); ?></div></div></div>
                    <div class="col-xl-3 col-md-6 mb-4"><div class="stat-card border-left-warning"><div class="stat-title text-warning"><?php echo isSupplier() ? 'Stok Menipis' : 'Akses Cepat'; ?></div><div class="stat-number"><?php if(isSupplier()): ?><a href="tables.php" class="small text-warning">Lihat semua →</a><?php else: ?><a href="tables.php" class="small text-primary">Mulai Belanja →</a><?php endif; ?></div></div></div>
                </div>

                <?php if(isSupplier()): ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-exclamation-triangle"></i> Peringatan Stok Menipis (Stok < 10)</h6>
                                <a href="tables.php" class="btn btn-sm btn-warning">Kelola Stok</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead><tr><th>Nama Produk</th><th>Jenis Kain</th><th>Stok Saat Ini</th><th>Aksi</th></tr></thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($produk_menipis) > 0): ?>
                                                <?php while($row = mysqli_fetch_assoc($produk_menipis)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                                    <td><?php echo $row['jenis_kain']; ?></td>
                                                    <td class="text-center"><span class="badge badge-danger"><?php echo $row['stok']; ?></span></td>
                                                    <td class="text-center"><a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Tambah Stok</a></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr><td colspan="4" class="text-center text-success"><i class="fas fa-check-circle"></i> Semua stok aman!</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isPembeli()): ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-star"></i> Produk Terbaru untuk Anda</h6>
                                <a href="tables.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php while($row = mysqli_fetch_assoc($produk_terbaru)): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="product-card" style="display: flex; flex-direction: column; height: 100%;">
                                            <div class="product-img">
                                                <?php if(!empty($row['gambar']) && file_exists($row['gambar'])): ?>
                                                    <img src="<?php echo $row['gambar']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-image"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="product-body" style="flex: 1;">
                                                <div class="product-title"><?php echo htmlspecialchars($row['nama_produk']); ?></div>
                                                <div style="font-size: 12px;">Jenis: <?php echo $row['jenis_kain']; ?></div>
                                                <div style="font-size: 12px;">Warna: <?php echo $row['warna']; ?></div>
                                                <div class="product-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                                                <div style="font-size: 12px; margin-bottom: 10px;">Stok: <?php echo $row['stok']; ?></div>
                                            </div>
                                            <div style="padding: 0 15px 15px 15px; text-align: right;">
                                                <?php if($row['stok'] > 0): ?>
                                                    <a href="beli.php?id=<?php echo $row['id']; ?>" class="btn-beli" style="display: inline-block; width: auto; padding: 8px 20px;">Beli Sekarang</a>
                                                <?php else: ?>
                                                    <button class="btn-beli" style="background: #95a5a6; width: auto; padding: 8px 20px;" disabled>Stok Habis</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-primary text-white">
                                <h6 class="m-0 font-weight-bold">🏆 Top 5 Produk Terlaris</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-stats">
                                        <thead><tr><th>Produk</th><th>Jenis Kain</th><th>Terjual</th></tr></thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($top_produk) > 0): ?>
                                                <?php while($row = mysqli_fetch_assoc($top_produk)): ?>
                                                <tr>
                                                    <td><?php echo $row['nama_produk']; ?></td>
                                                    <td><?php echo $row['jenis_kain']; ?></td>
                                                    <td><span class="badge badge-success"><?php echo $row['total_terjual']; ?> pcs</span></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <td><td colspan="3" class="text-center">Belum ada data penjualan</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-info text-white">
                                <h6 class="m-0 font-weight-bold">📊 Penjualan per Jenis Kain</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-stats">
                                        <thead><tr><th>Jenis Kain</th><th>Total Terjual</th></tr></thead>
                                        <tbody>
                                            <?php 
                                            $penjualan_kain_all = mysqli_query($koneksi, $query_kain);
                                            if(mysqli_num_rows($penjualan_kain_all) > 0): ?>
                                                <?php while($row = mysqli_fetch_assoc($penjualan_kain_all)): ?>
                                                <tr>
                                                    <td><?php echo $row['jenis_kain']; ?></td>
                                                    <td><?php echo $row['total_terjual']; ?> pcs</td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <td><td colspan="2" class="text-center">Belum ada data</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-success text-white">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> Grafik Penjualan per Jenis Kain</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartPenjualanKain" style="max-height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-warning text-dark">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-map-marker-alt"></i> Top 5 Provinsi Supplier Terbanyak</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTopProvinsi" style="max-height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-xl-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-map-marked-alt me-2"></i> Statistik Penjualan per Provinsi
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if(count($stat_per_provinsi) > 0): ?>
                                        <?php 
                                        $color_list = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
                                        $i = 0;
                                        foreach($stat_per_provinsi as $provinsi => $produk_list): 
                                            foreach($produk_list as $produk):
                                                $color = $color_list[$i % count($color_list)];
                                        ?>
                                        <div class="col-md-4 col-lg-3 mb-4">
                                            <div class="card stat-provinsi-card h-100 border-left-<?php echo $color; ?> shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h5 class="card-title text-<?php echo $color; ?> mb-0">
                                                            <i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($provinsi); ?>
                                                        </h5>
                                                        <span class="badge badge-<?php echo $color; ?>">
                                                            <?php echo number_format($produk['total_terjual']); ?> pcs
                                                        </span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="text-center">
                                                        <div class="mb-2">
                                                            <i class="fas fa-tshirt fa-2x text-muted"></i>
                                                        </div>
                                                        <h6 class="font-weight-bold mb-1"><?php echo htmlspecialchars($produk['nama_produk']); ?></h6>
                                                        <p class="text-muted small mb-0">
                                                            <i class="fas fa-tag"></i> <?php echo $produk['jenis_kain']; ?>
                                                        </p>
                                                        <div class="mt-2">
                                                            <span class="badge badge-light">
                                                                <i class="fas fa-chart-line"></i> Terlaris
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-transparent text-center py-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-shopping-cart"></i> Total terjual: <?php echo number_format($produk['total_terjual']); ?> pcs
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            $i++;
                                            endforeach; 
                                        endforeach; 
                                        ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center py-5">
                                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                            <h5>Belum ada data penjualan per provinsi</h5>
                                            <p class="text-muted">Pastikan produk memiliki provinsi dan sudah ada transaksi pembelian</p>
                                            <a href="tables.php" class="btn btn-sm btn-primary mt-2">Kelola Produk</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <footer class="sticky-footer bg-white"><div class="container my-auto"><div class="copyright text-center my-auto"><span>Copyright &copy; Sistem Tekstil <?php echo date('Y'); ?></span></div></div></footer>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>

<script>
<?php if(!empty($labels_kain)): ?>
var ctx = document.getElementById('chartPenjualanKain').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_kain); ?>,
        datasets: [{
            label: 'Jumlah Terjual (pcs)',
            data: <?php echo json_encode($data_terjual); ?>,
            backgroundColor: ['rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)', 'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)', 'rgba(133, 135, 150, 0.8)'],
            borderColor: ['rgb(78, 115, 223)', 'rgb(28, 200, 138)', 'rgb(54, 185, 204)', 'rgb(246, 194, 62)', 'rgb(231, 74, 59)', 'rgb(133, 135, 150)'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: function(context) { return context.raw + ' pcs'; } } } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Jumlah Terjual (pcs)' } } }
    }
});
<?php else: ?>
document.getElementById('chartPenjualanKain').innerHTML = '<div class="text-center py-5"><i class="fas fa-chart-line fa-3x text-muted mb-3"></i><p>Belum ada data penjualan untuk ditampilkan.<br><a href="tables.php" class="btn btn-sm btn-primary mt-2">Mulai Belanja</a></p></div>';
<?php endif; ?>

<?php if(!empty($labels_provinsi)): ?>
var ctxProvinsi = document.getElementById('chartTopProvinsi').getContext('2d');
new Chart(ctxProvinsi, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels_provinsi); ?>,
        datasets: [{
            label: 'Jumlah Supplier',
            data: <?php echo json_encode($data_provinsi); ?>,
            backgroundColor: 'rgba(246, 194, 62, 0.8)',
            borderColor: 'rgb(246, 194, 62)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { callbacks: { label: function(context) { return context.raw + ' supplier'; } } }
        },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Jumlah Supplier' }, ticks: { stepSize: 1 } } }
    }
});
<?php else: ?>
document.getElementById('chartTopProvinsi').innerHTML = '<div class="text-center py-5"><i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i><p>Belum ada data provinsi supplier.<br>Tambahkan provinsi pada produk</p></div>';
<?php endif; ?>
</script>

</body>
</html>