<?php
require_once __DIR__ . '/config.php';
require_role('user');

$conn = db();
$error = get_flash('flash_error');
$success = get_flash('flash_success');
$dateColumn = events_date_column();
$capacitySelect = events_has_capacity() ? 'e.capacity,' : '';
$orderDate = 'e.' . $dateColumn;
$userId = current_user_id();

$events = $conn->query(
    'SELECT e.id, e.title, e.description, e.venue, e.' . $dateColumn . ' AS event_date, e.price, ' . $capacitySelect . ' COUNT(b.id) AS booked_tickets,
            MAX(CASE WHEN b.user_id = ' . $userId . ' THEN 1 ELSE 0 END) AS already_booked
     FROM events e
     LEFT JOIN bookings b ON b.event_id = e.id
     GROUP BY e.id
     ORDER BY ' . $orderDate . ' ASC, e.title ASC'
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
        }

        .hero {
            background: linear-gradient(120deg, #0f766e, #0f172a);
            color: white;
            border-radius: 24px;
        }

        .event-card {
            border: 0;
            border-radius: 22px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <span class="navbar-brand fw-bold">EventHub</span>
            <div class="d-flex gap-2">
                <a href="/event_project/history.php" class="btn btn-outline-light">My Bookings</a>
                <a href="/event_project/logout.php" class="btn btn-warning">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        <section class="hero p-4 p-lg-5 mb-4">
            <p class="text-uppercase small mb-2">Attendee dashboard</p>
            <h1 class="display-6 fw-bold">Welcome, <?= e($_SESSION['user_name'] ?? 'Guest') ?></h1>
            <p class="mb-0 text-white-50">Reserve tickets for upcoming events and keep track of every booking in one place.</p>
        </section>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Available Events</h2>
            <span class="text-secondary"><?= count($events) ?> event(s)</span>
        </div>

        <?php if (!$events): ?>
            <div class="alert alert-info">No events have been created yet. Please check back soon.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                    <?php $available = event_available_tickets($event); ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card event-card shadow-sm h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between gap-3 align-items-start">
                                    <h3 class="h5 text-primary mb-2"><?= e($event['title']) ?></h3>
                                    <?php if (isset($event['capacity'])): ?>
                                        <span class="badge text-bg-dark"><?= $available ?> left</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-secondary flex-grow-1"><?= e($event['description']) ?></p>
                                <p class="mb-1"><strong>Venue:</strong> <?= e($event['venue']) ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?= e(date('d M Y', strtotime($event['event_date']))) ?></p>
                                <p class="mb-3"><strong>Price:</strong> INR <?= number_format((float) $event['price'], 2) ?></p>
                                <p class="mb-3"><strong>Rule:</strong> One seat per user</p>

                                <form action="/event_project/book.php" method="POST" class="mt-auto">
                                    <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                                    <button name="book" class="btn btn-success w-100" <?= ((isset($event['capacity']) && $available === 0) || (int) $event['already_booked'] === 1) ? 'disabled' : '' ?>>
                                        <?=
                                            (int) $event['already_booked'] === 1
                                                ? 'Already Booked'
                                                : ((isset($event['capacity']) && $available === 0) ? 'Sold Out' : 'Book 1 Seat')
                                        ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
