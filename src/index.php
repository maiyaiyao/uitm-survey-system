<?php
/**
 * Main Entry Point
 * UiTM ISO 27001 Audit System
 */

require_once 'config/config.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard based on role
    $role = getCurrentUserRole();
    switch ($role) {
        case 'admin':
            redirect(BASE_URL . '/modules/admin/dashboard.php');
            break;
        case 'auditor':
            redirect(BASE_URL . '/modules/auditor/dashboard.php');
            break;
        default:
            redirect(BASE_URL . '/modules/auth/login.php');
    }
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .landing-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .logo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px 0 0 15px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        /* Custom style for the secondary outline register button */
        .btn-outline-custom {
            border: 2px solid #667eea; /* Primary color border */
            color: #764ba2; /* Primary color text */
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }
        .features i {
            width: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="landing-card row g-0 overflow-hidden">
                    <!-- Left Section -->
                    <div class="col-md-5 logo-section d-flex flex-column align-items-center justify-content-center text-center">
                        <i class="bi bi-shield-check" style="font-size: 5rem;"></i>
                        <h2 class="mt-3 fw-bold">UiTM</h2>
                        <p class="mb-0">ISO 27001 Assessment System</p>
                    </div>

                    <!-- Right Section -->
                    <div class="col-md-7 p-5">
                        <h1 class="mb-4 fw-bold">Welcome to UiTM ISO Level Assessment System</h1>
                        <p class="text-muted mb-4">
                            A comprehensive ISO 27001 Level Assessment System designed for 
                            Universiti Teknologi MARA to manage cybersecurity survey and compliance.
                        </p>
                        
                        <div class="features mb-4">
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Comprehensive Assessment</h6>
                                    <p class="text-muted small mb-0">Domains, Criteria, Elements</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-graph-up text-primary me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Maturity Level Scoring</h6>
                                    <p class="text-muted small mb-0">5-level assessment framework</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <i class="bi bi-file-earmark-text text-info me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Detailed Reports</h6>
                                    <p class="text-muted small mb-0">Generate comprehensive survey reports</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <!-- Login Button -->
                            <a href="<?php echo BASE_URL; ?>/modules/auth/login.php" class="btn btn-custom w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login to System
                            </a>
                            <!-- Register Button (New) -->
                            <a href="<?php echo BASE_URL; ?>/modules/auth/register.php" class="btn btn-outline-custom w-100">
                                <i class="bi bi-person-plus-fill me-2"></i>Register New Account
                            </a>
                        </div>

                        <div class="mt-4 text-center">
                            <small class="text-muted">Version <?php echo APP_VERSION; ?> | © <?php echo date('Y'); ?> UiTM</small>
                        </div>
                    </div>
                </div>
