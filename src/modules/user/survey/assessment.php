<?php
/**
 * User Assessment Page
 * UPDATED: Fixed 'Invalid parameter number' error by handling NULL dept_ID dynamically.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$current_user_id = getCurrentUserId();

// --- 1. Validation & Setup ---
$assignment_id = $_GET['assignment_id'] ?? null;
$survey_id = $_GET['survey_id'] ?? null;
$dept_id = $_GET['dept_id'] ?? null;

if (!$assignment_id && !$survey_id) {
    setFlashMessage('danger', 'Invalid assessment link.');
    redirect('index.php');
}

// --- 2. Fetch Exact Assignment ---
if ($assignment_id) {
    $sql = "
        SELECT us.*, s.survey_name, s.department, s.end_date, s.status as survey_status, d.dept_name
        FROM user_survey us
        JOIN survey s ON us.survey_ID = s.survey_ID
        LEFT JOIN department d ON us.dept_ID = d.dept_ID
        WHERE us.user_survey_ID = :usid AND us.user_ID = :uid
    ";
    $params = [':usid' => $assignment_id, ':uid' => $current_user_id];
} else {
    $sql = "
        SELECT us.*, s.survey_name, s.department, s.end_date, s.status as survey_status, d.dept_name
        FROM user_survey us
        JOIN survey s ON us.survey_ID = s.survey_ID
        LEFT JOIN department d ON us.dept_ID = d.dept_ID
        WHERE us.survey_ID = :sid AND us.user_ID = :uid
    ";
    $params = [':sid' => $survey_id, ':uid' => $current_user_id];
    if ($dept_id) {
        $sql .= " AND us.dept_ID = :did";
        $params[':did'] = $dept_id;
    } else {
        $sql .= " LIMIT 1";
    }
}

$assignment = $db->fetchOne($sql, $params);

if (!$assignment) {
    setFlashMessage('danger', 'Access denied. Assessment not found.');
    redirect('index.php');
}

$survey_id = $assignment['survey_ID'];
$dept_id = $assignment['dept_ID']; 
$assignment_id = $assignment['user_survey_ID'];

if ($assignment['survey_status'] !== 'Active') {
    setFlashMessage('warning', 'This survey is no longer active.');
    redirect('index.php');
}

// --- 3. Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $redirectUrl = "assessment.php?assignment_id=$assignment_id";

        // === RESTART LOGIC ===
        if (isset($_POST['restart'])) {
            $db->beginTransaction();
            
            // DYNAMIC SQL: Avoid reusing :did parameter to prevent HY093 error
            $delSql = "DELETE FROM response WHERE survey_ID = :sid AND user_ID = :uid";
            $delParams = [':sid' => $survey_id, ':uid' => $current_user_id];

            if ($dept_id) {
                $delSql .= " AND dept_ID = :did";
                $delParams[':did'] = $dept_id;
            } else {
                $delSql .= " AND dept_ID IS NULL";
            }

            $db->query($delSql, $delParams);
            
            // Remove updated_at from user_survey update
            $db->query("UPDATE user_survey SET status = 'In progress' WHERE user_survey_ID = :usid", [':usid' => $assignment_id]);
            $db->commit();
            setFlashMessage('success', 'Assessment restarted.');
            redirect("$redirectUrl&page=1");
        }

        // === SAVE LOGIC ===
        $responses = $_POST['responses'] ?? [];
        $current_page = $_POST['page'] ?? 1;
        $is_finish = isset($_POST['finish']);
        $is_save_draft = isset($_POST['save_draft']);
        
        $db->beginTransaction();

        // DYNAMIC SQL: Build Check Query
        $base_check_sql = "SELECT response_ID FROM response WHERE element_ID = :eid AND user_ID = :uid AND survey_ID = :sid";
        if ($dept_id) {
            $base_check_sql .= " AND dept_ID = :did";
        } else {
            $base_check_sql .= " AND dept_ID IS NULL";
        }
        
        // Insert/Update Queries (kept updated_at for Response table)
        $insert_sql = "INSERT INTO response (element_ID, survey_ID, dept_ID, se_ID, user_ID, score, input_at, updated_at) 
                       VALUES (:eid, :sid, :did, :seid, :uid, :score, NOW(), NOW())";
        
        $update_sql = "UPDATE response SET se_ID = :seid, score = :score, updated_at = NOW() WHERE response_ID = :rid";
        
        $find_se_sql = "SELECT se_ID FROM score_element se JOIN score s ON se.score_ID = s.score_ID WHERE se.element_ID = :eid AND s.score_level = :lvl LIMIT 1";

        foreach ($responses as $element_id => $score_level) {
            $se_row = $db->fetchOne($find_se_sql, [':eid' => $element_id, ':lvl' => $score_level]);
            $se_id = $se_row ? $se_row['se_ID'] : null;

            // Prepare params for Check
            $check_params = [':eid' => $element_id, ':uid' => $current_user_id, ':sid' => $survey_id];
            if ($dept_id) {
                $check_params[':did'] = $dept_id;
            }

            $existing = $db->fetchOne($base_check_sql, $check_params);

            if ($existing) {
                $db->query($update_sql, [':seid' => $se_id, ':score' => $score_level, ':rid' => $existing['response_ID']]);
            } else {
                $db->query($insert_sql, [':eid' => $element_id, ':sid' => $survey_id, ':did' => $dept_id, ':seid' => $se_id, ':uid' => $current_user_id, ':score' => $score_level]);
            }
        }

        if ($is_finish) {
            $db->query("UPDATE user_survey SET status = 'Completed' WHERE user_survey_ID = :usid", [':usid' => $assignment_id]);
            $db->commit();
            setFlashMessage('success', 'Assessment submitted successfully.');
            redirect('index.php'); 
        } else {
            if ($assignment['status'] === 'Pending') {
                $db->query("UPDATE user_survey SET status = 'In progress' WHERE user_survey_ID = :usid", [':usid' => $assignment_id]);
            }
            $db->commit();
            if($is_save_draft) {
                setFlashMessage('success', 'Progress saved.');
                redirect("$redirectUrl&page=$current_page");
            } else {
                $next_page = $current_page + 1;
                redirect("$redirectUrl&page=$next_page");
            }
        }
    } catch (Exception $e) {
        if ($db->getConnection()->inTransaction()) $db->rollBack();
        setFlashMessage('danger', 'Error saving responses: ' . $e->getMessage());
    }
}

// --- 4. Data Fetching ---
$sql_structure = "
    SELECT d.domain_ID, d.domain_name, c.criteria_ID, c.criteria_name, e.element_ID, e.element_name, s.score_level, s.desc_level, se.se_ID, se.details
    FROM survey_domain sd
    JOIN domain d ON sd.domain_ID = d.domain_ID
    JOIN criteria c ON d.domain_ID = c.domain_ID
    JOIN element e ON c.criteria_ID = e.criteria_ID
    LEFT JOIN score_element se ON e.element_ID = se.element_ID
    LEFT JOIN score s ON se.score_ID = s.score_ID
    WHERE sd.survey_ID = :sid AND c.status = 'Active' AND e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID, s.score_level ASC
";
$raw_data = $db->fetchAll($sql_structure, [':sid' => $survey_id]);

$survey_structure = [];
foreach ($raw_data as $row) {
    $d_id = $row['domain_ID']; $c_id = $row['criteria_ID']; $e_id = $row['element_ID'];
    if (!isset($survey_structure[$d_id])) $survey_structure[$d_id] = ['name' => $row['domain_name'], 'criteria' => []];
    if (!isset($survey_structure[$d_id]['criteria'][$c_id])) $survey_structure[$d_id]['criteria'][$c_id] = ['name' => $row['criteria_name'], 'elements' => []];
    if (!isset($survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id])) $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id] = ['name' => $row['element_name'], 'scores' => []];
    if ($row['score_level']) $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id]['scores'][$row['score_level']] = ['details' => $row['details'], 'desc_level' => $row['desc_level']];
}

// Fetch Responses Contextually (Dynamic SQL here too)
$ans_sql = "SELECT element_ID, score FROM response WHERE user_ID = :uid AND survey_ID = :sid";
$ans_params = [':uid' => $current_user_id, ':sid' => $survey_id];
if ($dept_id) {
    $ans_sql .= " AND dept_ID = :did";
    $ans_params[':did'] = $dept_id;
} else {
    $ans_sql .= " AND dept_ID IS NULL";
}

$current_answers_raw = $db->fetchAll($ans_sql, $ans_params);
$user_responses = [];
foreach ($current_answers_raw as $r) $user_responses[$r['element_ID']] = $r['score'];

$domain_ids = array_keys($survey_structure);
$total_domains = count($domain_ids);
$current_page_num = max(1, min($total_domains, (int)($_GET['page'] ?? 1)));
$current_domain_data = ($total_domains > 0) ? $survey_structure[$domain_ids[$current_page_num - 1]] : null;
$flash = getFlashMessage();
$progress_percent = ($total_domains > 0) ? round((($current_page_num - 1) / $total_domains) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessment - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Preserved your CSS exactly */
        html, body { height: 100%; background-color: #f8f9fa; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); padding-bottom: 80px; }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05); }
        .card-header-clean { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.5rem; }
        .sticky-progress-bar { position: sticky; top: 0; z-index: 1020; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .assessment-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .assessment-option { 
            flex: 1; min-width: 180px; position: relative; border: 2px solid #e9ecef; border-radius: 12px; padding: 20px; 
            cursor: pointer; background: #fff; transition: all 0.2s ease; text-align: left; height: 100%; display: flex; flex-direction: column;
        }
        .assessment-option:hover { border-color: #dee2e6; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .assessment-input { position: absolute; opacity: 0; cursor: pointer; }
        .assessment-input:checked + .assessment-option { border-color: #667eea; background-color: #f0f4ff; color: #5e72e4; box-shadow: 0 0 0 1px #667eea inset; }
        .score-number { font-size: 1.5rem; font-weight: 800; color: #adb5bd; margin-bottom: 8px; }
        .assessment-input:checked + .assessment-option .score-number { color: #5e72e4; }
        .score-desc { font-weight: 700; font-size: 1rem; margin-bottom: 8px; display: block; }
        .score-detail { font-size: 0.85rem; color: #6c757d; line-height: 1.4; display: block; white-space: pre-wrap; margin-top: 5px; }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { opacity: 0.9; color: white; }
        .domain-title { font-weight: 800; color: #32325d; letter-spacing: -0.5px; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <?php include_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="sticky-progress-bar px-4 py-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div><span class="text-uppercase text-muted fw-bold small">Survey Progress</span></div>
                        <div class="fw-bold text-primary small">Page <?php echo $current_page_num; ?> / <?php echo $total_domains; ?></div>
                    </div>
                    <div class="progress rounded-pill" style="height: 8px;">
                        <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: <?php echo $progress_percent; ?>%"></div>
                    </div>
                </div>

                <div class="main-content px-4 py-4">
                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
                            <?php echo $flash['message']; ?> <button class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$current_domain_data): ?>
                        <div class="text-center p-5 card shadow-sm">
                            <h3 class="text-muted">No questions found.</h3>
                            <a href="index.php" class="btn btn-primary mt-3 px-4 rounded-pill">Back to Dashboard</a>
                        </div>
                    <?php else: ?>

                        <form method="POST" id="assessmentForm" action="?survey_id=<?php echo $survey_id; ?>&page=<?php echo $current_page_num; ?><?php echo $dept_id ? '&dept_id='.$dept_id : ''; ?>">
                            <input type="hidden" name="page" value="<?php echo $current_page_num; ?>">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold mb-1"><?php echo htmlspecialchars($assignment['survey_name']); ?></h6>
                                    
                                    <?php if(!empty($assignment['dept_name'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">
                                            <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($assignment['dept_name']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <h2 class="domain-title mb-0"><?php echo htmlspecialchars($current_domain_data['name']); ?></h2>
                                </div>
                                <?php if (!empty($user_responses)): ?>
                                    <button type="submit" name="restart" value="1" onclick="return confirm('Reset all answers for THIS survey?');" class="btn btn-light text-danger border-0 btn-sm rounded-pill px-3">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Answers
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php foreach ($current_domain_data['criteria'] as $crit): ?>
                                <div class="card mb-4 rounded-4">
                                    <div class="card-header-clean">
                                        <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($crit['name']); ?></h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <?php 
                                        $elem_count = 0;
                                        foreach ($crit['elements'] as $elem_id => $elem): 
                                            $elem_count++;
                                            $is_last = $elem_count === count($crit['elements']);
                                        ?>
                                            <div class="mb-4" id="el_<?php echo $elem_id; ?>">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-light text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; min-width: 32px;">
                                                        <?php echo $elem_count; ?>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($elem['name']); ?></h6>
                                                </div>
                                                
                                                <div class="assessment-group ms-md-5">
                                                    <?php $saved_score = $user_responses[$elem_id] ?? null; ?>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php 
                                                            $s = $elem['scores'][$i] ?? null;
                                                            $desc = $s['desc_level'] ?? "Level $i";
                                                            $detail = $s['details'] ?? 'No description.';
                                                        ?>
                                                        <input type="radio" class="btn-check assessment-input" 
                                                               name="responses[<?php echo $elem_id; ?>]" 
                                                               id="r_<?php echo $elem_id; ?>_<?php echo $i; ?>" 
                                                               value="<?php echo $i; ?>" 
                                                               <?php echo ($saved_score == $i) ? 'checked' : ''; ?> required>
                                                        
                                                        <label class="assessment-option" for="r_<?php echo $elem_id; ?>_<?php echo $i; ?>">
                                                            <div class="d-flex justify-content-between">
                                                                <span class="score-number"><?php echo $i; ?></span>
                                                                <?php if($saved_score == $i): ?><i class="bi bi-check-circle-fill text-primary"></i><?php endif; ?>
                                                            </div>
                                                            <span class="score-desc"><?php echo htmlspecialchars($desc); ?></span>
                                                            <span class="score-detail"><?php echo htmlspecialchars($detail); ?></span>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                                <?php if(!$is_last): ?><hr class="my-4 text-muted opacity-25 ms-md-5"><?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="fixed-bottom bg-white border-top shadow py-3 px-4" style="margin-left: 270px; z-index: 1030;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($current_page_num > 1): ?>
                                            <a href="?survey_id=<?php echo $survey_id; ?>&page=<?php echo $current_page_num - 1; ?><?php echo $dept_id ? '&dept_id='.$dept_id : ''; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                                                <i class="bi bi-arrow-left me-1"></i> Previous
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="save_draft" value="1" class="btn btn-light text-primary fw-bold border-0 rounded-pill px-3">
                                            <i class="bi bi-save me-1"></i> Save Draft
                                        </button>
                                        
                                        <?php if ($current_page_num < $total_domains): ?>
                                            <button type="submit" class="btn btn-gradient-primary rounded-pill px-4 shadow-sm">
                                                Next Domain <i class="bi bi-arrow-right ms-1"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="finish" value="1" class="btn btn-success rounded-pill px-5 shadow-sm fw-bold" onclick="return confirm('Are you ready to submit your assessment?');">
                                                Submit Assessment <i class="bi bi-check-lg ms-1"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                                if (window.innerWidth < 992) {
                                    document.querySelector('.fixed-bottom').style.marginLeft = '0';
                                }
                                window.addEventListener('resize', function() {
                                    if (window.innerWidth < 992) {
                                        document.querySelector('.fixed-bottom').style.marginLeft = '0';
                                    } else {
                                        document.querySelector('.fixed-bottom').style.marginLeft = '270px';
                                    }
                                });
                            </script>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let isDirty = false;
        document.getElementById('assessmentForm')?.addEventListener('change', () => isDirty = true);
        document.getElementById('assessmentForm')?.addEventListener('submit', () => isDirty = false);
        window.addEventListener('beforeunload', (e) => isDirty ? (e.returnValue = '') : undefined);
    </script>
</body>
</html>