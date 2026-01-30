<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$flash = getFlashMessage(); 

// 1. HANDLE DELETION 
if (isset($_GET['delete_org'])) {
    try {
        $id = (int)$_GET['delete_org'];
        $db->query("DELETE FROM organization WHERE org_ID = ?", [$id]);
        setFlashMessage('success', 'Organization and all its departments deleted successfully.');
        redirect('index.php');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting organization: ' . $e->getMessage()); // Changed 'danger' to 'error' to match system standard
    }
}

if (isset($_GET['delete_dept'])) {
    try {
        $id = (int)$_GET['delete_dept'];
        $db->query("DELETE FROM department WHERE dept_ID = ?", [$id]);
        setFlashMessage('success', 'Department deleted successfully.');
        redirect('index.php');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting department: ' . $e->getMessage());
    }
}

// 2. HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'];
    
    try {
        if ($form_type === 'org') {
            $name = sanitize($_POST['org_name']);
            $code = sanitize($_POST['org_code']);
            $status = $_POST['status'];
            $edit_id = $_POST['org_id'] ?? null;

            if (!in_array($status, ['Active', 'Inactive'])) throw new Exception('Invalid status.');

            $checkSql = "SELECT COUNT(*) as count FROM organization WHERE org_name = ?" . ($edit_id ? " AND org_ID != ?" : "");
            $checkParams = $edit_id ? [$name, $edit_id] : [$name];
            if ($db->fetchOne($checkSql, $checkParams)['count'] > 0) throw new Exception("Organization '$name' already exists.");

            if ($edit_id) {
                $db->query("UPDATE organization SET org_name = ?, org_code = ?, status = ? WHERE org_ID = ?", [$name, $code, $status, $edit_id]);
                setFlashMessage('success', 'Organization updated.');
            } else {
                $db->query("INSERT INTO organization (org_name, org_code, status) VALUES (?, ?, ?)", [$name, $code, $status]);
                setFlashMessage('success', 'Organization added.');
            }

        } elseif ($form_type === 'dept') {
            $name = sanitize($_POST['dept_name']);
            $code = sanitize($_POST['dept_code']);
            $org_id = (int)$_POST['org_id'];
            $status = $_POST['status'];
            $edit_id = $_POST['dept_id'] ?? null;

            if (empty($org_id)) throw new Exception('Parent Organization is required.');
            
            $checkSql = "SELECT COUNT(*) as count FROM department WHERE dept_name = ? AND org_ID = ?" . ($edit_id ? " AND dept_ID != ?" : "");
            $checkParams = $edit_id ? [$name, $org_id, $edit_id] : [$name, $org_id];
            if ($db->fetchOne($checkSql, $checkParams)['count'] > 0) throw new Exception("Department '$name' already exists in this Organization.");

            if ($edit_id) {
                $db->query("UPDATE department SET dept_name = ?, dept_code = ?, org_ID = ?, status = ? WHERE dept_ID = ?", [$name, $code, $org_id, $status, $edit_id]);
                setFlashMessage('success', 'Department updated.');
            } else {
                $db->query("INSERT INTO department (dept_name, dept_code, org_ID, status) VALUES (?, ?, ?, ?)", [$name, $code, $org_id, $status]);
                setFlashMessage('success', 'Department added.');
            }
        }
        redirect('index.php');

    } catch (Exception $e) {
        setFlashMessage('error', 'Error: ' . $e->getMessage());
    }
}

// 3. FETCH DATA 
$orgs = $db->fetchAll("SELECT * FROM organization ORDER BY org_name ASC");
$depts = $db->fetchAll("SELECT * FROM department ORDER BY dept_name ASC");

$grouped_depts = [];
foreach ($depts as $d) {
    $grouped_depts[$d['org_ID']][] = $d;
}

// 4. DETERMINE EDIT MODE
$active_tab = 'org';
$edit_org = null;
$edit_dept = null;

