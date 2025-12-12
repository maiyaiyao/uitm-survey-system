<?php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php';

// Require admin role
requireRole(['admin']);

$current_user = getCurrentUser();
$pdo = (new Database())->getConnection();

// 1. Validate Survey ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$survey_id = $_GET['id'];

// 2. Fetch Survey Details
$stmt_survey = $pdo->prepare("SELECT * FROM survey WHERE survey_ID = ?");
$stmt_survey->execute([$survey_id]);
$survey = $stmt_survey->fetch(PDO::FETCH_ASSOC);
if (!$survey) {
    die("Survey not found.");
}

// 3. Fetch Hierarchical Data
$sql = "
    SELECT 
        d.domain_ID, d.domain_name,
        c.criteria_ID, c.criteria_name,
        e.element_ID, e.element_name,
        s.score_level, s.desc_level,
        se.se_ID, se.details
    FROM survey_domain sd
    JOIN domain d ON sd.domain_ID = d.domain_ID
    JOIN criteria c ON d.domain_ID = c.domain_ID
    JOIN element e ON c.criteria_ID = e.criteria_ID
    LEFT JOIN score_element se ON e.element_ID = se.element_ID
    LEFT JOIN score s ON se.score_ID = s.score_ID
    WHERE sd.survey_ID = ? 
    AND c.status = 'Active' 
    AND e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID, s.score_level ASC
";

$stmt_data = $pdo->prepare($sql);
$stmt_data->execute([$survey_id]);
$raw_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// 4. Group Data for Display
$survey_structure = [];
foreach ($raw_data as $row) {
    $d_id = $row['domain_ID'];
    $c_id = $row['criteria_ID'];
    $e_id = $row['element_ID'];
    
    if (!isset($survey_structure[$d_id])) {
        $survey_structure[$d_id] = [
            'name' => $row['domain_name'],
            'criteria' => []
        ];
    }
    
    if (!isset($survey_structure[$d_id]['criteria'][$c_id])) {
        $survey_structure[$d_id]['criteria'][$c_id] = [
            'name' => $row['criteria_name'],
            'elements' => []
        ];
    }
    
    if (!isset($survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id])) {
        $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id] = [
            'name' => $row['element_name'],
            'scores' => []
        ];
    }
    
    if ($row['score_level']) {
        $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id]['scores'][$row['score_level']] = [
            'se_id' => $row['se_ID'],
            'details' => $row['details'],
            'desc_level' => $row['desc_level']
        ];
    }
}

// --- PAGING LOGIC ---
$domain_ids = array_keys($survey_structure);
$total_domains = count($domain_ids);

// Get current page from URL, default to 1
$survey_page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
if ($total_domains == 0) {
    $survey_page = 1;
} else {
    if ($survey_page < 1) $survey_page = 1;
    if ($survey_page > $total_domains) $survey_page = $total_domains;
}

// Find the domain ID and data for the current page
$current_domain_id = null;
$current_domain_data = null;
if ($total_domains > 0 && isset($domain_ids[$survey_page - 1])) {
    $current_domain_id = $domain_ids[$survey_page - 1];
    $current_domain_data = $survey_structure[$domain_ids[$survey_page - 1]];
}

