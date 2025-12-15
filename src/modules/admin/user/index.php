<?php
// Path: user/index.php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php';

// CRITICAL ISO CONTROL: Require admin role for IAM functions
requireRole(['admin']);

$current_user = getCurrentUser();

// --- 1. Database Connection and Data Fetch ---
try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // --- Lifecycle Management: Periodic Review ---
    // Fetch all users. JOIN with user_role and role tables to get the user's role name.
    $sql = "
        SELECT 
            u.user_ID, u.full_name, u.primary_email, u.status, u.created_at, u.last_login,
            r.role_name
        FROM 
            user u
        JOIN 
            user_role ur ON u.user_ID = ur.user_ID
        JOIN 
            role r ON ur.role_ID = r.role_ID
        ORDER BY 
            u.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Database Error: Could not load user list. " . $e->getMessage();
    setFlashMessage("danger", $message);
    $users = [];
}

// --- 2. Handle User Actions (Disable/Enable/Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['user_id'])) {
    $target_user_id = (int)$_GET['user_id'];
    $action = $_GET['action'];
    
    // Check for self-action protection
    if ($target_user_id == $current_user['user_ID']) {
        setFlashMessage('danger', 'Error: You cannot modify your own active account status from this panel.');
        header('Location: index.php');
        exit;
    }

    try {
        if ($action === 'delete') {
             // Handle Delete Logic
             $stmt = $pdo->prepare("DELETE FROM user WHERE user_ID = ?");
             $stmt->execute([$target_user_id]);
             $message = "User ID {$target_user_id} successfully deleted.";
             $message_type = "success";

        } else {
            // Handle Status Toggle
            $new_status = ($action == 'disable') ? 'Inactive' : 'Active';
            $stmt = $pdo->prepare("UPDATE user SET status = ? WHERE user_ID = ?");
            $stmt->execute([$new_status, $target_user_id]);
            
            $msg_verb = ($action == 'disable') ? 'disabled (access revoked)' : 'enabled (access restored)';
            $message = "User ID {$target_user_id} successfully {$msg_verb}.";
            $message_type = ($action == 'disable') ? "warning" : "success";
        }
        
        setFlashMessage($message_type, $message);
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        setFlashMessage('danger', "Failed to perform action on user: " . $e->getMessage());
        header("Location: index.php");
        exit;
    }
}

// --- 3. Helper functions for badges ---
function getStatusBadge($status) {
    $classes = [
        'Active' => 'bg-success-subtle text-success-emphasis',
        'Inactive' => 'bg-warning-subtle text-warning-emphasis',
    ];
    $class = $classes[$status] ?? 'bg-secondary-subtle text-secondary-emphasis';
    return '<span class="badge rounded-pill ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

function getRoleBadge($role) {
    $classes = [
        'admin' => 'bg-danger-subtle text-danger-emphasis',
        'auditor' => 'bg-primary-subtle text-primary-emphasis',
        'user' => 'bg-secondary-subtle text-secondary-emphasis',
    ];
    $class = $classes[strtolower($role)] ?? 'bg-info-subtle text-info-emphasis';
    return '<span class="badge rounded-pill ' . $class . '">' . htmlspecialchars(ucfirst($role)) . '</span>';
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php include_once __DIR__ . '/../../includes/admin_header.php'; ?>
    <style>
        /* Page Layout - Sidebar styles handled by responsive.css */

        /* Table Styles */
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff; /* Purple Header */
            border-bottom: 2px solid #f0f2f5;
            color: black;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: #67748e; /* Specific Grey Text */
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* User Meta Data */
        .user-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }
        .user-meta .name {
            font-weight: 600;
            color: #344767;
        }
        .user-meta .email {
            font-size: 0.75rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-md-2 col-lg-2 sidebar">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">User Management</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">User Management</h3>
                            <p class="text-muted mb-0">Manage system access, roles, and account lifecycles.</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="form-user.php" 
                                class="btn btn-primary shadow-sm px-4 py-2 rounded-3" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-person-plus-fill me-2"></i>Provision New User
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">Registered Users List</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">User Info</th>
                                        <th class="text-center">Role</th>
                                        <th class="text-center">Status</th>
                                        <th>Last Login</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-person-x display-4 d-block mb-2"></i>
                                                    No users found in the system.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 text-primary fw-bold" style="width: 40px; height: 40px; border: 1px solid #e9ecef;">
                                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <div class="user-meta">
                                                            <span class="name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                                            <span class="email"><?php echo htmlspecialchars($user['primary_email']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <?php echo getRoleBadge($user['role_name']); ?>
                                                </td>

                                                <td class="text-center">
                                                    <?php echo getStatusBadge($user['status']); ?>
                                                </td>

                                                <td>
                                                    <small class="text-secondary fw-semibold">
                                                        <?php echo $user['last_login'] ? date('d M Y H:i A', strtotime($user['last_login'])) : '-'; ?>
                                                    </small>
                                                </td>
                                                
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <a href="form-user.php?user_id=<?php echo $user['user_ID']; ?>" 
                                                           class="btn btn-sm btn-link text-primary px-2" 
                                                           title="Edit User">
                                                            <i class="bi bi-pencil-square fs-6"></i>
                                                        </a>

                                                        <?php if ($user['user_ID'] != $current_user['user_ID']): ?>
                                                            <?php if ($user['status'] === 'Active'): ?>
                                                                <a href="index.php?action=disable&user_id=<?php echo $user['user_ID']; ?>" 
                                                                   class="btn btn-sm btn-link text-warning px-2"
                                                                   onclick="return confirm('Disable this user? Access will be revoked.');"
                                                                   title="Disable Account">
                                                                    <i class="bi bi-slash-circle fs-6"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="index.php?action=enable&user_id=<?php echo $user['user_ID']; ?>" 
                                                                   class="btn btn-sm btn-link text-success px-2" 
                                                                   title="Enable Account">
                                                                    <i class="bi bi-check-circle fs-6"></i>
                                                                </a>
                                                            <?php endif; ?>

                                                            <button class="btn btn-sm btn-link text-danger px-2" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#deleteUserModal"
                                                                    data-bs-user-id="<?php echo $user['user_ID']; ?>"
                                                                    data-bs-username="<?php echo htmlspecialchars($user['full_name']); ?>"
                                                                    title="Delete User">
                                                                <i class="bi bi-trash fs-6"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Cannot modify own account">
                                                                <button class="btn btn-sm btn-link text-secondary px-2" disabled>
                                                                    <i class="bi bi-lock fs-6"></i>
                                                                </button>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="deleteUserForm" method="GET" action="index.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_user_ID" name="user_id" value="">
                    
                    <div class="modal-header border-bottom-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="text-danger mb-3">
                            <i class="bi bi-exclamation-triangle display-1"></i>
                        </div>
                        <h4 class="mb-2">Are you sure?</h4>
                        <p class="text-muted px-4">
                            Do you really want to permanently delete the user "<strong id="deleteUserName" class="text-dark"></strong>"? 
                            This action is irreversible and violates the ISO lifecycle logs if not documented.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-top-0 pt-0 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Delete User Modal Logic
        const deleteUserModal = document.getElementById('deleteUserModal');
        if (deleteUserModal) {
            deleteUserModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-bs-user-id');
                const userName = button.getAttribute('data-bs-username');

                document.getElementById('delete_user_ID').value = userId;
                document.getElementById('deleteUserName').textContent = userName;
            });
        }
    </script>
</body>
</html>