<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

// Daftar kategori warna (dropdown)
$kategori_warna = [
    'Merah', 'Hijau', 'Biru', 'Kuning', 'Ungu', 'Orange', 'Pink',
    'Hitam', 'Putih', 'Abu-abu', 'Coklat', 'Emas', 'Perak', 'Tosca',
    'Marun', 'Navy', 'Mint', 'Salmon', 'Lavender', 'Beige', 'Other'
];

// Ambil daftar jenis kain unik untuk dropdown
$query_jenis = "SELECT DISTINCT jenis_kain FROM produk_tekstil WHERE jenis_kain IS NOT NULL AND jenis_kain != '' ORDER BY jenis_kain";
$result_jenis = mysqli_query($koneksi, $query_jenis);
$daftar_jenis = [];
while($row = mysqli_fetch_assoc($result_jenis)) {
    $daftar_jenis[] = $row['jenis_kain'];
}

// Ambil daftar supplier unik untuk dropdown
$query_supplier = "SELECT DISTINCT supplier FROM produk_tekstil WHERE supplier IS NOT NULL AND supplier != '' ORDER BY supplier";
$result_supplier = mysqli_query($koneksi, $query_supplier);
$daftar_supplier = [];
while($row = mysqli_fetch_assoc($result_supplier)) {
    $daftar_supplier[] = $row['supplier'];
}

$query_provinsi_list = "SELECT DISTINCT provinsi FROM produk_tekstil WHERE provinsi IS NOT NULL AND provinsi != '' ORDER BY provinsi";
$result_provinsi_list = mysqli_query($koneksi, $query_provinsi_list);
$daftar_provinsi_filter = [];
while($row = mysqli_fetch_assoc($result_provinsi_list)) {
    $daftar_provinsi_filter[] = $row['provinsi'];
}

$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['warna_kategori']) && !empty($_GET['warna_kategori'])) {
    $warna_filter = $_GET['warna_kategori'];
    $where_conditions[] = "warna LIKE ?";
    $params[] = "%$warna_filter%";
    $types .= "s";
}

if (isset($_GET['jenis_kain']) && !empty($_GET['jenis_kain'])) {
    $where_conditions[] = "jenis_kain = ?";
    $params[] = $_GET['jenis_kain'];
    $types .= "s";
}

if (isset($_GET['supplier']) && !empty($_GET['supplier'])) {
    $where_conditions[] = "supplier = ?";
    $params[] = $_GET['supplier'];
    $types .= "s";
}

if (isset($_GET['provinsi']) && !empty($_GET['provinsi'])) {
    $where_conditions[] = "provinsi = ?";
    $params[] = $_GET['provinsi'];
    $types .= "s";
}

if (isset($_GET['harga_min']) && !empty($_GET['harga_min'])) {
    $where_conditions[] = "harga >= ?";
    $params[] = (float)$_GET['harga_min'];
    $types .= "d";
}

if (isset($_GET['harga_max']) && !empty($_GET['harga_max'])) {
    $where_conditions[] = "harga <= ?";
    $params[] = (float)$_GET['harga_max'];
    $types .= "d";
}

$keyword = isset($_GET['cari']) ? $_GET['cari'] : '';
if (!empty($keyword)) {
    $where_conditions[] = "nama_produk LIKE ?";
    $params[] = "%$keyword%";
    $types .= "s";
}

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$allowed_sort = ['id', 'nama_produk', 'harga', 'stok', 'warna', 'jenis_kain', 'supplier', 'provinsi'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'id';
}
$sort_order = ($sort_order == 'ASC') ? 'ASC' : 'DESC';

$sql = "SELECT * FROM produk_tekstil";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY $sort_by $sort_order";