if (isset($_GET['edit_org'])) {
    $edit_org = $db->fetchOne("SELECT * FROM organization WHERE org_ID = ?", [$_GET['edit_org']]);
    $active_tab = 'org';
} elseif (isset($_GET['edit_dept'])) {
    $edit_dept = $db->fetchOne("SELECT * FROM department WHERE dept_ID = ?", [$_GET['edit_dept']]);
    $active_tab = 'dept';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Structure Configuration - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Layout & General (Matched to ISO Module) */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Table Styles */
        .table th { font-weight: 700; background-color: #9d83b7ff; border-bottom: 2px solid #f0f2f5; color: black; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }

        /* Card & Button Styles */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { color: white; opacity: 0.9; }

        /* Custom Tabs Styling */
        .nav-tabs { border-bottom: 2px solid #e9ecef; }
        .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 1rem 1.5rem; transition: all 0.2s; }
        .nav-tabs .nav-link:hover { color: #5e72e4; }
        .nav-tabs .nav-link.active { color: #5e72e4; border-bottom: 3px solid #5e72e4; background: transparent; }

        /* Badge Styling for Departments */
        .dept-badge { 
            cursor: pointer; 
            transition: all 0.2s; 
            color: #525f7f !important;
            border: 1px solid #dee2e6 !important;
            background-color: #fff;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .dept-badge:hover { 
            transform: translateY(-1px); 
            background-color: #f8f9fa !important;
            border-color: #5e72e4 !important;
            color: #5e72e4 !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">Organization Structure</li>
                        </ol>
                    </nav>

                    <?php 
                    $msg = getFlashMessage();
                    if ($msg): 
                        $alertClass = ($msg['type'] === 'error' || $msg['type'] === 'danger') ? 'danger' : $msg['type'];
                    ?>
                        <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show shadow-sm" role="alert">
                            <?php if($msg['type'] === 'success'): ?>
                                <i class="bi bi-check-circle-fill me-2"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php endif; ?>
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">Organization Structure</h3>
                            <p class="text-muted mb-0">Manage Organizations and their sub-departments.</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $active_tab == 'org' ? 'active' : ''; ?>" 
                                               href="#tab-org" data-bs-toggle="tab">
                                               <?php echo $edit_org ? 'Edit Org' : 'Add Org'; ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo $active_tab == 'dept' ? 'active' : ''; ?>" 
                                               href="#tab-dept" data-bs-toggle="tab">
                                               <?php echo $edit_dept ? 'Edit Dept' : 'Add Dept'; ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane fade <?php echo $active_tab == 'org' ? 'show active' : ''; ?>" id="tab-org">
                                            <form method="POST" action="index.php">
                                                <input type="hidden" name="form_type" value="org">
                                                <?php if ($edit_org): ?>
                                                    <input type="hidden" name="org_id" value="<?php echo $edit_org['org_ID']; ?>">
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Organization Name</label>
                                                    <input type="text" class="form-control" name="org_name" required 
                                                           value="<?php echo $edit_org ? htmlspecialchars($edit_org['org_name']) : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Short Code (e.g., UiTM)</label>
                                                    <input type="text" class="form-control" name="org_code" required 
                                                           value="<?php echo $edit_org['org_code'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label small fw-bold text-muted">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="Active" <?php echo ($edit_org && $edit_org['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?php echo ($edit_org && $edit_org['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-gradient-primary shadow-sm">
                                                        <?php echo $edit_org ? 'Update Organization' : 'Save Organization'; ?>
                                                    </button>
                                                    <?php if ($edit_org || $edit_dept): ?>
                                                        <a href="index.php" class="btn btn-light text-muted">Cancel / Reset</a>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane fade <?php echo $active_tab == 'dept' ? 'show active' : ''; ?>" id="tab-dept">
                                            <form method="POST" action="index.php">
                                                <input type="hidden" name="form_type" value="dept">
                                                <?php if ($edit_dept): ?>
                                                    <input type="hidden" name="dept_id" value="<?php echo $edit_dept['dept_ID']; ?>">
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Parent Organization <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="org_id" required>
                                                        <option value="">-- Select --</option>
                                                        <?php foreach ($orgs as $o): ?>
                                                            <option value="<?php echo $o['org_ID']; ?>"
                                                                <?php echo ($edit_dept && $edit_dept['org_ID'] == $o['org_ID']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($o['org_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Department Name</label>
                                                    <input type="text" class="form-control" name="dept_name" required 
                                                           value="<?php echo $edit_dept ? htmlspecialchars($edit_dept['dept_name']) : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Short Code (e.g., HR)</label>
                                                    <input type="text" class="form-control" name="dept_code" required 
                                                           value="<?php echo $edit_dept['dept_code'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label small fw-bold text-muted">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="Active" <?php echo ($edit_dept && $edit_dept['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?php echo ($edit_dept && $edit_dept['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-gradient-primary shadow-sm">
                                                        <?php echo $edit_dept ? 'Update Department' : 'Save Department'; ?>
                                                    </button>
                                                    <?php if ($edit_org || $edit_dept): ?>
                                                        <a href="index.php" class="btn btn-light text-muted">Cancel / Reset</a>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0">Overview</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead>
                                            <tr>
                                                <th class="ps-4" style="width: 30%;">Organization</th>
                                                <th>Departments (Click to Edit)</th>
                                                <th class="text-end pe-4" style="width: 15%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($orgs)): ?>
                                                <tr><td colspan="3" class="text-center py-5 text-muted">No data found.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($orgs as $org): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($org['org_name']); ?></div>
                                                            <div class="small text-muted"> 
                                                                Code: <?php echo htmlspecialchars($org['org_code']); ?> | 
                                                                <span class="badge <?php echo $org['status'] == 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?> border border-0">
                                                                    <?php echo $org['status']; ?>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <?php 
                                                            $my_depts = $grouped_depts[$org['org_ID']] ?? []; 
                                                            if (empty($my_depts)):
                                                            ?>
                                                                <span class="small text-muted fst-italic">No departments yet</span>
                                                            <?php else: ?>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php foreach ($my_depts as $dept): ?>
                                                                        <div class="btn-group" role="group">
                                                                            <a href="index.php?edit_dept=<?php echo $dept['dept_ID']; ?>" 
                                                                               class="text-decoration-none badge rounded-pill dept-badge"
                                                                               title="Edit Department">
                                                                                <?php echo htmlspecialchars($dept['dept_name']); ?>
                                                                            </a>
                                                                            <a href="index.php?delete_dept=<?php echo $dept['dept_ID']; ?>" 
                                                                               onclick="return confirm('Delete department: <?php echo htmlspecialchars($dept['dept_name']); ?>?');"
                                                                               class="badge rounded-pill bg-white text-danger border border-start-0 ps-1 pe-2 d-flex align-items-center" 
                                                                               style="margin-left: -5px; border-color: #dee2e6 !important;"
                                                                               title="Delete Dept">
                                                                                <i class="bi bi-x"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-end pe-4">
                                                            <a href="index.php?edit_org=<?php echo $org['org_ID']; ?>" class="btn btn-sm btn-link text-primary" title="Edit Organization">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <a href="index.php?delete_org=<?php echo $org['org_ID']; ?>" 
                                                               onclick="return confirm('WARNING: Deleting this Organization will also delete ALL its departments!\n\nAre you sure you want to delete <?php echo htmlspecialchars($org['org_name']); ?>?');"
                                                               class="btn btn-sm btn-link text-danger" title="Delete Organization">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>