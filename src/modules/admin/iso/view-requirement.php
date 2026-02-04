<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$sec_id = $_GET['id'] ?? null;

if (!$sec_id) {
    setFlashMessage('error', 'Invalid Section ID.');
    header("Location: index.php");
    exit;
}

// 1. Fetch Section
$section = $db->fetchOne("SELECT * FROM section WHERE sec_ID = :id", [':id' => $sec_id]);
if (!$section) {
    setFlashMessage('error', 'Section not found.');
    header("Location: index.php");
    exit;
}

// 2. Fetch Requirements
$requirements = $db->fetchAll("
    SELECT * FROM sub_req 
    WHERE sec_ID = :id 
    ORDER BY CAST(SUBSTRING_INDEX(sub_req_ID, '.', -1) AS UNSIGNED) ASC
", [':id' => $sec_id]);

// 3. Stats
$total = count($requirements);
$mapped_count = 0;
foreach ($requirements as $r) { if (!empty($r['criteria_ID'])) $mapped_count++; }
$unmapped_count = $total - $mapped_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section <?php echo htmlspecialchars($sec_id); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- Styles from ISO Index Page --- */
        
        /* Layout & General */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); transition: margin-left 0.3s ease; }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .main-content { padding: 2rem; }

        /* Table Styles */
        .table th { font-weight: 700; background-color: #9d83b7ff; border-bottom: 2px solid #f0f2f5; color: black; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }

        /* Card & Button Styles */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { color: white; opacity: 0.9; }

        /* Specific styles for this page's stats */
        .stat-card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); background: white; padding: 1.5rem; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-value { font-size: 2rem; font-weight: 700; color: #343a40; margin-bottom: 0; }
        .stat-label { color: #8898aa; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }

        /* Search Input Styling */
        .search-input { border-radius: 50px; padding-left: 20px; border: 1px solid #e9ecef; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .search-input:focus { box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); border-color: #5e72e4; }
    </style>
</head>
<body>
    
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">ISO Standards</a></li>
                            <li class="breadcrumb-item active text-dark">Section <?php echo htmlspecialchars($sec_id); ?></li>
                        </ol>
                    </nav>

                    <?php 
                    $msg = getFlashMessage();
                    if ($msg): 
                        $alertClass = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
                    ?>
                        <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                            <?php if($msg['type'] === 'success'): ?>
                                <i class="bi bi-check-circle-fill me-2"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php endif; ?>
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center mb-4">
                        <a href="index.php" class="btn btn-white shadow-sm rounded-circle me-3 text-secondary" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>
                        <div>
                            <h2 class="fw-bold mb-0 text-dark">
                                <span class="badge bg-primary rounded-3 me-2" style="font-size: 0.6em; vertical-align: middle;"><?php echo htmlspecialchars($sec_id); ?></span>
                                <?php echo htmlspecialchars($section['sec_name']); ?>
                            </h2>
                            <p class="text-muted mt-1 mb-0">Manage requirements (clauses) for this section.</p>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label mb-2">Total Requirements</div>
                                <div class="stat-value"><?php echo $total; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label mb-2 text-success">Mapped</div>
                                <div class="stat-value text-success"><?php echo $mapped_count; ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label mb-2 text-warning">Unmapped</div>
                                <div class="stat-value text-warning"><?php echo $unmapped_count; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <input type="text" id="searchInput" class="form-control form-control-lg search-input" placeholder="Search requirements...">
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Requirements List</h5>
                            <span class="badge bg-light text-secondary border"><?php echo $total; ?> records</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="reqTable">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">ID</th>
                                        <th style="width: 45%;">Requirement Name</th>
                                        <th style="width: 20%;">Status</th>
                                        <th class="text-end pe-4" style="width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($requirements)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                No requirements found for this section.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($requirements as $req): ?>
                                            <?php $isMapped = !empty($req['criteria_ID']); ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($req['sub_req_ID']); ?></td>
                                                <td class="fw-semibold text-secondary"><?php echo htmlspecialchars($req['sub_req_name']); ?></td>
                                                <td>
                                                    <?php if ($isMapped): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Mapped
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Unmapped</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-requirement.php?id=<?php echo urlencode($req['sub_req_ID']); ?>" 
                                                       class="btn btn-sm btn-link text-primary" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-link text-danger" 
                                                            onclick="confirmDelete('requirement', '<?php echo $req['sub_req_ID']; ?>')" 
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Are you sure you want to delete this requirement? <br><strong>This action cannot be undone.</strong></p>
                    <form id="deleteForm" method="POST" action="delete_iso.php">
                        <input type="hidden" name="type" id="delType">
                        <input type="hidden" name="id" id="delId">
                        <input type="hidden" name="redirect_to" value="view-requirement.php?id=<?php echo urlencode($sec_id); ?>">
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteForm').submit()">Delete It</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search Filter
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#reqTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Open Delete Modal
        function confirmDelete(type, id) {
            document.getElementById('delType').value = type;
            document.getElementById('delId').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>