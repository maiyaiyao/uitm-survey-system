<?php
// Path: src/modules/admin/iso/view-control.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$sec_id = $_GET['id'] ?? null;

if (!$sec_id) {
    setFlashMessage('error', 'Invalid Section ID.');
    header("Location: index.php");
    exit;
}

// 1. Fetch Section Details
$section = $db->fetchOne("SELECT * FROM section WHERE sec_ID = :id", [':id' => $sec_id]);

if (!$section) {
    setFlashMessage('error', 'Section not found.');
    header("Location: index.php");
    exit;
}

// 2. Fetch Controls (Sub-controls)
// We use a subquery to count mappings in 'element_control' to determine status
$controls = $db->fetchAll("
    SELECT sc.*, 
           (SELECT COUNT(*) FROM element_control ec WHERE ec.sub_con_ID = sc.sub_con_ID) as mapping_count
    FROM sub_con sc 
    WHERE sec_ID = :id 
    ORDER BY CAST(SUBSTRING_INDEX(sub_con_ID, '.', -1) AS UNSIGNED) ASC
", [':id' => $sec_id]);

// 3. Calculate Stats
$total = count($controls);
$mapped_count = 0;
foreach ($controls as $c) {
    if ($c['mapping_count'] > 0) {
        $mapped_count++;
    }
}
$unmapped_count = $total - $mapped_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control <?php echo htmlspecialchars($sec_id); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- LAYOUT STYLES --- */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); transition: margin-left 0.3s ease; }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .main-content { padding: 2rem; }
        
        /* Card Styles */
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: white; padding: 1.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #343a40; margin-bottom: 0; }
        .stat-value.text-success { color: #198754 !important; }
        .stat-value.text-warning { color: #fd7e14 !important; }
        
        /* Table Styles */
        .table-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        .table th { background-color: #9d83b7ff; color: black; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; }
        .table td { vertical-align: middle; padding: 1rem; font-size: 0.9rem; color: #525f7f; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
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
                    
                    <?php 
                    $msg = getFlashMessage();
                    if ($msg): ?>
                        <div class="alert alert-<?php echo ($msg['type']=='error')?'danger':$msg['type']; ?> alert-dismissible fade show mb-4" role="alert">
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center mb-4">
                        <a href="index.php" class="text-decoration-none text-secondary me-3">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </a>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1">
                                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">ISO Standards</a></li>
                                    <li class="breadcrumb-item active">Control <?php echo htmlspecialchars($sec_id); ?></li>
                                </ol>
                            </nav>
                            <h2 class="fw-bold mb-0">
                                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($sec_id); ?></span>
                                <?php echo htmlspecialchars($section['sec_name']); ?>
                            </h2>
                            <p class="text-muted mt-1 mb-0">Annex A Controls for this section</p>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4"><div class="stat-card"><div class="text-muted small fw-bold mb-1">TOTAL</div><div class="stat-value"><?php echo $total; ?></div></div></div>
                        <div class="col-md-4"><div class="stat-card"><div class="text-muted small fw-bold mb-1">MAPPED</div><div class="stat-value text-success"><?php echo $mapped_count; ?></div></div></div>
                        <div class="col-md-4"><div class="stat-card"><div class="text-muted small fw-bold mb-1">UNMAPPED</div><div class="stat-value text-warning"><?php echo $unmapped_count; ?></div></div></div>
                    </div>

                    <div class="mb-4">
                        <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm border-0" placeholder="Search controls...">
                    </div>

                    <div class="card table-card">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Controls List</h6>
                            <span class="text-muted small"><?php echo $total; ?> found</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="conTable">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">ID</th>
                                        <th style="width: 45%;">Control Name</th>
                                        <th style="width: 20%;">Status</th>
                                        <th class="text-end pe-4" style="width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($controls)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">No controls found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($controls as $con): ?>
                                            <?php $isMapped = ($con['mapping_count'] > 0); ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($con['sub_con_ID']); ?></td>
                                                <td class="fw-medium text-dark"><?php echo htmlspecialchars($con['sub_con_name']); ?></td>
                                                <td>
                                                    <?php if ($isMapped): ?>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-success fw-bold small"><i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i> Mapped</span>
                                                            <a href="map_iso.php?type=control&id=<?php echo urlencode($con['sub_con_ID']); ?>" class="text-decoration-none small text-primary mt-1">Edit Link</a>
                                                        </div>
                                                    <?php else: ?>
                                                        <a href="map_iso.php?type=control&id=<?php echo urlencode($con['sub_con_ID']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="bi bi-link-45deg me-1"></i> Map Now
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="edit-control.php?id=<?php echo urlencode($con['sub_con_ID']); ?>" class="btn btn-sm btn-link text-primary me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                                    <button type="button" class="btn btn-sm btn-link text-danger" 
                                                            onclick="openDeleteModal('control', '<?php echo $con['sub_con_ID']; ?>')" 
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
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Are you sure you want to delete this control? <br><strong>This action cannot be undone.</strong></p>
                    <form id="deleteForm" method="POST" action="delete_iso.php">
                        <input type="hidden" name="type" id="delType">
                        <input type="hidden" name="id" id="delId">
                        <input type="hidden" name="redirect_to" value="view-control.php?id=<?php echo urlencode($sec_id); ?>">
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
            let rows = document.querySelectorAll('#conTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Open Delete Modal
        function openDeleteModal(type, id) {
            document.getElementById('delType').value = type;
            document.getElementById('delId').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>