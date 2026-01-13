<?php
/**
 * Certificate Generator
 * Renders a print-friendly certificate of completion.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$user_ID = getCurrentUserId();
$survey_id = $_GET['id'] ?? null;

if (!$survey_id) die("Invalid Request");

// 1. Fetch User & Survey Details with derived Completion Date
$sql_details = "
    SELECT s.*, us.status,
           (SELECT MAX(r.input_at) FROM response r WHERE r.survey_ID = s.survey_ID AND r.user_ID = us.user_ID) as completion_date
    FROM survey s 
    JOIN user_survey us ON s.survey_ID = us.survey_ID 
    WHERE s.survey_ID = :sid AND us.user_ID = :uid AND us.status = 'Completed'
";

$survey = $db->fetchOne($sql_details, [':sid' => $survey_id, ':uid' => $user_ID]);
$user = getCurrentUser();

if (!$survey) die("Certificate not available (Survey incomplete or not found).");

// 2. Calculate Score
// We calculate the average score (1-5) converted to percentage (x20)
$sql_calc = "
    SELECT AVG(r.score * 20) as overall_score
    FROM response r
    WHERE r.survey_ID = :sid AND r.user_ID = :uid
";
$score_data = $db->fetchOne($sql_calc, [':sid' => $survey_id, ':uid' => $user_ID]);
$final_score = round($score_data['overall_score'] ?? 0, 2);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate - <?php echo htmlspecialchars($survey['survey_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #525659; display: flex; justify-content: center; padding: 40px; font-family: 'Times New Roman', serif; }
        .certificate-container {
            width: 297mm; /* A4 Landscape */
            height: 210mm;
            background: #fff;
            position: relative;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            border: 10px solid #ddd;
        }
        .border-inner {
            border: 2px solid #667eea;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 120px; color: rgba(0,0,0,0.03); font-weight: bold; z-index: 0;
            pointer-events: none;
        }
        .header-text { font-size: 48px; color: #764ba2; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .sub-header { font-size: 24px; color: #555; margin-bottom: 40px; }
        .recipient-name { font-size: 42px; color: #333; font-weight: bold; margin: 20px 0; border-bottom: 2px solid #764ba2; display: inline-block; padding: 0 40px 10px; font-style: italic;}
        .survey-name { font-size: 28px; color: #555; font-weight: bold; margin-top: 10px; }
        .score-box { 
            margin: 30px auto; 
            padding: 15px 40px; 
            background: #f8f9fa; 
            border-radius: 50px; 
            border: 1px solid #ddd;
            display: inline-block;
        }
        .date-text { font-size: 18px; color: #777; margin-top: 40px; }
        
        .print-btn { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
        
        @media print {
            body { background: none; padding: 0; }
            .certificate-container { box-shadow: none; border: 5px solid #ddd; width: 100%; height: 100vh; }
            .print-btn { display: none; }
            @page { size: landscape; margin: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn btn-primary btn-lg print-btn">Download / Print</button>

    <div class="certificate-container">
        <div class="border-inner">
            <div class="watermark">COMPLETED</div>
            
            <div class="content" style="z-index: 1;">
                <div class="header-text">Certificate of Completion</div>
                <div class="sub-header">This is to certify that</div>
                
                <div class="recipient-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                
                <p style="font-size: 20px; color: #666; margin-top: 10px;">has successfully completed the assessment for</p>
                
                <div class="survey-name"><?php echo htmlspecialchars($survey['survey_name']); ?></div>
                
                <div class="score-box">
                    <span style="font-size: 18px; color: #777; margin-right: 10px;">Maturity Score Achieved:</span>
                    <strong style="font-size: 24px; color: #764ba2;"><?php echo $final_score; ?>%</strong>
                </div>

                <div class="row mt-5">
                    <div class="col-6 text-start ps-5">
                        <div style="border-top: 1px solid #999; width: 200px; padding-top: 5px;">
                            <strong><?php echo APP_NAME; ?> System</strong><br>
                            <span style="font-size: 14px; color: #888;">Administrator</span>
                        </div>
                    </div>
                    <div class="col-6 text-end pe-5">
                        <div class="date-text">
                            Date: <strong><?php echo $survey['completion_date'] ? date('F d, Y', strtotime($survey['completion_date'])) : date('F d, Y'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>