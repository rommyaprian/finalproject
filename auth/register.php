<?php
// register.php
require '../config/database.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // VERSI AMAN: Role diatur secara hardcode menjadi 'user'.
    $role = 'user'; 

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $role]);
        $message = "Pendaftaran berhasil! Silakan <a href='login.php' class='alert-link'>login</a>.";
    } catch (PDOException $e) {
        // Kode 23000 adalah kode error MySQL untuk duplicate entry
        if ($e->getCode() == 23000) {
            $message = "Username atau Email sudah terdaftar.";
        } else {
            $message = "Pendaftaran gagal: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            <h2 class="card-title text-center mb-4 text-primary">Daftar Akun Baru</h2>
            
            <?php if ($message): 
                $alert_class = (strpos($message, 'berhasil') !== false) ? 'alert-success' : 'alert-danger';
            ?>
                <div class="alert <?= $alert_class ?>" role="alert">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
            <p class="text-center mt-3">Sudah punya akun? <a href="login.php" class="text-primary">Login</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>