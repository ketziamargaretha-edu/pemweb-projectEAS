<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; 
    $role = $_POST['role'];
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    
    $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        
        $query = "INSERT INTO users (username, password, role, nama_lengkap) 
                  VALUES ('$username', '$password', '$role', '$nama_lengkap')";
        
        if (mysqli_query($koneksi, $query)) {
            $success = "Pendaftaran berhasil! Silakan login.";
            echo "<meta http-equiv='refresh' content='2;url=login.php'>";
        } else {
            $error = "Pendaftaran gagal: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Tekstil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>Daftar Akun Baru</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daftar Sebagai</label>
                            <select name="role" class="form-select" required>
                                <option value="pembeli">Pembeli</option>
                                <option value="supplier">Supplier (Perusahaan)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Daftar</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php">Sudah punya akun? Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>