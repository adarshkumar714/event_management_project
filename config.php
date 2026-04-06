<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'event_db';

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    return $conn;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function require_role(string $role): void
{
    if (!is_logged_in() || current_role() !== $role) {
        $_SESSION['flash_error'] = 'Please sign in to continue.';
        redirect('/event_project/auth.php?role=' . $role);
    }
}

function set_flash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $message;
}

function event_available_tickets(array $event): int
{
    if (!isset($event['capacity'])) {
        return 9999;
    }

    return max(0, (int) $event['capacity'] - (int) $event['booked_tickets']);
}

function events_date_column(): string
{
    static $column = null;

    if ($column !== null) {
        return $column;
    }

    $query = db()->query("SHOW COLUMNS FROM events LIKE 'event_date'");
    $column = $query->num_rows > 0 ? 'event_date' : 'date';

    return $column;
}

function events_has_capacity(): bool
{
    static $hasCapacity = null;

    if ($hasCapacity !== null) {
        return $hasCapacity;
    }

    $query = db()->query("SHOW COLUMNS FROM events LIKE 'capacity'");
    $hasCapacity = $query->num_rows > 0;

    return $hasCapacity;
}

function bookings_has_created_at(): bool
{
    static $hasCreatedAt = null;

    if ($hasCreatedAt !== null) {
        return $hasCreatedAt;
    }

    $query = db()->query("SHOW COLUMNS FROM bookings LIKE 'created_at'");
    $hasCreatedAt = $query->num_rows > 0;

    return $hasCreatedAt;
}

function verify_user_password(string $plainPassword, string $storedPassword): bool
{
    if ($plainPassword === $storedPassword) {
        return true;
    }

    return password_verify($plainPassword, $storedPassword);
}
?>
