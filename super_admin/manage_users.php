<?php
// super_admin/manage_users.php
require_once '../config/database.php';
checkAuth('super_admin'); 

$message = '';
$current_user_id = $_SESSION['user_id'];

// Logika Update (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $target_id = $_POST['target_user_id'];
    try {
        if ($current_user_id == $target_id) throw new Exception("Tidak bisa mengubah akun sendiri.");

        if ($_POST['action'] == 'update_role') {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$_POST['new_role'], $target_id]);
            $message = "<div class='alert alert-success'>Role berhasil diperbarui!</div>";
        } elseif ($_POST['action'] == 'toggle_status') {
            $new_status = $_POST['current_status'] == 1 ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$new_status, $target_id]);
            $message = "<div class='alert alert-info'>Status akun berhasil diubah!</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, username ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body{
            background:#f4f6fb;
            font-family:"Segoe UI", sans-serif;
        }

        /* ===== PAGE HEADER ===== */
        .page-title{
            font-weight:800;
            color:#1e3a8a;
        }

        /* ===== CARD ===== */
        .card-custom{
            border:none;
            border-radius:20px;
            box-shadow:0 20px 50px rgba(0,0,0,.1);
        }

        /* ===== TABLE ===== */
        .table thead{
            background:#1e3a8a;
            color:#fff;
        }

        .table td{
            vertical-align:middle;
        }

        .table tbody tr:hover{
            background:#eef2ff;
        }

        /* ===== BUTTON ===== */
        .btn-rounded{
            border-radius:999px;
            font-weight:600;
        }

        /* ===== FORM ===== */
        .form-select-sm{
            border-radius:10px;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <!-- ===== HEADER ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">Manajemen Pengguna</h3>
    </div>

    <!-- ===== MESSAGE ===== -->
    <?= $message ?>

    <!-- ===== TABLE CARD ===== -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width:180px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($u['username']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($u['email']) ?>
                        </td>

                        <!-- ROLE -->
                        <td>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="update_role">

                                <select name="new_role"
                                        class="form-select form-select-sm"
                                        <?= $u['id'] == $current_user_id ? 'disabled' : '' ?>>
                                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                    <option value="super_admin" <?= $u['role']=='super_admin'?'selected':'' ?>>Super Admin</option>
                                </select>

                                <button type="submit"
                                        class="btn btn-sm btn-primary btn-rounded"
                                        <?= $u['id'] == $current_user_id ? 'disabled' : '' ?>>
                                    Set
                                </button>
                            </form>
                        </td>

                        <!-- STATUS -->
                        <td>
                            <span class="badge rounded-pill bg-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>

                        <!-- ACTION -->
                        <td>
                            <?php if($u['id'] != $current_user_id): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                                <input type="hidden" name="action" value="toggle_status">

                                <button type="submit"
                                        class="btn btn-sm btn-<?= $u['is_active'] ? 'outline-danger' : 'outline-success' ?> btn-rounded">
                                    <?= $u['is_active'] ? 'Blokir' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
