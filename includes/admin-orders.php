<?php
declare(strict_types=1);

function handle_admin_orders(string $path): void
{
    require_admin();
    $reference = '';
    if (str_starts_with($path, 'admin/orders/') && strlen($path) > 13) {
        $reference = strtoupper(trim(substr($path, 13)));
    }

    if ($reference !== '') {
        $order = fetch_order_by_reference($reference);
        if (!$order) {
            http_response_code(404);
            render_start('Order not found');
            echo '<section class="admin-panel"><h1 class="h3">Order not found</h1><p><a href="' . e(url('admin/orders')) . '">Back to orders</a></p></section>';
            render_end();
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $actor = current_user();
            $statuses = order_statuses();
            $status = (string) ($_POST['status'] ?? $order['status']);
            $status = isset($statuses[$status]) ? $status : (string) $order['status'];
            $assignee = trim((string) ($_POST['assignee_id'] ?? ''));
            $assignee = $assignee === '' ? null : $assignee;
            $staffNote = trim((string) ($_POST['staff_note'] ?? ''));
            $customerNote = trim((string) ($_POST['customer_note'] ?? ''));
            db()->prepare('UPDATE orders SET status = ?, assignee_id = ?, staff_note = ? WHERE id = ?')->execute([$status, $assignee, $staffNote !== '' ? $staffNote : null, $order['id']]);
            if ($status !== (string) $order['status']) {
                add_order_event($order['id'], 'status', 'Status changed to ' . ($statuses[$status] ?? $status) . '.', false, $actor['id'] ?? null);
            }
            if ($customerNote !== '') {
                add_order_event($order['id'], 'note', $customerNote, false, $actor['id'] ?? null);
            }
            flash('Order updated.');
            redirect('admin/orders/' . $order['reference']);
        }
        $events = fetch_order_events((string) $order['id'], true);
        $assignees = staff_assignees();
        render_start('Order ' . $order['reference']);
        ?>
        <section class="admin-panel">
          <div class="dash-head">
            <div>
              <p class="eyebrow"><a href="<?= e(url('admin/orders')) ?>">Orders</a></p>
              <h1><?= e($order['reference']) ?></h1>
              <p class="dash-lead"><?= e($order['partner_name']) ?> · <?= e($order['partner_email']) ?></p>
            </div>
            <span class="portal-status"><?= e(order_statuses()[$order['status']] ?? $order['status']) ?></span>
          </div>
          <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
          <div class="work-split">
            <div class="dash-panel">
              <div class="dash-panel-head"><h2>Request</h2></div>
              <div class="p-4">
                <?php if (!empty($order['company_name'])): ?><p class="text-muted sans mb-2"><?= e($order['company_name']) ?></p><?php endif; ?>
                <p class="sans mb-1"><?= e(order_types()[$order['type']] ?? $order['type']) ?><?php if (!empty($order['product_name'])): ?> · <?= e($order['product_name']) ?><?php endif; ?></p>
                <?php if (!empty($order['quantity'])): ?><p class="sans mb-2">Qty: <?= e($order['quantity']) ?><?= !empty($order['unit']) ? ' ' . e($order['unit']) : '' ?></p><?php endif; ?>
                <p class="mb-4"><?= nl2br(e($order['message'])) ?></p>
                <h2 class="h6">Timeline</h2>
                <?php foreach ($events as $event): ?>
                  <div class="border-bottom py-2">
                    <div class="small text-muted"><?= e($event['created_at']) ?><?= !empty($event['actor_name']) ? ' · ' . e($event['actor_name']) : '' ?><?= !empty($event['internal']) ? ' · internal' : '' ?></div>
                    <div><?= e(ucfirst((string) $event['event'])) ?><?php if (!empty($event['body'])): ?> — <?= nl2br(e($event['body'])) ?><?php endif; ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <form method="post" class="work-form">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <h2>Update</h2>
              <p class="work-form-lead">Status and notes for this request.</p>
              <div class="work-fields">
                <label class="work-field">Status
                  <select class="form-select" name="status">
                    <?php foreach (order_statuses() as $value => $label): ?>
                      <option value="<?= e($value) ?>"<?= $order['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <label class="work-field">Assignee
                  <select class="form-select" name="assignee_id">
                    <option value="">Unassigned</option>
                    <?php foreach ($assignees as $person): ?>
                      <option value="<?= e($person['id']) ?>"<?= ($order['assignee_id'] ?? '') === $person['id'] ? ' selected' : '' ?>><?= e($person['full_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <label class="work-field is-wide">Internal note
                  <textarea class="form-control" name="staff_note" rows="3"><?= e((string) ($order['staff_note'] ?? '')) ?></textarea>
                </label>
                <label class="work-field is-wide">Note to partner
                  <textarea class="form-control" name="customer_note" rows="3" placeholder="Optional update they will see"></textarea>
                </label>
              </div>
              <div class="work-actions">
                <button class="btn btn-primary sans">Save</button>
              </div>
            </form>
          </div>
        </section>
        <?php
        render_end();
        exit;
    }

    $status = trim((string) ($_GET['status'] ?? ''));
    $type = trim((string) ($_GET['type'] ?? ''));
    $status = isset(order_statuses()[$status]) ? $status : '';
    $type = isset(order_types()[$type]) ? $type : '';
    $sql = 'SELECT o.*, u.full_name AS partner_name, u.company_name FROM orders o JOIN users u ON u.id = o.user_id';
    $where = [];
    $values = [];
    if ($status !== '') {
        $where[] = 'o.status = ?';
        $values[] = $status;
    }
    if ($type !== '') {
        $where[] = 'o.type = ?';
        $values[] = $type;
    }
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY o.created_at DESC';
    $orders = safe_query(function () use ($sql, $values): array {
        $stmt = db()->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }, []);
    render_start('Orders');
    ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Workspace</p>
          <h1>Orders</h1>
          <p class="dash-lead">Sample, trial, and quote requests from partners.</p>
        </div>
        <form class="d-flex flex-wrap gap-2" method="get">
          <select class="form-select" name="status">
            <option value="">All statuses</option>
            <?php foreach (order_statuses() as $value => $label): ?>
              <option value="<?= e($value) ?>"<?= $status === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select" name="type">
            <option value="">All types</option>
            <?php foreach (order_types() as $value => $label): ?>
              <option value="<?= e($value) ?>"<?= $type === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-dark sans">Filter</button>
        </form>
      </div>
      <div class="dash-panel">
        <div class="table-responsive mb-0">
          <table class="table align-middle sans mb-0">
            <thead><tr><th>Reference</th><th>Partner</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $row): ?>
              <tr>
                <td><a href="<?= e(url('admin/orders/' . $row['reference'])) ?>"><?= e($row['reference']) ?></a></td>
                <td><?= e($row['partner_name']) ?><?= !empty($row['company_name']) ? ' · ' . e($row['company_name']) : '' ?></td>
                <td><?= e(order_types()[$row['type']] ?? $row['type']) ?></td>
                <td><span class="portal-status"><?= e(order_statuses()[$row['status']] ?? $row['status']) ?></span></td>
                <td><?= e(date('j M Y', strtotime((string) $row['created_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="5"><p class="dash-empty mb-0">No orders yet.</p></td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <?php
    render_end();
    exit;
}
