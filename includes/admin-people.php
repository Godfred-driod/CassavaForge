<?php
declare(strict_types=1);

function handle_admin_people(): void
{
    require_owner();
    $me = current_user();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'add_staff') {
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                flash('Provide a name, valid email, and a password of at least 8 characters.', 'error');
            } elseif (find_user_by_email($email)) {
                flash('That email already has an account.', 'error');
            } else {
                db()->prepare('INSERT INTO users (id, email, password_hash, full_name, role, status) VALUES (?, ?, ?, ?, ?, ?)')->execute([uuid(), $email, password_hash($password, PASSWORD_DEFAULT), $name, 'staff', 'active']);
                flash('Staff account created.');
            }
        } elseif ($action === 'set_status') {
            $id = trim((string) ($_POST['id'] ?? ''));
            $status = (string) ($_POST['status'] ?? '');
            $status = in_array($status, ['active', 'disabled'], true) ? $status : '';
            $target = safe_query(function () use ($id) {
                $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                return $stmt->fetch() ?: null;
            }, null);
            if (!$target || $status === '') {
                flash('That person could not be updated.', 'error');
            } elseif ($target['id'] === ($me['id'] ?? '')) {
                flash('You cannot disable your own account.', 'error');
            } elseif ($target['role'] === 'owner') {
                flash('The owner account cannot be disabled here.', 'error');
            } else {
                db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
                flash('Account updated.');
            }
        }
        redirect('admin/people');
    }
    $people = safe_query(fn() => db()->query('SELECT id, email, full_name, role, status, company_name, created_at, last_login_at FROM users ORDER BY role, full_name')->fetchAll(), []);
    render_start('People');
    ?>
    <section class="admin-panel">
      <div class="dash-head">
        <div>
          <p class="eyebrow">Workspace</p>
          <h1>People</h1>
          <p class="dash-lead">Staff and partner accounts. Only the owner can add staff.</p>
        </div>
      </div>
      <?php if ($notice = take_flash()): ?><div class="alert <?= $notice['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> sans"><?= e($notice['message']) ?></div><?php endif; ?>
      <div class="work-split">
        <div class="dash-panel">
          <div class="dash-panel-head">
            <h2>Accounts</h2>
            <span class="sans text-muted"><?= e((string) count($people)) ?></span>
          </div>
          <div class="table-responsive mb-0">
            <table class="table align-middle sans mb-0">
              <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($people as $person): ?>
                <tr>
                  <td><?= e($person['full_name']) ?><?= !empty($person['company_name']) ? '<div class="small text-muted">' . e($person['company_name']) . '</div>' : '' ?></td>
                  <td><?= e($person['email']) ?></td>
                  <td><?= e($person['role']) ?></td>
                  <td><span class="portal-status"><?= e($person['status']) ?></span></td>
                  <td>
                    <?php if ($person['role'] !== 'owner' && $person['id'] !== ($me['id'] ?? '')): ?>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e($person['id']) ?>">
                        <input type="hidden" name="status" value="<?= $person['status'] === 'active' ? 'disabled' : 'active' ?>">
                        <button class="btn btn-sm btn-outline-dark" name="action" value="set_status"><?= $person['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <form method="post" class="work-form">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <h2>Add staff</h2>
          <p class="work-form-lead">Staff can manage products, journal, messages, traffic, and orders. They cannot add other staff.</p>
          <div class="work-fields">
            <label class="work-field">Name
              <input class="form-control" name="full_name" required maxlength="200">
            </label>
            <label class="work-field">Email
              <input class="form-control" type="email" name="email" required maxlength="254">
            </label>
            <label class="work-field is-wide">Password
              <input class="form-control" type="password" name="password" minlength="8" required>
            </label>
          </div>
          <div class="work-actions">
            <button class="btn btn-primary sans" name="action" value="add_staff">Create staff account</button>
          </div>
        </form>
      </div>
    </section>
    <?php
    render_end();
    exit;
}
