<?php
require_once __DIR__ . '/config.php';

$role = $_GET['role'] ?? 'user';
$role = $role === 'admin' ? 'admin' : 'user';
$error = '';
$success = get_flash('flash_success');
$flashError = get_flash('flash_error');
if ($flashError) {
    $error = $flashError;
}

if (is_logged_in()) {
    redirect(current_role() === 'admin' ? '/event_project/admin/admin.php' : '/event_project/user.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $conn = db();

        if ($action === 'register') {
            $name = trim($_POST['name'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                throw new RuntimeException('Please fill in every required field.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Please enter a valid email address.');
            }

            $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                throw new RuntimeException('An account with that email already exists.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            $stmt->execute();
            $stmt->close();

            set_flash('flash_success', 'Registration complete. You can sign in now.');
            redirect('/event_project/auth.php?role=' . $role);
        }

        if ($action === 'login') {
            if ($email === '' || $password === '') {
                throw new RuntimeException('Email and password are required.');
            }

            $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ? AND role = ? LIMIT 1');
            $stmt->bind_param('ss', $email, $role);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user || !verify_user_password($password, $user['password'])) {
                throw new RuntimeException('Invalid credentials for this panel.');
            }

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            redirect($user['role'] === 'admin' ? '/event_project/admin/admin.php' : '/event_project/user.php');
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(ucfirst($role)) ?> Access | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: linear-gradient(140deg, #111827, #1d4ed8 55%, #f59e0b);
            font-family: Arial, sans-serif;
        }

        .panel {
            width: min(920px, calc(100% - 2rem));
            border-radius: 26px;
            overflow: hidden;
            border: 0;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.32);
        }

        .brand {
            background: #f8fafc;
        }

        .role-badge {
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.82rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="card panel">
        <div class="row g-0">
            <div class="col-lg-5 p-5 text-white" style="background:#0f172a;">
                <p class="role-badge mb-3"><?= e($role) ?> access</p>
                <h1 class="fw-bold"><?= $role === 'admin' ? 'Simple admin login' : 'Simple user login' ?></h1>
                <p class="mt-3 text-white-50"><?= $role === 'admin' ? 'Login with your admin email and password to manage events.' : 'Login or create a user account in one step.' ?></p>
                <a href="/event_project/index.php" class="btn btn-outline-light mt-3">Back to home</a>
            </div>
            <div class="col-lg-7 brand p-5">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h3 class="mb-3">Login</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $role === 'admin' ? 'ad@gmail.com' : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" value="<?= $role === 'admin' ? 'ad@321' : '' ?>" required>
                            </div>
                            <button class="btn btn-primary w-100">Sign In</button>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <h3 class="mb-3">Sign Up</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3">
                                <label class="form-label">Full name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button class="btn btn-success w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
