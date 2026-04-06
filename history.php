<?php
require_once __DIR__ . '/config.php';
require_role('user');

$conn = db();
$dateColumn = events_date_column();
$bookedOnSelect = bookings_has_created_at() ? 'b.created_at' : 'NULL AS created_at';
$orderColumn = bookings_has_created_at() ? 'b.created_at DESC' : 'b.id DESC';
$stmt = $conn->prepare(
    'SELECT e.title, e.venue, e.' . $dateColumn . ' AS event_date, e.price, b.tickets, ' . $bookedOnSelect . '
     FROM bookings b
     INNER JOIN events e ON e.id = b.event_id
     WHERE b.user_id = ?
     ORDER BY ' . $orderColumn
);
$userId = current_user_id();
$stmt->bind_param('i', $userId);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark py-3">
        <div class="container">
            <span class="navbar-brand fw-bold">My Bookings</span>
            <div class="d-flex gap-2">
                <a href="/event_project/user.php" class="btn btn-outline-light">Back to events</a>
                <a href="/event_project/logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Booking History</h1>

                <?php if (!$bookings): ?>
                    <div class="alert alert-info mb-0">You have not booked any events yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Venue</th>
                                    <th>Date</th>
                                    <th>Seat</th>
                                    <th>Total Paid</th>
                                    <?php if (bookings_has_created_at()): ?>
                                        <th>Booked On</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?= e($booking['title']) ?></td>
                                        <td><?= e($booking['venue']) ?></td>
                                        <td><?= e(date('d M Y', strtotime($booking['event_date']))) ?></td>
                                        <td>1</td>
                                        <td>INR <?= number_format((float) $booking['price'], 2) ?></td>
                                        <?php if (bookings_has_created_at()): ?>
                                            <td><?= e(date('d M Y h:i A', strtotime($booking['created_at']))) ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
