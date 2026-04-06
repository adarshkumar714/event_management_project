<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventHub | Event Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-start: #f7efe5;
            --bg-end: #dce8f2;
            --accent: #0f766e;
            --accent-dark: #134e4a;
            --warm: #d97706;
            --ink: #1f2937;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(217, 119, 6, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(15, 118, 110, 0.22), transparent 35%),
                linear-gradient(135deg, var(--bg-start), var(--bg-end));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, 'Times New Roman', serif;
        }

        .hero-card {
            width: min(960px, calc(100% - 2rem));
            border: 0;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.16);
        }

        .hero-copy {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(10px);
        }

        .hero-side {
            background: linear-gradient(160deg, var(--accent), var(--accent-dark));
            color: #fff;
        }

        .pill {
            display: inline-block;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            background: rgba(15, 118, 110, 0.1);
            color: var(--accent-dark);
            font-size: 0.9rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .entry-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .entry-button {
            display: block;
            text-decoration: none;
            border-radius: 22px;
            padding: 1.25rem 1.35rem;
            min-height: 170px;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
        }

        .entry-button:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 36px rgba(15, 23, 42, 0.14);
        }

        .entry-user {
            background: linear-gradient(145deg, #0f766e, #115e59);
            color: #fff;
        }

        .entry-admin {
            background: #ffffff;
            color: var(--ink);
        }

        .entry-kicker {
            display: inline-block;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.74rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.9rem;
        }

        .entry-user .entry-kicker {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        .entry-admin .entry-kicker {
            background: rgba(15, 118, 110, 0.08);
            color: var(--accent-dark);
        }

        .entry-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.55rem;
        }

        .entry-copy {
            margin: 0;
            opacity: 0.86;
            line-height: 1.5;
        }

        .entry-arrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 1.25rem;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .entry-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="card hero-card">
        <div class="row g-0">
            <div class="col-lg-7 hero-copy p-5 p-lg-6">
                <span class="pill mb-3">Smarter event booking</span>
                <h1 class="display-5 fw-bold mt-3">Run events and reservations from one polished dashboard.</h1>
                <p class="lead mt-3 mb-4">EventHub gives attendees a simple booking experience and gives organizers a clean place to publish and monitor events.</p>
                <div class="entry-grid">
                    <a href="/event_project/auth.php?role=user" class="entry-button entry-user">
                        <span class="entry-kicker">User Area</span>
                        <div class="entry-title">Login or Sign Up</div>
                        <p class="entry-copy">Browse events, book tickets, and manage your booking history with a simple account.</p>
                        <span class="entry-arrow">Open User Panel <span>&rarr;</span></span>
                    </a>
                    <a href="/event_project/auth.php?role=admin" class="entry-button entry-admin">
                        <span class="entry-kicker">Admin Area</span>
                        <div class="entry-title">Login or Sign Up</div>
                        <p class="entry-copy">Create events, review bookings, and manage the event system from one control panel.</p>
                        <span class="entry-arrow">Open Admin Panel <span>&rarr;</span></span>
                    </a>
                </div>
            </div>
            <div class="col-lg-5 hero-side p-5 d-flex flex-column justify-content-center">
                <h2 class="fw-bold">What is included</h2>
                <ul class="mt-4 fs-5">
                    <li>Role-based login for attendees and admins</li>
                    <li>Event creation with capacity tracking</li>
                    <li>Booking history for each attendee</li>
                    <li>Admin dashboard with live booking totals</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
