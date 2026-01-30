<?php

require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$org_id = $_GET['org_id'] ?? null;

if (!$org_id) {
    setFlashMessage('error', "Invalid Organization ID.");
    header('Location: index.php');
    exit();
}

// 1. Fetch Org Details
$orgInfo = $db->fetchOne("SELECT org_name FROM organization WHERE org_ID = :id", [':id' => $org_id]);

// 2. Fetch Available Surveys for THIS Organization
$surveys = $db->fetchAll("
    SELECT survey_ID, survey_name, start_date, end_date, status 
    FROM survey 
    WHERE org_ID = :oid AND status != 'Draft'
    ORDER BY created_at DESC
", [':oid' => $org_id]);

// 3. Determine Selected Survey (Default to latest)
$selected_survey_id = $_GET['survey_id'] ?? ($surveys[0]['survey_ID'] ?? null);

// 4. Fetch Scores (Filtered by Survey ID)
$domainScores = [];
$deptScores = [];
$overallScore = 0;

if ($selected_survey_id) {
    // A. Domain Scores
    $domainScores = $db->fetchAll("
        SELECT 
            dm.domain_name,
            AVG(r.score) * 20 as domain_score_pct
        FROM response r
        JOIN element e ON r.element_ID = e.element_ID
        JOIN criteria c ON e.criteria_ID = c.criteria_ID
        JOIN domain dm ON c.domain_ID = dm.domain_ID
        WHERE r.survey_ID = :sid
        GROUP BY dm.domain_ID
        ORDER BY domain_score_pct DESC
    ", [':sid' => $selected_survey_id]);

    // B. Department Breakdown
    $deptScores = $db->fetchAll("
        SELECT 
            d.dept_name,
            COUNT(DISTINCT r.user_ID) as user_count,
            AVG(r.score) * 20 as dept_score_pct
        FROM department d
        JOIN response r ON d.dept_ID = r.dept_ID
        WHERE r.survey_ID = :sid AND d.org_ID = :oid
        GROUP BY d.dept_ID
        ORDER BY dept_score_pct DESC
    ", [':sid' => $selected_survey_id, ':oid' => $org_id]);

    // C. Calculate Overall Score (Average of the Domain Percentages)
    if (!empty($domainScores)) {
        $sum = array_sum(array_column($domainScores, 'domain_score_pct'));
        $overallScore = $sum / count($domainScores);
    }
}

function getColor($pct) {
    if ($pct >= 80) return 'success';
    if ($pct >= 60) return 'primary';
    if ($pct >= 40) return 'warning';
    return 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($orgInfo['org_name']); ?> Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; padding: 2rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        .score-big { font-size: 3rem; font-weight: 800; }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <div class="main-content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="survey_summary.php" class="btn btn-outline-secondary rounded-circle"><i class="bi bi-arrow-left"></i></a>
                <div>
                    <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($orgInfo['org_name']); ?></h3>
                    <p class="text-muted mb-0">Performance Report</p>
                </div>
            </div>
            
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="org_id" value="<?php echo $org_id; ?>">
                <label class="me-2 fw-bold text-secondary">Survey:</label>
                <select name="survey_id" class="form-select form-select-sm" style="width: 250px;" onchange="this.form.submit()">
                    <?php if(empty($surveys)): ?>
                        <option>No surveys found</option>
                    <?php else: ?>
                        <?php foreach($surveys as $s): ?>
                            <option value="<?php echo $s['survey_ID']; ?>" <?php echo $s['survey_ID'] == $selected_survey_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['survey_name']); ?> (<?php echo date('Y', strtotime($s['start_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_survey_id): ?>
            
            <div class="card mb-4 p-4 text-center">
                <h6 class="text-uppercase text-muted fw-bold">Overall Maturity Score</h6>
                <div class="score-big text-<?php echo getColor($overallScore); ?>">
                    <?php echo number_format($overallScore, 2); ?>%
                </div>
                <div class="text-muted small">Based on average of domain scores</div>
            </div>

            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card h-100 p-3">
                        <h5 class="fw-bold mb-3">Domain Performance</h5>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach($domainScores as $ds): 
                                $pct = $ds['domain_score_pct']; 
                            ?>
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><?php echo htmlspecialchars($ds['domain_name']); ?></span>
                                        <span class="fw-bold text-<?php echo getColor($pct); ?>"><?php echo number_format($pct, 1); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-<?php echo getColor($pct); ?>" style="width: <?php echo $pct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card h-100 p-0">
                        <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">Department Breakdown</h5></div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Department</th>
                                        <th class="text-center">Respondents</th>
                                        <th class="text-end pe-4">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($deptScores as $dt): 
                                        $pct = $dt['dept_score_pct'];
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold"><?php echo htmlspecialchars($dt['dept_name']); ?></td>
                                            <td class="text-center"><?php echo $dt['user_count']; ?></td>
                                            <td class="text-end pe-4 fw-bold text-<?php echo getColor($pct); ?>">
                                                <?php echo number_format($pct, 1); ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info">No survey data available for this organization.</div>
            <?php endif; ?>
    </div>
</body>
</html>