<?php
// Path: modules/admin/organization/index.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$flash = getFlashMessage();

// --- 1. HANDLE DELETION (GET REQUEST) ---
if (isset($_GET['delete_org'])) {
    try {
        $id = (int)$_GET['delete_org'];
        // Note: SQL Foreign Keys are set to CASCADE, so this deletes linked Departments too.
        $db->query("DELETE FROM organization WHERE org_ID = ?", [$id]);
        setFlashMessage('success', 'Organization and all its departments deleted successfully.');
        redirect('index.php');
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error deleting organization: ' . $e->getMessage());
    }
}

if (isset($_GET['delete_dept'])) {
    try {
        $id = (int)$_GET['delete_dept'];
        $db->query("DELETE FROM department WHERE dept_ID = ?", [$id]);
        setFlashMessage('success', 'Department deleted successfully.');
        redirect('index.php');
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error deleting department: ' . $e->getMessage());
    }
}

// --- 2. HANDLE FORM SUBMISSION (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'];
    
    try {
        if ($form_type === 'org') {
            // Organization Logic
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
            // Department Logic
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
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }
}

// --- 3. FETCH DATA ---
$orgs = $db->fetchAll("SELECT * FROM organization ORDER BY org_name ASC");
$depts = $db->fetchAll("SELECT * FROM department ORDER BY dept_name ASC");

$grouped_depts = [];
foreach ($depts as $d) {
    $grouped_depts[$d['org_ID']][] = $d;
}

// --- 4. DETERMINE EDIT MODE ---
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
    <title>Structure Configuration - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* PURPLE HEADER STYLE */
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
            color: #67748e;
            font-size: 0.875rem;
        }

        /* Badge Styling */
        .dept-badge { 
            cursor: pointer; 
            transition: all 0.2s; 
            color: #000 !important;
            border: 1px solid #dee2e6 !important;
            background-color: #f8f9fa;
        }
        .dept-badge:hover { 
            transform: translateY(-1px); 
            background-color: #e9ecef !important;
            border-color: #adb5bd !important;
        }

        /* Delete Button Hover */
        .btn-delete:hover {
            background-color: #dc3545;
            color: white !important;
            border-color: #dc3545;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-2 sidebar">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            <div class="col-md-10 main-content-wrapper">
                <div class="container py-4">
                    <h3 class="fw-bold mb-4 text-black">Organizational Structure</h3>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                <div class="card-header p-0">
                                    <ul class="nav nav-tabs nav-fill card-header-tabs m-0">
                                        <li class="nav-item">
                                            <a class="nav-link fw-bold py-3 text-black <?php echo $active_tab == 'org' ? 'active' : ''; ?>" 
                                               href="#tab-org" data-bs-toggle="tab">
                                               <?php echo $edit_org ? 'Edit Organization' : 'Add Organization'; ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link fw-bold py-3 text-black <?php echo $active_tab == 'dept' ? 'active' : ''; ?>" 
                                               href="#tab-dept" data-bs-toggle="tab">
                                               <?php echo $edit_dept ? 'Edit Department' : 'Add Department'; ?>
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
                                                    <label class="form-label small fw-bold text-black">Name</label>
                                                    <input type="text" class="form-control" name="org_name" required 
                                                           value="<?php echo $edit_org ? htmlspecialchars($edit_org['org_name']) : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-black">Short Code</label>
                                                    <input type="text" class="form-control" name="org_code" required 
                                                           value="<?php echo $edit_org['org_code'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-black">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="Active" <?php echo ($edit_org && $edit_org['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?php echo ($edit_org && $edit_org['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                        Save Organization
                                                    </button>
                                                    <?php if ($edit_org || $edit_dept): ?>
                                                        <a href="index.php" class="btn btn-outline-dark">Clear / Reset</a>
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
                                                    <label class="form-label small fw-bold text-black">Parent Organization <span class="text-danger">*</span></label>
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
                                                    <label class="form-label small fw-bold text-black">Department Name</label>
                                                    <input type="text" class="form-control" name="dept_name" required 
                                                           value="<?php echo $edit_dept ? htmlspecialchars($edit_dept['dept_name']) : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-black">Short Code</label>
                                                    <input type="text" class="form-control" name="dept_code" required 
                                                           value="<?php echo $edit_dept['dept_code'] ?? ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-black">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="Active" <?php echo ($edit_dept && $edit_dept['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                        <option value="Inactive" <?php echo ($edit_dept && $edit_dept['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                        Save Department
                                                    </button>
                                                    <?php if ($edit_org || $edit_dept): ?>
                                                        <a href="index.php" class="btn btn-outline-dark">Clear / Reset</a>
                                                    <?php endif; ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card shadow-sm border-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="table-purple">
                                            <tr>
                                                <th class="ps-4" style="width: 35%;">Organization</th>
                                                <th>Departments (Click to Edit)</th>
                                                <th class="text-end pe-4" style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($orgs)): ?>
                                                <tr><td colspan="3" class="text-center py-4 text-black">No data found.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($orgs as $org): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="fw-bold text-black"><?php echo htmlspecialchars($org['org_name']); ?></div>
                                                            <div class="small text-black"> 
                                                                Code: <?php echo htmlspecialchars($org['org_code']); ?> | 
                                                                <span class="text-<?php echo $org['status'] == 'Active' ? 'success' : 'secondary'; ?>">
                                                                    <?php echo $org['status']; ?>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        
                                                        <td>
                                                            <?php 
                                                            $my_depts = $grouped_depts[$org['org_ID']] ?? []; 
                                                            if (empty($my_depts)):
                                                            ?>
                                                                <span class="small fst-italic text-black">No departments yet</span>
                                                            <?php else: ?>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php foreach ($my_depts as $dept): ?>
                                                                        <div class="d-flex align-items-center">
                                                                            <a href="index.php?edit_dept=<?php echo $dept['dept_ID']; ?>" 
                                                                               class="badge rounded-pill text-decoration-none dept-badge me-1"
                                                                               title="Edit Department">
                                                                                <?php echo htmlspecialchars($dept['dept_name']); ?>
                                                                            </a>
                                                                            <a href="index.php?delete_dept=<?php echo $dept['dept_ID']; ?>" 
                                                                               onclick="return confirm('Delete department: <?php echo htmlspecialchars($dept['dept_name']); ?>?');"
                                                                               class="text-danger small text-decoration-none" title="Delete Dept">
                                                                                <i class="bi bi-x-circle-fill"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-end pe-4">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="index.php?edit_org=<?php echo $org['org_ID']; ?>" class="btn btn-outline-dark" title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <a href="index.php?delete_org=<?php echo $org['org_ID']; ?>" 
                                                                   onclick="return confirm('WARNING: Deleting this Organization will also delete ALL its departments!\n\nAre you sure you want to delete <?php echo htmlspecialchars($org['org_name']); ?>?');"
                                                                   class="btn btn-outline-danger btn-delete" title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </a>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>