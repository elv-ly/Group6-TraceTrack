<?php
$title = "Manage Users";
require_once __DIR__ . '/../../autoload.php';
requireAdmin();

$me      = sessionUser();
$userObj = new User($db);
$users   = $userObj->getAllUsers();

ob_start();
?>

<div class="tt-page-header">
    <div><h1>Manage Users</h1><p>View and manage all registered SLSU Main Campus accounts.</p></div>
</div>

<?= csrf_field() ?>

<div class="tt-card">
    <div class="tt-card-header">
        <h5><i class="bi bi-people"></i> All Users</h5>
        <span class="tt-badge-count"><?= count($users) ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="table tt-table">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Role</th>
                    <th>ID Number</th><th>Contact</th>
                    <th>Registered</th><th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                <?php else: foreach ($users as $row): ?>
                <tr>
                    <td class="fw-500"><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php if ($row['role'] === 'admin'): ?>
                            <span class="tt-badge tt-badge-gold"><i class="bi bi-shield-fill"></i> Admin</span>
                        <?php elseif ($row['role'] === 'faculty'): ?>
                            <span class="tt-badge tt-badge-blue"><i class="bi bi-person-badge"></i> Faculty</span>
                        <?php else: ?>
                            <span class="tt-badge tt-badge-muted"><i class="bi bi-person"></i> Student</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['id_number']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <?= $row['is_active']
                            ? "<span class='tt-badge tt-badge-green'><i class='bi bi-check-circle'></i> Active</span>"
                            : "<span class='tt-badge tt-badge-red'><i class='bi bi-x-circle'></i> Deactivated</span>" ?>
                    </td>
                    <td class="text-center">
                        <?php if ($row['role'] !== 'admin'): ?>
                            <?php if ($row['is_active']): ?>
                            <button class="tt-btn-outline-sm tt-deactivate-user"
                                    style="border-color:rgba(198,40,40,.4);color:#EF9A9A;"
                                    data-id="<?= encryptId($row['user_id']) ?>"
                                    data-name="<?= htmlspecialchars($row['full_name']) ?>">
                                <i class="bi bi-person-x"></i> Deactivate
                            </button>
                            <?php else: ?>
                            <button class="tt-btn-outline-sm tt-activate-user"
                                    style="border-color:rgba(46,125,50,.4);color:#A5D6A7;"
                                    data-id="<?= encryptId($row['user_id']) ?>"
                                    data-name="<?= htmlspecialchars($row['full_name']) ?>">
                                <i class="bi bi-person-check"></i> Activate
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.tt-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.tt-badge-red{background:rgba(198,40,40,.18);color:#EF9A9A;}
.tt-badge-green{background:rgba(46,125,50,.18);color:#A5D6A7;}
.tt-badge-blue{background:rgba(21,101,192,.18);color:#90CAF9;}
.tt-badge-gold{background:rgba(232,168,56,.18);color:#FFE082;}
.tt-badge-muted{background:rgba(255,255,255,.07);color:var(--text-muted);}
.tt-badge-count{font-size:.82rem;color:var(--text-muted);}
.tt-modal{background:var(--card-bg);color:var(--text);border:1px solid var(--border);}
.fw-500{font-weight:500;}
</style>

<script src="/views/admin/admin-users-js.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
