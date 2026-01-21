<?php
/**
 * User Header Component
 * Reusable top navigation bar for the User Portal.
 * * Variables expected:
 * $page_title (string) - Current page title
 * $page_breadcrumbs (array) - [ ['label' => 'Home', 'link' => 'url'], ... ]
 */

// Ensure we have user data
if (!isset($current_user)) {
    $current_user = getCurrentUser(); 
}

// Default Title
$header_title = $page_title ?? 'User Portal';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top px-4 py-3 shadow-sm z-3">
    <div class="container-fluid p-0">
        
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-lg-none me-3" type="button" data-bs-toggle="collapse" data-bs-target="#userSidebar" aria-controls="userSidebar" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div>
                <?php if (!empty($page_breadcrumbs)): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small" style="font-size: 0.75rem;">
                            <?php foreach ($page_breadcrumbs as $crumb): ?>
                                <?php if (!empty($crumb['link'])): ?>
                                    <li class="breadcrumb-item"><a href="<?php echo $crumb['link']; ?>" class="text-decoration-none text-muted"><?php echo htmlspecialchars($crumb['label']); ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($crumb['label']); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>
                
                <h5 class="fw-bold text-dark m-0"><?php echo htmlspecialchars($header_title); ?></h5>
            </div>
        </div>

        <div class="d-flex align-items-center gap-4 ms-auto">
            
            <div class="d-none d-md-block text-end">
                <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Today</small>
                <span class="fw-semibold text-dark small"><?php echo date('D, d M Y'); ?></span>
            </div>
            
            <div class="vr d-none d-md-block text-secondary opacity-25"></div>

            <div class="position-relative cursor-pointer" title="Notifications">
                <i class="bi bi-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="headerUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 shadow-sm" 
                         style="width: 38px; height: 38px; font-weight: 600;">
                        <?php echo strtoupper(substr($current_user['full_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="d-none d-lg-block text-start">
                        <div class="small fw-bold text-dark lh-1"><?php echo htmlspecialchars(mb_strimwidth($current_user['full_name'] ?? 'User', 0, 15, '..')); ?></div>
                        <small class="text-muted" style="font-size: 0.7rem;"><?php echo htmlspecialchars($current_user['user_position'] ?? 'User'); ?></small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 mt-2" aria-labelledby="headerUserDropdown">
                    <li class="px-3 py-2 border-bottom">
                        <span class="d-block small text-muted">Signed in as</span>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($current_user['primary_email'] ?? ''); ?></span>
                    </li>
                    <li><a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/modules/user/profile.php"><i class="bi bi-person-gear me-2"></i>Profile Settings</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/modules/user/report/index.php"><i class="bi bi-file-earmark-text me-2"></i>My Reports</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="<?php echo BASE_URL; ?>/modules/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>