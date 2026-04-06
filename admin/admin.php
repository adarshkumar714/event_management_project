<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$conn = db();
$stats = [
    'events' => 0,
    'bookings' => 0,
    'revenue' => 0.0,
];

$stats['events'] = (int) $conn->query('SELECT COUNT(*) AS total FROM events')->fetch_assoc()['total'];
$stats['bookings'] = (int) $conn->query('SELECT COUNT(*) AS total FROM bookings')->fetch_assoc()['total'];
$stats['revenue'] = (float) $conn->query('SELECT COALESCE(SUM(e.price), 0) AS total FROM bookings b INNER JOIN events e ON e.id = b.event_id')->fetch_assoc()['total'];
$dateColumn = events_date_column();
$capacitySelect = events_has_capacity() ? 'e.capacity,' : '';

$events = $conn->query(
    'SELECT e.id, e.title, e.venue, e.' . $dateColumn . ' AS event_date, e.price, ' . $capacitySelect . ' COUNT(b.id) AS booked_tickets
     FROM events e
     LEFT JOIN bookings b ON b.event_id = e.id
     GROUP BY e.id
     ORDER BY e.' . $dateColumn . ' ASC, e.title ASC'
)->fetch_all(MYSQLI_ASSOC);

$recentBookingsSelect = bookings_has_created_at() ? 'b.created_at' : 'NULL AS created_at';
$recentBookingsOrder = bookings_has_created_at() ? 'b.created_at DESC' : 'b.id DESC';
$recentBookings = $conn->query(
    'SELECT u.name AS user_name, e.title AS event_title, ' . $recentBookingsSelect . '
     FROM bookings b
     INNER JOIN users u ON u.id = b.user_id
     INNER JOIN events e ON e.id = b.event_id
     ORDER BY ' . $recentBookingsOrder . '
     LIMIT 8'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
        }

        .stat-card {
            border: 0;
            border-radius: 22px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <span class="navbar-brand fw-bold">EventHub Admin</span>
            <div class="d-flex gap-2">
                <a href="/event_project/admin/create_event.php" class="btn btn-success">Create Event</a>
                <a href="/event_project/logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-uppercase text-secondary small mb-1">Organizer overview</p>
                <h1 class="h2 mb-0">Welcome, <?= e($_SESSION['user_name'] ?? 'Admin') ?></h1>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="text-secondary mb-2">Total events</p>
                        <h2 class="display-6 mb-0"><?= $stats['events'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="text-secondary mb-2">Tickets booked</p>
                        <h2 class="display-6 mb-0"><?= $stats['bookings'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="text-secondary mb-2">Revenue</p>
                        <h2 class="display-6 mb-0">INR <?= number_format($stats['revenue'], 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="h4 mb-0">Event Inventory</h2>
                            <a href="/event_project/admin/create_event.php" class="btn btn-outline-primary btn-sm">Add another</a>
                        </div>
                        <?php if (!$events): ?>
                            <div class="alert alert-info mb-0">No events created yet.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Venue</th>
                                            <?php if (events_has_capacity()): ?>
                                                <th>Capacity</th>
                                            <?php endif; ?>
                                            <th>Booked</th>
                                            <?php if (events_has_capacity()): ?>
                                                <th>Available</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?= e($event['title']) ?></div>
                                                    <small class="text-secondary">INR <?= number_format((float) $event['price'], 2) ?></small>
                                                </td>
                                                <td><?= e(date('d M Y', strtotime($event['event_date']))) ?></td>
                                                <td><?= e($event['venue']) ?></td>
                                                <?php if (isset($event['capacity'])): ?>
                                                    <td><?= (int) $event['capacity'] ?></td>
                                                <?php endif; ?>
                                                <td><?= (int) $event['booked_tickets'] ?></td>
                                                <?php if (isset($event['capacity'])): ?>
                                                    <td><?= event_available_tickets($event) ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-3">Recent Bookings</h2>
                        <?php if (!$recentBookings): ?>
                            <div class="alert alert-info mb-0">No bookings yet.</div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentBookings as $booking): ?>
                                    <div class="list-group-item px-0">
                                        <div class="fw-semibold"><?= e($booking['user_name']) ?></div>
                                        <div><?= e($booking['event_title']) ?></div>
                                        <small class="text-secondary">
                                            1 seat booked
                                            <?php if (!empty($booking['created_at'])): ?>
                                                on <?= e(date('d M Y h:i A', strtotime($booking['created_at']))) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
