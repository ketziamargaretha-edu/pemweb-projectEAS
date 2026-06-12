<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireSupplier();

$pesan = '';
$id_supplier = $_SESSION['user_id'];
$nama_supplier = $_SESSION['nama_lengkap'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id_pengiriman = (int)$_POST['id_pengiriman'];
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $query_update = "UPDATE pengiriman SET 
                     status = '$status_baru',
                     updated_at = NOW()
                     WHERE id = $id_pengiriman AND id_supplier = $id_supplier";
    
    if (mysqli_query($koneksi, $query_update)) {
        $pesan = '<div class="alert alert-success">Status pengiriman berhasil diupdate!</div>';
    } else {
        $pesan = '<div class="alert alert-danger">Error: ' . mysqli_error($koneksi) . '</div>';
    }
}

$query_pesanan = "SELECT p.*, t.id as transaksi_id, t.jumlah, t.total_harga, 
                         t.tanggal_transaksi, pr.nama_produk, pr.gambar,
                         u.nama_lengkap as pembeli_nama
                  FROM pengiriman p
                  JOIN transaksi t ON p.id_transaksi = t.id
                  JOIN produk_tekstil pr ON t.id_produk = pr.id
                  JOIN users u ON t.id_pembeli = u.id
                  WHERE pr.supplier = '{$_SESSION['nama_lengkap']}'
                  ORDER BY p.updated_at DESC";
$pesanan = mysqli_query($koneksi, $query_pesanan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Masuk - Supplier Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .sidebar-brand-text {
            font-size: 1.2rem;
        }
    </style>
</head>
<body id="page-top">

<div id="wrapper">
    <!-- SIDEBAR - SAMA PERSIS DENGAN TABLES.PHP -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-industry"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Tekstil <sup>App</sup></div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="tables.php">
                <span>Data Produk</span>
            </a>
        </li>

        <?php if(isSupplier()): ?>
        <li class="nav-item">
            <a class="nav-link" href="tambah.php">
                <span>Tambah Produk</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if(isSupplier()): ?>
        <li class="nav-item active">
            <a class="nav-link" href="pesanan_masuk.php">
                <span>Pesanan Masuk</span>
                <?php
                $count_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengiriman WHERE id_supplier = $id_supplier AND status = 'pending'");
                $pending = mysqli_fetch_assoc($count_pending);
                if($pending['total'] > 0): ?>
                    <span class="badge badge-danger ml-2"><?php echo $pending['total']; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <?php endif; ?>

        <?php if(isPembeli()): ?>
        <li class="nav-item">
            <a class="nav-link" href="riwayat.php">
                <span>Riwayat Belanja</span>
            </a>
        </li>
        <?php endif; ?>

        <hr class="sidebar-divider d-none d-md-block">

        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <span>Logout</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                                <br><small class="text-primary">(<?php echo $_SESSION['role']; ?>)</small>
                            </span>
                            <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid">
                <h1 class="h3 mb-2 text-gray-800">Pesanan Masuk</h1>
                <p class="mb-4">Kelola pengiriman pesanan dari pembeli.</p>

                <?php echo $pesan; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Pesanan</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal Pesan</th>
                                        <th>Pembeli</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($pesanan) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($pesanan)): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['pembeli_nama']); ?></td>
                                            <td><?php echo $row['nama_produk']; ?></td>
                                            <td><?php echo $row['jumlah']; ?></td>
                                            <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'dikemas' => 'info',
                                                    'dikirim' => 'primary',
                                                    'selesai' => 'success',
                                                    'batal' => 'danger'
                                                ];
                                                $class = $status_class[$row['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $class; ?>">
                                                    <?php echo strtoupper($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalStatus<?php echo $row['id']; ?>">
                                                    <i class="fas fa-edit"></i> Update Status
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modalStatus<?php echo $row['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Update Status Pengiriman</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id_pengiriman" value="<?php echo $row['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label>Status</label>
                                                                <select name="status" class="form-control" required>
                                                                    <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending (Menunggu)</option>
                                                                    <option value="dikemas" <?php echo $row['status'] == 'dikemas' ? 'selected' : ''; ?>>Sedang Dikemas</option>
                                                                    <option value="dikirim" <?php echo $row['status'] == 'dikirim' ? 'selected' : ''; ?>>Sudah Dikirim</option>
                                                                    <option value="selesai" <?php echo $row['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                                    <option value="batal" <?php echo $row['status'] == 'batal' ? 'selected' : ''; ?>>Batal</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                            <button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada pesanan masuk</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Sistem Tekstil <?php echo date('Y'); ?></span>
                </div>
            </div>
        </footer>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html>