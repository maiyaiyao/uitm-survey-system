<?php
/**
 * User Assessment Page
 * STRICT ISOLATION: Answers are unique to each survey.
 * No data is shared or pre-filled from previous surveys.
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

// --- 2. Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // === RESTART LOGIC ===
        if (isset($_POST['restart'])) {
            $db->beginTransaction();

            // Only delete responses for THIS survey_ID
            $db->query("DELETE FROM response WHERE survey_ID = :sid AND user_ID = :uid", 
                [':sid' => $survey_id, ':uid' => $current_user_id]);

            $db->query("UPDATE user_survey SET status = 'In progress' WHERE user_survey_ID = :usid", 
                [':usid' => $assignment['user_survey_ID']]);

            $db->commit();
            setFlashMessage('success', 'Survey has been reset.');
            redirect("assessment.php?survey_id=$survey_id&page=1");
            exit;
        }

        // === SAVE LOGIC ===
        $responses = $_POST['responses'] ?? [];
        $current_page = $_POST['page'] ?? 1;
        $is_finish = isset($_POST['finish']);
        $is_save_draft = isset($_POST['save_draft']);
        
        $db->beginTransaction();

        // 1. Check for EXISTING response STRICTLY for THIS survey
        $check_sql = "SELECT response_ID FROM response 
                      WHERE element_ID = :eid AND user_ID = :uid AND survey_ID = :sid";
        
        // 2. Insert NEW response linked to THIS survey
        $insert_sql = "INSERT INTO response (response_ID, element_ID, survey_ID, se_ID, user_ID, score, input_at, updated_at) 
                       VALUES (:rid, :eid, :sid, :seid, :uid, :score, NOW(), NOW())";
        
        // 3. Update EXISTING response
        $update_sql = "UPDATE response SET se_ID = :seid, score = :score, updated_at = NOW() 
                       WHERE response_ID = :rid";
                       
        $find_se_sql = "SELECT se_ID FROM score_element se 
                        JOIN score s ON se.score_ID = s.score_ID 
                        WHERE se.element_ID = :eid AND s.score_level = :lvl LIMIT 1";

        foreach ($responses as $element_id => $score_level) {
            $se_row = $db->fetchOne($find_se_sql, [':eid' => $element_id, ':lvl' => $score_level]);
            $se_id = $se_row ? $se_row['se_ID'] : null;

            $existing = $db->fetchOne($check_sql, [
                ':eid' => $element_id, 
                ':uid' => $current_user_id,
                ':sid' => $survey_id // <--- Strict Check
            ]);

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
                    ':sid' => $survey_id, // <--- Link to specific survey
                    ':seid' => $se_id,
                    ':uid' => $current_user_id,
                    ':score' => $score_level
                ]);
            }
        }

        // Update Status
        if ($is_finish) {
            $db->query("UPDATE user_survey SET status = 'Completed' WHERE user_survey_ID = :usid", 
                [':usid' => $assignment['user_survey_ID']]);
            $db->commit();
            setFlashMessage('success', 'Assessment submitted successfully.');
            redirect('index.php'); 

        } elseif ($is_save_draft) {
            if ($assignment['status'] === 'Pending') {
                $db->query("UPDATE user_survey SET status = 'In progress' WHERE user_survey_ID = :usid", 
                    [':usid' => $assignment['user_survey_ID']]);
            }
            $db->commit();
            setFlashMessage('success', 'Progress saved.');
            redirect("assessment.php?survey_id=$survey_id&page=$current_page");

        } else {
            if ($assignment['status'] === 'Pending') {
                $db->query("UPDATE user_survey SET status = 'In progress' WHERE user_survey_ID = :usid", 
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

// --- 3. Data Fetching (Strict Isolation) ---

// A. Get Questions Structure
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

// Build Tree Structure
$survey_structure = [];
foreach ($raw_data as $row) {
    $d_id = $row['domain_ID'];
    $c_id = $row['criteria_ID'];
    $e_id = $row['element_ID'];
    
    if (!isset($survey_structure[$d_id])) {
        $survey_structure[$d_id] = ['name' => $row['domain_name'], 'criteria' => []];
    }
    if (!isset($survey_structure[$d_id]['criteria'][$c_id])) {
        $survey_structure[$d_id]['criteria'][$c_id] = ['name' => $row['criteria_name'], 'elements' => []];
    }
    if (!isset($survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id])) {
        $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id] = ['name' => $row['element_name'], 'scores' => []];
    }
    if ($row['score_level']) {
        $survey_structure[$d_id]['criteria'][$c_id]['elements'][$e_id]['scores'][$row['score_level']] = [
            'details' => $row['details'],
            'desc_level' => $row['desc_level']
        ];
    }
}

// B. Fetch Answers (STRICTLY for this Survey ID)
// We removed the logic that looked for previous history.
$current_answers_raw = $db->fetchAll("
    SELECT element_ID, score 
    FROM response 
    WHERE user_ID = :uid AND survey_ID = :sid
", [':uid' => $current_user_id, ':sid' => $survey_id]);

$user_responses = [];
foreach ($current_answers_raw as $r) {
    $user_responses[$r['element_ID']] = $r['score'];
}

// --- Pagination ---
$domain_ids = array_keys($survey_structure);
$total_domains = count($domain_ids);
$current_page_num = max(1, min($total_domains, (int)($_GET['page'] ?? 1)));
$current_domain_data = ($total_domains > 0) ? $survey_structure[$domain_ids[$current_page_num - 1]] : null;
$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessment - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .score-option { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; cursor: pointer; background: #fff; transition: 0.2s; }
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
                                <li class="breadcrumb-item"><a href="index.php">My Surveys</a></li>
                                <li class="breadcrumb-item active">Assessment</li>
                            </ol>
                        </nav>
                        
                        <?php if (!empty($user_responses)): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to reset your answers for THIS survey?');">
                                <input type="hidden" name="restart" value="1">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset Survey
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show"><?php echo $flash['message']; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <?php if (!$current_domain_data): ?>
                        <div class="text-center p-5"><h3>No questions found.</h3><a href="index.php" class="btn btn-primary mt-3">Back</a></div>
                    <?php else: ?>

                        <form method="POST" id="assessmentForm">
                            <input type="hidden" name="page" value="<?php echo $current_page_num; ?>">
                            
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-1 fw-bold"><?php echo htmlspecialchars($assignment['survey_name']); ?></h4>
                                            <p class="text-muted mb-0 small">Domain <?php echo $current_page_num; ?> of <?php echo $total_domains; ?>: <strong><?php echo htmlspecialchars($current_domain_data['name']); ?></strong></p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo ($current_page_num / $total_domains) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <?php foreach ($current_domain_data['criteria'] as $crit): ?>
                                    <div class="card shadow-sm mb-4 border-0">
                                        <div class="card-header bg-white py-3 border-bottom"><h5 class="mb-0 text-primary"><?php echo htmlspecialchars($crit['name']); ?></h5></div>
                                        <div class="card-body p-4">
                                            <?php foreach ($crit['elements'] as $elem_id => $elem): ?>
                                                <div class="mb-5" id="el_<?php echo $elem_id; ?>">
                                                    <h6 class="fw-bold mb-3"><?php echo htmlspecialchars($elem['name']); ?></h6>
                                                    
                                                    <div class="score-selection">
                                                        <?php $saved_score = $user_responses[$elem_id] ?? null; ?>
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php 
                                                                $s = $elem['scores'][$i] ?? null;
                                                                $desc = $s['desc_level'] ?? "Level $i";
                                                                $detail = $s['details'] ?? 'No description.';
                                                            ?>
                                                            <div class="d-block mb-2">
                                                                <input type="radio" class="btn-check score-input" 
                                                                    name="responses[<?php echo $elem_id; ?>]" 
                                                                    id="r_<?php echo $elem_id; ?>_<?php echo $i; ?>" 
                                                                    value="<?php echo $i; ?>" 
                                                                    <?php echo ($saved_score == $i) ? 'checked' : ''; ?> required>
                                                                <label class="score-option d-flex align-items-center" for="r_<?php echo $elem_id; ?>_<?php echo $i; ?>">
                                                                    <span class="score-badge"><?php echo $i; ?></span>
                                                                    <div>
                                                                        <div class="text-uppercase fw-bold text-muted small"><?php echo htmlspecialchars($desc); ?></div>
                                                                        <div class="text-dark small"><?php echo htmlspecialchars($detail); ?></div>
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

                            <div class="card shadow-sm fixed-bottom border-0 mt-4">
                                <div class="card-body p-3 bg-white border-top d-flex justify-content-between">
                                    <?php if ($current_page_num > 1): ?>
                                        <a href="?survey_id=<?php echo $survey_id; ?>&page=<?php echo $current_page_num - 1; ?>" class="btn btn-outline-secondary">Previous</a>
                                    <?php else: ?>
                                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                                    <?php endif; ?>

                                    <div>
                                        <?php if ($current_page_num < $total_domains): ?>
                                            <button type="submit" class="btn btn-primary">Next Domain</button>
                                        <?php else: ?>
                                            <button type="submit" name="save_draft" value="1" class="btn btn-outline-primary me-2">Save Draft</button>
                                            <button type="submit" name="finish" value="1" class="btn btn-success" onclick="return confirm('Submit now?');">Submit</button>
                                        <?php endif; ?>
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
        let isDirty = false;
        document.getElementById('assessmentForm')?.addEventListener('change', () => isDirty = true);
        document.getElementById('assessmentForm')?.addEventListener('submit', () => isDirty = false);
        window.addEventListener('beforeunload', (e) => isDirty ? (e.returnValue = '') : undefined);
    </script>
</body>
</html>