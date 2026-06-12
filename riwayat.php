<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requirePembeli();

$id_pembeli = $_SESSION['user_id'];

$query_riwayat = "SELECT t.*, p.nama_produk, p.jenis_kain, p.gambar,
                         peng.status as status_pengiriman, peng.nomor_resi
                  FROM transaksi t 
                  JOIN produk_tekstil p ON t.id_produk = p.id 
                  LEFT JOIN pengiriman peng ON t.id = peng.id_transaksi
                  WHERE t.id_pembeli = $id_pembeli 
                  ORDER BY t.tanggal_transaksi DESC";
$riwayat = mysqli_query($koneksi, $query_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Belanja - Sistem Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
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
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="tables.php">
                <i class="fas fa-fw fa-table"></i>
                <span>Data Produk</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="riwayat.php">
                <i class="fas fa-fw fa-shopping-cart"></i>
                <span>Riwayat Belanja</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- TOPBAR -->
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
                <h1 class="h3 mb-2 text-gray-800">Riwayat Belanja</h1>
                <p class="mb-4">Daftar semua pembelian yang pernah Anda lakukan.</p>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">History Pembelian</h6>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($riwayat) == 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-gray-300 mb-3"></i>
                                <h5>Belum ada riwayat belanja</h5>
                                <p class="text-muted">Anda belum melakukan pembelian apapun.</p>
                                <a href="tables.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-shopping-cart"></i> Mulai Belanja
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($riwayat)): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                            <td><?php echo $row['nama_produk']; ?> (<?php echo $row['jenis_kain']; ?>)</td>
                                            <td><?php echo $row['jumlah']; ?> pcs</td>
                                            <td>Rp <?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td class="text-success font-weight-bold">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'dikemas' => 'info',
                                                    'dikirim' => 'primary',
                                                    'selesai' => 'success',
                                                    'batal' => 'danger'
                                                ];
                                                $class = $status_class[$row['status_pengiriman']] ?? 'secondary';
                                                $text = $row['status_pengiriman'] ? strtoupper($row['status_pengiriman']) : 'PENDING';
                                                ?>
                                                <span class="badge badge-<?php echo $class; ?>"><?php echo $text; ?></span>
                                             </td>
                                         </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Sistem Tekstil Kelompok 5 <?php echo date('Y'); ?></span>
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