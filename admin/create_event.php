<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$error = '';
$success = get_flash('flash_success');
$hasCapacity = events_has_capacity();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $price = (float) ($_POST['price'] ?? 0);
        $capacity = (int) ($_POST['capacity'] ?? 0);

        if ($title === '' || $description === '' || $venue === '' || $eventDate === '') {
            throw new RuntimeException('Please complete all fields.');
        }

        if ($price < 0) {
            throw new RuntimeException('Price cannot be negative.');
        }

        if ($hasCapacity && $capacity <= 0) {
            throw new RuntimeException('Capacity must be at least 1 ticket.');
        }

        $conn = db();
        $dateColumn = events_date_column();

        if ($hasCapacity) {
            $stmt = $conn->prepare('INSERT INTO events (title, description, venue, ' . $dateColumn . ', price, capacity) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssdi', $title, $description, $venue, $eventDate, $price, $capacity);
        } else {
            $stmt = $conn->prepare('INSERT INTO events (title, description, venue, ' . $dateColumn . ', price) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssd', $title, $description, $venue, $eventDate, $price);
        }
        $stmt->execute();
        $stmt->close();

        set_flash('flash_success', 'Event created successfully.');
        redirect('/event_project/admin/create_event.php');
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
    <title>Create Event | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <span class="navbar-brand fw-bold">Create Event</span>
            <div class="d-flex gap-2">
                <a href="/event_project/admin/admin.php" class="btn btn-outline-light">Dashboard</a>
                <a href="/event_project/logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h1 class="h3 mb-4">Publish a new event</h1>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= e($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Event title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Event date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ticket price</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" required>
                            </div>
                            <?php if ($hasCapacity): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Capacity</label>
                                    <input type="number" name="capacity" min="1" class="form-control" required>
                                </div>
                            <?php endif; ?>
                            <div class="col-12">
                                <button class="btn btn-success w-100">Create Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
