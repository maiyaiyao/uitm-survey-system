<?php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);
$db = new Database();

// Fetch Data Hierarchy with Average Scores from ALL responses
$sql = "
    SELECT 
        d.domain_ID, d.domain_name,
        c.criteria_ID, c.criteria_name,
        e.element_ID, e.element_name,
        AVG(r.score) as avg_raw,
        COUNT(r.score) as response_count
    FROM element e
    JOIN criteria c ON e.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    LEFT JOIN response r ON e.element_ID = r.element_ID
    WHERE e.status = 'Active'
    GROUP BY d.domain_ID, c.criteria_ID, e.element_ID
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID
";
$rows = $db->fetchAll($sql);

$report = [];
foreach ($rows as $r) {
    $d_id = $r['domain_ID'];
    $c_id = $r['criteria_ID'];
    
    if (!isset($report[$d_id])) {
        $report[$d_id] = ['name' => $r['domain_name'], 'criteria' => []];
    }
    if (!isset($report[$d_id]['criteria'][$c_id])) {
        $report[$d_id]['criteria'][$c_id] = ['name' => $r['criteria_name'], 'elements' => []];
    }
    
    // Convert 1-5 score to 0-100%
    $percent = ($r['avg_raw'] > 0) ? round($r['avg_raw'] * 20) : 0;
    
    $report[$d_id]['criteria'][$c_id]['elements'][] = [
        'name' => $r['element_name'],
        'percent' => $percent,
        'count' => $r['response_count']
    ];
}

function getCls($p) { return ($p >= 80) ? 'success' : (($p >= 50) ? 'primary' : 'danger'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Domain Detail Analysis - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h3 class="mb-0">Detailed Domain Analysis</h3>
                <small class="text-muted">Aggregated data across all surveys (All Time)</small>
            </div>
            <a href="index.php" class="btn btn-outline-secondary align-self-center">Back</a>
        </div>

        <?php foreach ($report as $d_id => $domain): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark border-start border-4 border-primary ps-3">
                        <?php echo htmlspecialchars($domain['name']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($domain['criteria'] as $crit): ?>
                        <div class="mb-4 ps-3">
                            <h6 class="fw-bold text-secondary text-uppercase small mb-3">
                                <?php echo htmlspecialchars($crit['name']); ?>
                            </h6>
                            
                            <div class="row g-3">
                                <?php foreach ($crit['elements'] as $el): ?>
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-dark fw-medium"><?php echo htmlspecialchars($el['name']); ?></span>
                                                <div class="text-end">
                                                    <span class="h5 fw-bold mb-0 text-<?php echo getCls($el['percent']); ?>">
                                                        <?php echo $el['percent']; ?>%
                                                    </span>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        <?php echo $el['count']; ?> Responses
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-<?php echo getCls($el['percent']); ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $el['percent']; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>