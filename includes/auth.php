<?php
declare(strict_types=1);

function ensure_owner_user(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $count = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count > 0) {
            return;
        }
        $config = app_config();
        $email = strtolower(trim((string) ($config['admin_email'] ?? '')));
        $hash = (string) ($config['admin_password_hash'] ?? '');
        if ($email === '' || $hash === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $stmt = db()->prepare('INSERT INTO users (id, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([uuid(), $email, $hash, 'Owner', 'owner', 'active']);
    } catch (Throwable $error) {
        error_log('ensure_owner_user: ' . $error->getMessage());
    }
}

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $id = trim((string) ($_SESSION['user_id'] ?? ''));
    if ($id === '') {
        $user = null;
        return null;
    }
    try {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch() ?: null;
    } catch (Throwable $error) {
        error_log('current_user: ' . $error->getMessage());
        $row = null;
    }
    if (!$row || ($row['status'] ?? '') !== 'active') {
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['admin'], $_SESSION['admin_seen'], $_SESSION['user_seen']);
        $user = null;
        return null;
    }
    $user = $row;
    return $row;
}

function user_has_role(array $roles): bool
{
    $person = current_user();
    return $person !== null && in_array((string) $person['role'], $roles, true);
}

function is_staff(): bool
{
    return user_has_role(['owner', 'staff']);
}

function is_partner(): bool
{
    return user_has_role(['partner']);
}

function is_admin(): bool
{
    return is_staff();
}

function require_role(array $roles): void
{
    $person = current_user();
    if ($person === null) {
        redirect('signin');
    }
    if (!in_array((string) $person['role'], $roles, true)) {
        redirect($person['role'] === 'partner' ? 'portal' : 'admin');
    }
    user_touch_session();
}

function require_admin(): void
{
    require_role(['owner', 'staff']);
}

function require_owner(): void
{
    require_role(['owner']);
}

function require_partner(): void
{
    require_role(['partner']);
}

function user_idle_seconds(): int
{
    return 8 * 3600;
}

function user_touch_session(): void
{
    $last = (int) ($_SESSION['user_seen'] ?? $_SESSION['admin_seen'] ?? 0);
    if ($last > 0 && (time() - $last) > user_idle_seconds()) {
        $role = (string) ($_SESSION['user_role'] ?? '');
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['admin'], $_SESSION['admin_seen'], $_SESSION['user_seen']);
        flash('Your session expired after 8 hours idle. Please sign in again.', 'error');
        redirect('signin');
    }
    $_SESSION['user_seen'] = time();
}

function after_login_path(?array $user = null): string
{
    $user = $user ?? current_user();
    if ($user && in_array((string) $user['role'], ['owner', 'staff'], true)) {
        return 'admin';
    }
    return 'portal';
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_seen'] = time();
    $_SESSION['admin'] = $user['role'] === 'partner' ? null : $user['email'];
    try {
        db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
    } catch (Throwable $error) {
        error_log('login_user: ' . $error->getMessage());
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function find_user_by_email(string $email): ?array
{
    $email = strtolower(trim($email));
    if ($email === '') {
        return null;
    }
    try {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $error) {
        error_log('find_user_by_email: ' . $error->getMessage());
        return null;
    }
}

function authenticate_user(string $email, string $password, array $roles): ?array
{
    $user = find_user_by_email($email);
    if (!$user || ($user['status'] ?? '') !== 'active') {
        return null;
    }
    if (!in_array((string) $user['role'], $roles, true)) {
        return null;
    }
    if (!password_verify($password, (string) $user['password_hash'])) {
        return null;
    }
    return $user;
}

function register_failures_locked(): bool
{
    return (int) ($_SESSION['register_lock_until'] ?? 0) > time();
}

function register_record_failure(): void
{
    $_SESSION['register_failures'] = (int) ($_SESSION['register_failures'] ?? 0) + 1;
    if ((int) $_SESSION['register_failures'] >= 8) {
        $_SESSION['register_lock_until'] = time() + 15 * 60;
        $_SESSION['register_failures'] = 0;
    }
}
