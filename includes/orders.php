<?php
declare(strict_types=1);

function order_types(): array
{
    return [
        'sample' => 'Sample',
        'trial' => 'Trial',
        'quote' => 'Quote',
    ];
}

function order_statuses(): array
{
    return [
        'submitted' => 'Submitted',
        'reviewing' => 'Reviewing',
        'in_progress' => 'In progress',
        'shipped' => 'Shipped',
        'quoted' => 'Quoted',
        'declined' => 'Declined',
        'closed' => 'Closed',
    ];
}

function inquiry_to_order_type(string $topic): string
{
    return match ($topic) {
        'sample' => 'sample',
        'partnership' => 'trial',
        default => 'quote',
    };
}

function next_order_reference(): string
{
    $prefix = 'CF-' . date('Y') . '-';
    $stmt = db()->prepare('SELECT reference FROM orders WHERE reference LIKE ? ORDER BY reference DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $n = 1;
    if (is_string($last) && preg_match('/(\d+)$/', $last, $match)) {
        $n = (int) $match[1] + 1;
    }
    return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
}

function add_order_event(string $orderId, string $event, ?string $body, bool $internal = false, ?string $actorId = null): void
{
    $stmt = db()->prepare('INSERT INTO order_events (id, order_id, actor_user_id, event, body, internal) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([uuid(), $orderId, $actorId, $event, $body, $internal ? 1 : 0]);
}

function create_work_order(string $userId, string $type, string $message, ?string $productId, string $quantity, string $unit, ?string $actorId = null): string
{
    $types = order_types();
    $type = isset($types[$type]) ? $type : 'quote';
    $id = uuid();
    $reference = next_order_reference();
    $stmt = db()->prepare('INSERT INTO orders (id, reference, user_id, type, status, payment_status, product_id, quantity, unit, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $reference, $userId, $type, 'submitted', 'none', $productId, $quantity !== '' ? $quantity : null, $unit !== '' ? $unit : null, $message]);
    if ($productId) {
        db()->prepare('INSERT INTO order_items (id, order_id, product_id, qty, unit) VALUES (?, ?, ?, ?, ?)')->execute([uuid(), $id, $productId, $quantity !== '' ? $quantity : null, $unit !== '' ? $unit : null]);
    }
    add_order_event($id, 'created', 'Request submitted.', false, $actorId ?: $userId);
    return $reference;
}

function fetch_order_by_reference(string $reference): ?array
{
    $stmt = db()->prepare('SELECT o.*, u.full_name AS partner_name, u.email AS partner_email, u.company_name, p.name AS product_name, a.full_name AS assignee_name FROM orders o JOIN users u ON u.id = o.user_id LEFT JOIN products p ON p.id = o.product_id LEFT JOIN users a ON a.id = o.assignee_id WHERE o.reference = ? LIMIT 1');
    $stmt->execute([$reference]);
    return $stmt->fetch() ?: null;
}

function fetch_order_events(string $orderId, bool $includeInternal): array
{
    $sql = 'SELECT e.*, u.full_name AS actor_name FROM order_events e LEFT JOIN users u ON u.id = e.actor_user_id WHERE e.order_id = ?';
    if (!$includeInternal) {
        $sql .= ' AND e.internal = 0';
    }
    $sql .= ' ORDER BY e.created_at ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function published_products(): array
{
    return safe_query(fn() => db()->query('SELECT id, name, slug FROM products WHERE published = 1 ORDER BY display_order, name')->fetchAll(), []);
}

function staff_assignees(): array
{
    return safe_query(fn() => db()->query("SELECT id, full_name, email FROM users WHERE role IN ('owner','staff') AND status = 'active' ORDER BY full_name")->fetchAll(), []);
}
