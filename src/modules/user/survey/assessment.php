<?php
/**
 * User Assessment Page
 * Allows users to answer survey questions domain by domain.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$current_user_id = getCurrentUserId();

// --- 1. Validation & Setup ---
$survey_id = $_GET['survey_id'] ?? null;

if (!$survey_id) {
    setFlashMessage('danger', 'Invalid survey ID.');
    redirect('index.php');
}

// Check if survey is assigned to this user
$assignment = $db->fetchOne("
    SELECT us.*, s.survey_name, s.department, s.end_date, s.status as survey_status
    FROM user_survey us
    JOIN survey s ON us.survey_ID = s.survey_ID
    WHERE us.survey_ID = :sid AND us.user_ID = :uid
", [':sid' => $survey_id, ':uid' => $current_user_id]);

if (!$assignment) {
    setFlashMessage('danger', 'Access denied. This survey is not assigned to you.');
    redirect('index.php');
}

if ($assignment['survey_status'] !== 'Active') {
    setFlashMessage('warning', 'This survey is no longer active.');
    redirect('index.php');
}

// --- 2. Handle Form Submission (Save, Finish, Restart, or Save Draft) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // === RESTART LOGIC ===
        if (isset($_POST['restart'])) {
            $db->beginTransaction();

            $sql_elements = "
                SELECT e.element_ID 
                FROM element e
                JOIN criteria c ON e.criteria_ID = c.criteria_ID
                JOIN domain d ON c.domain_ID = d.domain_ID
                JOIN survey_domain sd ON d.domain_ID = sd.domain_ID
                WHERE sd.survey_ID = :sid
            ";
            $elements = $db->fetchAll($sql_elements, [':sid' => $survey_id]);
            
            if (!empty($elements)) {
                $element_ids = array_column($elements, 'element_ID');
                $placeholders = implode(',', array_fill(0, count($element_ids), '?'));
                $delete_params = array_merge($element_ids, [$current_user_id]);
                
                $sql_delete = "DELETE FROM response WHERE element_ID IN ($placeholders) AND user_ID = ?";
                $db->query($sql_delete, $delete_params);
            }

            $db->query("UPDATE user_survey SET status = 'in progress' WHERE user_survey_ID = :usid", 
                [':usid' => $assignment['user_survey_ID']]);

            $db->commit();
            setFlashMessage('success', 'Survey progress has been reset.');
            redirect("assessment.php?survey_id=$survey_id&page=1");
            exit;
        }

        // === SAVE / NEXT / FINISH LOGIC ===
        $responses = $_POST['responses'] ?? [];
        $current_page = $_POST['page'] ?? 1;
        $is_finish = isset($_POST['finish']);
        $is_save_draft = isset($_POST['save_draft']);
        
        $db->beginTransaction();

        // SQL Statements
        $check_sql = "SELECT response_ID FROM response WHERE element_ID = :eid AND user_ID = :uid";
        $insert_sql = "INSERT INTO response (response_ID, element_ID, se_ID, user_ID, score, input_at) 
                       VALUES (:rid, :eid, :seid, :uid, :score, NOW())";
        $update_sql = "UPDATE response SET se_ID = :seid, score = :score, updated_at = NOW() 
                       WHERE response_ID = :rid";
        $find_se_sql = "SELECT se.se_ID 
                        FROM score_element se 
                        JOIN score s ON se.score_ID = s.score_ID 
                        WHERE se.element_ID = :eid AND s.score_level = :lvl LIMIT 1";

        // Save Answers loop
        foreach ($responses as $element_id => $score_level) {
            $se_row = $db->fetchOne($find_se_sql, [':eid' => $element_id, ':lvl' => $score_level]);
            $se_id = $se_row ? $se_row['se_ID'] : null;

            $existing = $db->fetchOne($check_sql, [':eid' => $element_id, ':uid' => $current_user_id]);

            if ($existing) {
                $db->query($update_sql, [
                    ':seid' => $se_id,
                    ':score' => $score_level,
                    ':rid' => $existing['response_ID']
                ]);
            } else {
                $db->query($insert_sql, [
                    ':rid' => 'NEW', 
                    ':eid' => $element_id,
                    ':seid' => $se_id,
                    ':uid' => $current_user_id,
                    ':score' => $score_level
                ]);
            }
        }

        // Determine Action
        if ($is_finish) {
            // 1. Submit Final
            $db->query("UPDATE user_survey SET status = 'completed' WHERE user_survey_ID = :usid", 
                [':usid' => $assignment['user_survey_ID']]);
            $db->commit();
            setFlashMessage('success', 'Assessment submitted successfully.');
            redirect('index.php'); 

        } elseif ($is_save_draft) {
            // 2. Save Draft (Stay on page)
            if ($assignment['status'] === 'Pending') {
                $db->query("UPDATE user_survey SET status = 'in progress' WHERE user_survey_ID = :usid", 
                    [':usid' => $assignment['user_survey_ID']]);
            }
            $db->commit();
            setFlashMessage('success', 'Progress saved successfully.');
            redirect("assessment.php?survey_id=$survey_id&page=$current_page");

        } else {
            // 3. Next Domain (Default behavior)
            if ($assignment['status'] === 'Pending') {
                $db->query("UPDATE user_survey SET status = 'in progress' WHERE user_survey_ID = :usid", 
                    [':usid' => $assignment['user_survey_ID']]);
            }
            $db->commit();
            $next_page = $current_page + 1;
            redirect("assessment.php?survey_id=$survey_id&page=$next_page");
        }

    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('danger', 'Error saving responses: ' . $e->getMessage());
    }
}

// --- 3. Data Fetching for View ---

$sql_structure = "
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
    WHERE sd.survey_ID = :sid 
    AND c.status = 'Active' 
    AND e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID, s.score_level ASC
";

$raw_data = $db->fetchAll($sql_structure, [':sid' => $survey_id]);

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
            'details' => $row['details'],
            'desc_level' => $row['desc_level']
        ];
    }
}

// Fetch Existing Responses
$sql_responses = "SELECT element_ID, score FROM response WHERE user_ID = :uid";
$user_responses_raw = $db->fetchAll($sql_responses, [':uid' => $current_user_id]);
$user_responses = [];
foreach ($user_responses_raw as $r) {
    $user_responses[$r['element_ID']] = $r['score'];
}

// Pagination Logic
$domain_ids = array_keys($survey_structure);
$total_domains = count($domain_ids);
$current_page_num = isset($_GET['page']) ? (int)$_GET['page'] : 1; 

if ($total_domains == 0) {
    $current_page_num = 1;
} else {
    if ($current_page_num < 1) $current_page_num = 1;
    if ($current_page_num > $total_domains) $current_page_num = $total_domains;
}

$current_domain_id = null;
$current_domain_data = null;

if ($total_domains > 0 && isset($domain_ids[$current_page_num - 1])) {
    $current_domain_id = $domain_ids[$current_page_num - 1];
    $current_domain_data = $survey_structure[$current_domain_id];
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .score-option { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 10px; transition: all 0.2s; cursor: pointer; background-color: #fff; }
        .score-option:hover { background-color: #f8f9fa; border-color: #adb5bd; }
        .score-input:checked + .score-option { background-color: #e7f1ff; border-color: #0d6efd; box-shadow: 0 0 0 1px #0d6efd inset; }
        .score-badge { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #e9ecef; color: #495057; font-weight: bold; margin-right: 12px; }
        .score-input:checked + .score-option .score-badge { background: #0d6efd; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <?php include_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">My Surveys</a></li>
                                <li class="breadcrumb-item active text-dark" aria-current="page">Assessment</li>
                            </ol>
                        </nav>
                        
                        <?php if (!empty($user_responses)): ?>
                            <form method="POST" onsubmit="return confirm('WARNING: This will erase ALL your saved answers for this survey and restart from the beginning.\n\nAre you sure you want to restart?');">
                                <input type="hidden" name="restart" value="1">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restart Survey
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$current_domain_data): ?>
                        <div class="card shadow-sm p-5 text-center">
                            <h3>No questions found.</h3>
                            <p class="text-muted">This survey appears to be empty.</p>
                            <a href="index.php" class="btn btn-primary mt-3">Back</a>
                        </div>
                    <?php else: ?>

                        <form method="POST" id="assessmentForm">
                            <input type="hidden" name="page" value="<?php echo $current_page_num; ?>">
                            
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-1 fw-bold"><?php echo htmlspecialchars($assignment['survey_name']); ?></h4>
                                            <p class="text-muted mb-0 small">
                                                Domain <?php echo $current_page_num; ?> of <?php echo $total_domains; ?>: 
                                                <strong class="text-dark"><?php echo htmlspecialchars($current_domain_data['name']); ?></strong>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="progress" style="height: 10px;">
                                                <?php $pct = ($current_page_num / $total_domains) * 100; ?>
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                                            </div>
                                            <div class="text-end mt-1 text-muted small"><?php echo round($pct); ?>% Complete</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <?php foreach ($current_domain_data['criteria'] as $criteria_id => $crit): ?>
                                    
                                    <div class="card shadow-sm mb-4 border-0">
                                        <div class="card-header bg-white py-3 border-bottom">
                                            <h5 class="mb-0 text-primary">
                                                <i class="bi bi-layers me-2"></i> <?php echo htmlspecialchars($crit['name']); ?>
                                            </h5>
                                        </div>
                                        <div class="card-body p-4">
                                            
                                            <?php foreach ($crit['elements'] as $elem_id => $elem): ?>
                                                <div class="mb-5 element-block" id="el_<?php echo $elem_id; ?>">
                                                    <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($elem['name']); ?></h6>
                                                    
                                                    <div class="score-selection">
                                                        <?php 
                                                            $saved_score = $user_responses[$elem_id] ?? null; 
                                                        ?>
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php 
                                                                $s_data = $elem['scores'][$i] ?? null;
                                                                $s_detail = $s_data['details'] ?? 'No description available for this level.';
                                                                $s_desc = $s_data['desc_level'] ?? "Level $i";
                                                            ?>
                                                            <div class="d-block">
                                                                <input type="radio" class="btn-check score-input" 
                                                                    name="responses[<?php echo $elem_id; ?>]" 
                                                                    id="radio_<?php echo $elem_id; ?>_<?php echo $i; ?>" 
                                                                    value="<?php echo $i; ?>"
                                                                    <?php echo ($saved_score == $i) ? 'checked' : ''; ?> 
                                                                    required>
                                                                
                                                                <label class="score-option d-flex align-items-center" for="radio_<?php echo $elem_id; ?>_<?php echo $i; ?>">
                                                                    <span class="score-badge flex-shrink-0"><?php echo $i; ?></span>
                                                                    <div>
                                                                        <div class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">
                                                                            <?php echo htmlspecialchars($s_desc); ?>
                                                                        </div>
                                                                        <div class="mb-0 text-dark small">
                                                                            <?php echo nl2br(htmlspecialchars($s_detail)); ?>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <hr class="text-muted opacity-25 my-4">
                                            <?php endforeach; ?>

                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </div>

                            <div class="card shadow-sm fixed-bottom position-sticky mt-4 border-0" style="bottom: 0; z-index: 99;">
                                <div class="card-body p-3 bg-white border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($current_page_num > 1): ?>
                                                <a href="?survey_id=<?php echo $survey_id; ?>&page=<?php echo $current_page_num - 1; ?>" 
                                                   class="btn btn-outline-secondary px-4">
                                                    <i class="bi bi-arrow-left me-2"></i> Previous
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <?php if ($current_page_num < $total_domains): ?>
                                                <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                    Next Domain <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="save_draft" value="1" class="btn btn-outline-primary px-4 me-2 rounded-3">
                                                    <i class="bi bi-save me-2"></i> Save Progress
                                                </button>
                                                <button type="submit" name="finish" value="1" 
                                                        class="btn btn-success px-5 rounded-3 shadow-sm"
                                                        onclick="return confirm('Are you sure you want to submit your assessment? You cannot edit it afterwards.');">
                                                    <i class="bi bi-check-circle-fill me-2"></i> Submit Assessment
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevent accidental navigation
        let isDirty = false;
        const form = document.getElementById('assessmentForm');
        
        if(form) {
            form.addEventListener('change', () => isDirty = true);
            form.addEventListener('submit', () => isDirty = false);
        }

        window.addEventListener('beforeunload', (e) => {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>