if (!empty($params)) {
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($koneksi, $sql);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Produk Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar-brand-text { font-size: 1.2rem; }
        .filter-card { background: #f8f9fc; border: 1px solid #e3e6f0; }
        .btn-action {
            margin: 2px;
            min-width: 65px;
        }
        .btn-action i {
            margin-right: 4px;
        }
        .table tbody tr {
            text-align: center;
            vertical-align: middle;
        }
        .table tbody td.text-left {
            text-align: left;
        }
        .table tbody td.text-right {
            text-align: right;
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
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><span>Dashboard</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="tables.php"><span>Data Produk</span></a></li>
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
                <h1 class="h3 mb-2 text-gray-800">Data Produk Tekstil</h1>
                <p class="mb-4">Kelola data produk tekstil Anda di sini.</p>

                <!-- FORM FILTER -->
                <div class="card shadow mb-4 filter-card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter Pencarian</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="tables.php" class="row">
                            <!-- Kategori Warna -->
                            <div class="col-md-3 mb-2">
                                <label>Kategori Warna</label>
                                <select name="warna_kategori" class="form-control">
                                    <option value="">-- Semua Warna --</option>
                                    <?php foreach($kategori_warna as $warna): ?>
                                        <option value="<?php echo $warna; ?>" <?php echo (isset($_GET['warna_kategori']) && $_GET['warna_kategori'] == $warna) ? 'selected' : ''; ?>><?php echo $warna; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Pilih kategori warna</small>
                            </div>

                            <!-- Jenis Kain -->
                            <div class="col-md-3 mb-2">
                                <label>Jenis Kain</label>
                                <select name="jenis_kain" class="form-control">
                                    <option value="">-- Semua Jenis --</option>
                                    <?php foreach($daftar_jenis as $jenis): ?>
                                        <option value="<?php echo $jenis; ?>" <?php echo (isset($_GET['jenis_kain']) && $_GET['jenis_kain'] == $jenis) ? 'selected' : ''; ?>><?php echo $jenis; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Supplier -->
                            <div class="col-md-3 mb-2">
                                <label>Supplier</label>
                                <select name="supplier" class="form-control">
                                    <option value="">-- Semua Supplier --</option>
                                    <?php foreach($daftar_supplier as $supp): ?>
                                        <option value="<?php echo $supp; ?>" <?php echo (isset($_GET['supplier']) && $_GET['supplier'] == $supp) ? 'selected' : ''; ?>><?php echo $supp; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dropdown Provinsi -->
                            <div class="col-md-3 mb-2">
                                <label>Provinsi</label>
                                <select name="provinsi" class="form-control">
                                    <option value="">-- Semua Provinsi --</option>
                                    <?php foreach($daftar_provinsi_filter as $prov): ?>
                                        <option value="<?php echo $prov; ?>" <?php echo (isset($_GET['provinsi']) && $_GET['provinsi'] == $prov) ? 'selected' : ''; ?>><?php echo $prov; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Filter berdasarkan provinsi supplier</small>
                            </div>

                            <!-- Cari Nama Produk -->
                            <div class="col-md-3 mb-2">
                                <label>Cari Nama Produk</label>
                                <input type="text" name="cari" class="form-control" placeholder="Kata kunci..." value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
                            </div>

                            <!-- Harga Min -->
                            <div class="col-md-2 mb-2">
                                <label>Harga Min (Rp)</label>
                                <input type="number" name="harga_min" class="form-control" placeholder="Minimal" value="<?php echo isset($_GET['harga_min']) ? $_GET['harga_min'] : ''; ?>">
                            </div>

                            <!-- Harga Max -->
                            <div class="col-md-2 mb-2">
                                <label>Harga Max (Rp)</label>
                                <input type="number" name="harga_max" class="form-control" placeholder="Maksimal" value="<?php echo isset($_GET['harga_max']) ? $_GET['harga_max'] : ''; ?>">
                            </div>

                            <!-- Urutkan Berdasarkan -->
                            <div class="col-md-2 mb-2">
                                <label>Urutkan Berdasarkan</label>
                                <select name="sort_by" class="form-control">
                                    <option value="id" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'id') ? 'selected' : ''; ?>>ID</option>
                                    <option value="nama_produk" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'nama_produk') ? 'selected' : ''; ?>>Nama Produk</option>
                                    <option value="harga" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'harga') ? 'selected' : ''; ?>>Harga</option>
                                    <option value="stok" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'stok') ? 'selected' : ''; ?>>Stok</option>
                                    <option value="warna" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'warna') ? 'selected' : ''; ?>>Warna</option>
                                    <option value="jenis_kain" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'jenis_kain') ? 'selected' : ''; ?>>Jenis Kain</option>
                                    <option value="supplier" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                    <option value="provinsi" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'provinsi') ? 'selected' : ''; ?>>Provinsi</option>
                                </select>
                            </div>

                            <!-- Urutan -->
                            <div class="col-md-2 mb-2">
                                <label>Urutan</label>
                                <select name="sort_order" class="form-control">
                                    <option value="ASC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') ? 'selected' : ''; ?>>Meningkat (A-Z)</option>
                                    <option value="DESC" <?php echo (isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') ? 'selected' : ''; ?>>Menurun (Z-A)</option>
                                </select>
                            </div>

                            <!-- Tombol Filter & Reset -->
                            <div class="col-md-12 mt-3 text-center">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <a href="tables.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Produk</h6>
                        <?php if(isSupplier()): ?>
                        <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Produk</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Jenis Kain</th>
                                        <th>Warna</th>
                                        <th>Ukuran</th>
                                        <th>Stok</th>
                                        <th>Harga</th>
                                        <th>Supplier</th>
                                        <th>Provinsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr style="text-align: center; vertical-align: middle;">
                                            <td><?php echo $row['id']; ?></td>
                                            <td>
                                                <?php if(!empty($row['gambar']) && file_exists($row['gambar'])): ?>
                                                    <img src="<?php echo $row['gambar']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                                <?php else: ?>
                                                    <i class="fas fa-image fa-2x text-gray-300"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: left;"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                            <td><?php echo $row['jenis_kain']; ?></td>
                                            <td><?php echo $row['warna']; ?></td>
                                            <td><?php echo $row['ukuran']; ?></td>
                                            <td>
                                                <span class="badge <?php echo ($row['stok'] < 10) ? 'badge-danger' : 'badge-success'; ?>">
                                                    <?php echo $row['stok']; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right;">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                                            <td><?php echo !empty($row['provinsi']) ? htmlspecialchars($row['provinsi']) : '-'; ?></td>
                                            <td>
                                                <?php if(isSupplier()): ?>
                                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm btn-action" title="Edit">
                                                        <i class="fas fa-edit fa-lg"></i>
                                                    </a>
                                                    <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm btn-action" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                        <i class="fas fa-trash-alt fa-lg"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <?php if($row['stok'] > 0): ?>
                                                        <a href="beli.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm btn-action" title="Beli">
                                                            <i class="fas fa-shopping-cart fa-lg"></i> Beli
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm btn-action" disabled title="Stok Habis">
                                                            <i class="fas fa-ban fa-lg"></i> Habis
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data yang sesuai dengan filter</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
</body>
</html>