// --- Variables for View ---
$currentPage = basename(__FILE__);
$currentDir = basename(__DIR__);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?php echo htmlspecialchars($survey['survey_name']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Sidebar and layout styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; }
        .main-content-wrapper {
            margin-left: 16.6667%; height: 100vh; 
            overflow-y: auto; padding: 0;
            background-color: #f8f9fa; 
        }
        .main-content { padding: 1.5rem; }
        .btn-primary {
            background-color: #667eea !important; border-color: #667eea !important;
        }
        .btn-primary:hover {
            background-color: #764ba2 !important; border-color: #764ba2 !important;
        }
        @media (max-width: 991.98px) {
            html, body { overflow: auto; }
            .sidebar { position: relative; width: 100%; height: auto; overflow-y: visible; z-index: 1; }
            .main-content-wrapper { margin-left: 0; height: auto; overflow-y: visible; }
        }

        /* Page-specific styles for survey form */
        .score-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.2s;
            cursor: default; /* Changed cursor to default since it's disabled */
            background-color: #fff;
        }
        /* Adjusted checked state for disabled view */
        .score-input:checked + .score-option {
            background-color: #e7f1ff;
            border-color: #0d6efd;
            box-shadow: none; /* Removed glow since it's not interactive */
        }
        .score-badge {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e9ecef;
            color: #495057;
            font-weight: bold;
            margin-right: 10px;
        }
        .score-input:checked + .score-option .score-badge {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-md-2 col-lg-2 sidebar">
                <?php 
                include_once __DIR__ . '/../../includes/admin_sidebar.php'; 
                ?>
            </div>
            
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content">

                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Survey Management</a></li>
                            
                            <li class="breadcrumb-item active text-dark" aria-current="page" 
                                title="Survey: <?php echo htmlspecialchars($survey['survey_name']); ?>"> Survey: <?php echo htmlspecialchars(truncate($survey['survey_name'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>
                                <i class="bi bi-eye-fill me-2 text-primary"></i>
                                Preview: <?php echo htmlspecialchars($survey['survey_name']); ?>
                            </h2>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary-emphasis mb-2"><?php echo htmlspecialchars($survey['department']); ?></span>
                                    <h1 class="h3 mb-1"><?php echo htmlspecialchars($survey['survey_name']); ?></h1>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($survey['survey_description']); ?></p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo ($survey['status'] === 'Active') ? 'success' : 'warning'; ?>-subtle text-<?php echo ($survey['status'] === 'Active') ? 'success' : 'warning'; ?>-emphasis rounded-pill">
                                        <?php echo htmlspecialchars($survey['status']); ?>
                                    </span>
                                    <div class="small text-muted mt-2">
                                        Due: <?php echo date('d M Y H:i:s', strtotime($survey['end_date'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="survey-container">
                        
                        <?php if (empty($survey_structure) || $total_domains == 0): ?>
                            <div class="card shadow-sm">
                                <div class="card-body text-center p-5">
                                    <div class="alert alert-warning d-inline-block">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No domains or criteria found for this survey. Please <a href="edit.php?id=<?php echo htmlspecialchars($survey_id); ?>">configure the survey</a> first.
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>

                            <div class="card shadow-sm mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-primary">
                                            Domain <?php echo $survey_page; ?> of <?php echo $total_domains; ?>
                                        </h5>
                                        <div class="w-50">
                                            <div class="progress" style="height: 20px;">
                                                <?php 
                                                $progress_percent = 0;
                                                if ($total_domains > 0) {
                                                    $progress_percent = max(0, ((int)$survey_page / (int)$total_domains) * 100);
                                                }
                                                ?>
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress_percent; ?>%;" 
                                                     aria-valuenow="<?php echo $survey_page; ?>" aria-valuemin="1" aria-valuemax="<?php echo $total_domains; ?>">
                                                    <?php echo round($progress_percent); ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h3 class="border-bottom pb-2 mb-3 text-dark">
                                    <i class="bi bi-layers me-2"></i>Domain - <?php echo htmlspecialchars($current_domain_data['name']); ?>
                                </h3>

                                <?php foreach ($current_domain_data['criteria'] as $criteria_id => $criteria): ?>
                                    <div class="card shadow-sm mb-4 border-0">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                            <h4 class="h5 text-dark mb-0">
                                                <i class="bi bi-bookmark-check me-2 text-muted"></i>Criteria -
                                                <?php echo htmlspecialchars($criteria['name']); ?>
                                            </h4>
                                        </div>
                                        <div class="card-body p-4">
                                            
                                            <?php foreach ($criteria['elements'] as $element_id => $element): ?>
                                                <div class="mb-4 p-3 rounded bg-light border">
                                                    <h5 class="mb-3">Element - <?php echo htmlspecialchars($element['name']); ?></h5>
                                                    
                                                    <div class="score-selection">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <?php 
                                                                $score_data = $element['scores'][$i] ?? null;
                                                                $detail_text = $score_data['details'] ?? 'No description available';
                                                                $desc_level = $score_data['desc_level'] ?? '';
                                                            ?>
                                                            
                                                            <div class="d-block">
                                                                <input type="radio" 
                                                                    class="btn-check score-input" 
                                                                    name="responses[<?php echo $element_id; ?>]" 
                                                                    id="e_<?php echo $element_id; ?>_s_<?php echo $i; ?>" 
                                                                    value="<?php echo $i; ?>" 
                                                                    disabled>
                                                                
                                                                <label class="score-option d-flex align-items-center w-100" 
                                                                    for="e_<?php echo $element_id; ?>_s_<?php echo $i; ?>">
                                                                    <span class="score-badge flex-shrink-0"><?php echo $i; ?></span>
                                                                    <div class="flex-grow-1">
                                                                        <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">
                                                                            <?php echo htmlspecialchars($desc_level); ?>
                                                                        </small>
                                                                        <div class="mb-0">
                                                                            <?php echo htmlspecialchars($detail_text); ?>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="card mb-5 shadow-sm">
                                <div class="card-body d-flex justify-content-between gap-2 p-4">
                                    
                                    <div>
                                        <?php if ($survey_page > 1): ?>
                                            <a href="?id=<?php echo $survey_id; ?>&page=<?php echo $survey_page - 1; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-arrow-left me-2"></i>Previous Domain
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary" disabled>
                                                <i class="bi bi-arrow-left me-2"></i>Previous Domain
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <a href="index.php" class="btn btn-secondary">Close Preview</a>

                                        <?php if ($survey_page < $total_domains): ?>
                                            <a href="?id=<?php echo $survey_id; ?>&page=<?php echo $survey_page + 1; ?>" class="btn btn-primary px-4">
                                                Next Domain <i class="bi bi-arrow-right ms-2"></i>
                                            </a>
                                        
                                        <?php else: ?>
                                            <a href="index.php" class="btn btn-success px-4">
                                                <i class="bi bi-check-circle me-2"></i>Finish Preview
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>