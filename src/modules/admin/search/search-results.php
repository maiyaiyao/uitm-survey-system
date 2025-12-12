<?php
// Path: ../../../config/config.php (up three levels)
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$search_query = $_GET['query'] ?? null;
$results = [];

if ($search_query) {
    $search_param = '%' . $search_query . '%';

    // We construct a massive UNION query to search all tables at once
    // CAST(item_id AS CHAR) is used to ensure all ID types match (User ID is int, others are varchar)
    $sql = "
        (
            -- 1. SEARCH DOMAINS
            SELECT 
                'Domain' AS item_type, 
                d.domain_ID AS item_id, 
                d.domain_name AS item_name, 
                'Domain' AS context_info, 
                CONCAT('criteria/view-criteria.php?id=', d.domain_ID) AS link
            FROM domain d
            WHERE d.domain_name LIKE ? OR d.domain_ID LIKE ?
        )
        UNION ALL
        (
            -- 2. SEARCH CRITERIA
            SELECT 
                'Criteria' AS item_type, 
                c.criteria_ID AS item_id, 
                c.criteria_name AS item_name, 
                CONCAT('Domain: ', d.domain_name) AS context_info, 
                CONCAT('element/view-element.php?id=', c.criteria_ID) AS link
            FROM criteria c
            JOIN domain d ON c.domain_ID = d.domain_ID
            WHERE c.criteria_name LIKE ? OR c.criteria_ID LIKE ?
        )
        UNION ALL
        (
            -- 3. SEARCH ELEMENTS
            SELECT 
                'Element' AS item_type, 
                e.element_ID AS item_id, 
                e.element_name AS item_name, 
                CONCAT('Criteria: ', c.criteria_name) AS context_info,
                CONCAT('element/view-element.php?id=', c.criteria_ID) AS link
            FROM element e
            JOIN criteria c ON e.criteria_ID = c.criteria_ID
            WHERE e.element_name LIKE ? OR e.element_ID LIKE ?
        )
        UNION ALL
        (
            -- 4. SEARCH SURVEYS
            SELECT 
                'Survey' AS item_type, 
                s.survey_ID AS item_id, 
                s.survey_name AS item_name, 
                CONCAT('Dept: ', COALESCE(s.department, 'General')) AS context_info,
                CONCAT('survey/form-survey.php?id=', s.survey_ID) AS link
            FROM survey s
            WHERE s.survey_name LIKE ? OR s.survey_ID LIKE ?
        )
        UNION ALL
        (
            -- 5. SEARCH USERS
            -- Cast ID to char to match other tables
            SELECT 
                'User' AS item_type, 
                CAST(u.user_ID AS CHAR) AS item_id, 
                u.full_name AS item_name, 
                u.primary_email AS context_info,
                CONCAT('user/form-user.php?user_id=', u.user_ID) AS link
            FROM user u
            WHERE u.full_name LIKE ? OR u.primary_email LIKE ?
        )
        ORDER BY item_type, item_name
    ";

    try {
        // IMPORTANT: The order of params must match the '?' in the SQL above exactly
        $params = [
            $search_param, $search_param, // Domain (Name, ID)
            $search_param, $search_param, // Criteria (Name, ID)
            $search_param, $search_param, // Element (Name, ID)
            $search_param, $search_param, // Survey (Name, ID)
            $search_param, $search_param  // User (Full Name, Email)
        ];
        
        $results = $db->fetchAll($sql, $params);

    } catch (Exception $e) {
        setFlashMessage('error', 'An error occurred during the search: ' . $e->getMessage());
    }
}

$flash = getFlashMessage();
$currentPage = basename(__FILE__);
$currentDir = basename(__DIR__);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .main-content { padding-left: calc(16.6667% + 1.5rem); height: 100vh; overflow-y: auto; }
        .card-header.bg-primary, .btn-primary { background-color: #667eea !important; border-color: #667eea !important; }
        .btn-primary:hover { background-color: #764ba2 !important; border-color: #764ba2 !important; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; overflow-y: visible; }
            .main-content { padding-left: 1.5rem; padding-right: 1.5rem; height: auto; }
        }
    </style>
</head>
<body>
    <div class="container-fluid h-100">
        <div class="row h-100">

            <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 offset-lg-2 offset-md-3">
                <div class="main-content px-4 py-3">

                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">Search</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2><i class="bi bi-search me-2 text-primary"></i>Search Results</h2>
                            <?php if ($search_query): ?>
                                <p class="text-muted mb-0">
                                    Showing results for: <strong><?php echo htmlspecialchars($search_query); ?></strong>
                                </p>
                            <?php else: ?>
                                <p class="text-muted mb-0">Please enter a term in the global search bar.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm mb-5">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Found <?php echo count($results); ?> item(s)</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($results) && $search_query): ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-emoji-frown fs-1 text-muted"></i>
                                    <h4 class="mt-3">No Results Found</h4>
                                    <p class="text-muted">No domains, criteria, elements, surveys, or users matched your search term.</p>
                                </div>
                            <?php elseif (!empty($results)): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($results as $item): 
                                        $badge_class = 'bg-secondary';
                                        $icon_class = 'bi-question-circle';
                                        
                                        // Display Logic for Different Types
                                        if ($item['item_type'] === 'Domain') {
                                            $badge_class = 'bg-primary';
                                            $icon_class = 'bi-archive';
                                        } elseif ($item['item_type'] === 'Criteria') {
                                            $badge_class = 'bg-info text-dark';
                                            $icon_class = 'bi-list-check';
                                        } elseif ($item['item_type'] === 'Element') {
                                            $badge_class = 'bg-success';
                                            $icon_class = 'bi-file-text';
                                        } elseif ($item['item_type'] === 'Survey') {
                                            $badge_class = 'bg-warning text-dark';
                                            $icon_class = 'bi-card-checklist';
                                        } elseif ($item['item_type'] === 'User') {
                                            $badge_class = 'bg-dark';
                                            $icon_class = 'bi-person';
                                        }
                                    ?>
                                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">
                                            <div class="flex-grow-1 me-3">
                                                <a href="<?php echo BASE_URL; ?>/modules/admin/<?php echo htmlspecialchars($item['link']); ?>" 
                                                   class="text-decoration-none stretched-link">
                                                    <strong class="mb-1 d-block text-dark">
                                                        <?php echo htmlspecialchars($item['item_id']); ?>
                                                        - <?php echo htmlspecialchars($item['item_name']); ?>
                                                    </strong>
                                                </a>
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    <?php echo htmlspecialchars($item['context_info']); ?>
                                                </small>
                                            </div>
                                            <div class="text-nowrap">
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <i class="bi <?php echo $icon_class; ?> me-1"></i>
                                                    <?php echo $item['item_type']; ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                 </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>