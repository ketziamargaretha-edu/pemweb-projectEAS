<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

// Ambil statistik
$query_produk = "SELECT COUNT(*) as total FROM produk_tekstil";
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, $query_produk))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-primary p-3">
            <div class="container-fluid">
                <span class="navbar-brand">Sistem Tekstil</span>
                <div>
                    <span class="text-white me-3">Halo, <?php echo $_SESSION['nama_lengkap']; ?></span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
        </nav>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h2>Selamat Datang di Sistem Tekstil</h2>
                        <p class="lead">Anda login sebagai: <strong><?php echo strtoupper($_SESSION['role']); ?></strong></p>
                        
                        <div class="row mt-5">
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h3><?php echo $total_produk; ?></h3>
                                        <p>Total Produk</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h3>
                                            <a href="tables.php" class="text-white text-decoration-none">
                                                Lihat Produk →
                                            </a>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>