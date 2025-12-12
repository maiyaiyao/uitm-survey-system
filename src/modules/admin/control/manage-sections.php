<?php
// Path: src/modules/admin/control/manage-sections.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// Fetch All Sections
$sections = $db->fetchAll("SELECT * FROM section ORDER BY type, sec_ID ASC");
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sections - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        body.sb-collapsed .main-content-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .table th { background-color: #9d83b7ff; color: black; font-size: 0.75rem; text-transform: uppercase; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
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
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">ISO Controls</a></li>
                            <li class="breadcrumb-item active text-dark">Manage Sections</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">ISO Sections</h3>
                            <p class="text-muted mb-0">Manage high-level ISO categories (e.g., A.5, Clause 4).</p>
                        </div>
                        
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary shadow-sm rounded-3 me-2">
                                <i class="bi bi-arrow-left me-1"></i> Back to Controls
                            </a>
                            <a href="add-section.php" 
                               class="btn btn-primary shadow-sm px-4 rounded-3" 
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Section
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-white py-3"><h5 class="mb-0">Section List</h5></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Section ID</th>
                                        <th>Type</th>
                                        <th>Section Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sections)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted">No sections found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sections as $row): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($row['sec_ID']); ?></td>
                                            <td>
                                                <span class="badge <?php echo ($row['type'] == 'Control') ? 'bg-info-subtle text-info-emphasis' : 'bg-warning-subtle text-warning-emphasis'; ?>">
                                                    <?php echo htmlspecialchars($row['type']); ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['sec_name']); ?></td>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>