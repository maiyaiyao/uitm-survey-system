<?php
    // Detect current script path for active state highlighting
    $current_page = $_SERVER['SCRIPT_NAME'] ?? '';

    // --- Helper Functions for Active State ---
    if (!function_exists('is_user_nav_active')) {
        function is_user_nav_active(string $current_page, string $link_path): string {
            // Check if the current page ends with the link path
            return str_ends_with(strtolower($current_page), strtolower($link_path)) ? 'active' : '';
        }
    }
?>

<style>
    /* Force width on desktop - using fixed pixel width */
    @media (min-width: 992px) {
        .sidebar {
            width: 270px !important;
            min-width: 270px !important;
            max-width: 270px !important;
            flex: 0 0 270px !important;
            transition: all 0.3s ease;
        }
        .main-content-wrapper {
            margin-left: 270px !important;
            width: calc(100% - 270px) !important;
            transition: all 0.3s ease;
        }

        /* --- COLLAPSED STATE STYLES --- */
        body.sb-collapsed .sidebar {
            width: 80px !important;
            min-width: 80px !important;
            max-width: 80px !important;
            flex: 0 0 80px !important;
        }
        body.sb-collapsed .main-content-wrapper {
            margin-left: 80px !important;
            width: calc(100% - 80px) !important;
        }

        /* 1. Hide text elements */
        body.sb-collapsed .sidebar .sidebar-brand-text,
        body.sb-collapsed .sidebar .nav-link span,
        body.sb-collapsed .sidebar .btn-logout span,
        body.sb-collapsed .sidebar .sidebar-brand small {
            display: none !important;
        }

        /* 2. Brand Section Layout */
        body.sb-collapsed .sidebar .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 0;
            gap: 20px;
        }
        
        body.sb-collapsed .sidebar .sidebar-brand-icon {
            margin-right: 0;
        }

        /* 3. Toggle Button Layout */
        body.sb-collapsed #sidebarToggle {
            position: static !important;
            margin: 0;
            top: auto;
            right: auto;
            transform: none;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
        }

        /* 4. Center Navigation Icons */
        body.sb-collapsed .sidebar .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            text-align: center;
        }
        body.sb-collapsed .sidebar .nav-link-icon {
            margin-right: 0;
        }
    }

    /* Tablet and smaller screens - Overlay Sidebar */
    @media (max-width: 991.98px) {
        .sidebar {
            position: fixed !important;
            left: -100% !important;
            width: 280px !important;
            min-width: 280px !important;
            max-width: 280px !important;
            height: 100vh !important;
            transition: left 0.3s ease !important;
            z-index: 1050 !important;
        }
        
        /* Show sidebar when active */
        .sidebar.show {
            left: 0 !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
        }
        
        .main-content-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        /* Mobile toggle button */
        #sidebarToggle {
            display: none !important;
        }
        
        #mobileMenuToggle {
            display: flex !important;
        }
        
        /* Overlay backdrop */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
    }
    
    /* Desktop - hide mobile elements */
    @media (min-width: 992px) {
        #mobileMenuToggle {
            display: none !important;
        }
        
        .sidebar-overlay {
            display: none !important;
        }
    }

    /* --- STANDARD STYLES --- */
    .sidebar {
        position: fixed; 
        top: 0; 
        bottom: 0; 
        left: 0;
        z-index: 1000;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        color: white;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 0 !important;
        display: flex;
        flex-direction: column;
    }

    /* Sidebar Brand */
    .sidebar-brand-icon {
        min-width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.15);
        color: white;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 12px; 
    }
    
    .sidebar-brand-text h6, .sidebar-brand-text small {
        margin: 0;
        white-space: nowrap;
    }

    /* Toggle Button - Default Position */
    #sidebarToggle {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        position: absolute;
        top: 24px;
        right: 15px;
    }
    #sidebarToggle:hover {
        background: rgba(255,255,255,0.3);
    }

    /* Navigation Items */
    .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 1rem 1.5rem;
        border-left: 3px solid transparent;
        border-radius: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    .nav-link-icon {
        min-width: 28px;
        height: 28px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: rgba(255,255,255,0.9);
        background: rgba(255,255,255,0.15);
        border-radius: 6px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background: rgba(255,255,255,0.1);
        border-left-color: white;
    }

    .sidebar .nav-link.active .nav-link-icon {
        background: white;
        color: #764ba2;
    }

    /* Logout button */
    .btn-logout {
        background: #fff0f3;
        color: #d63384;
        border: none;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.8rem;
        border-radius: 10px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-logout:hover {
        background: #ffe0e9;
        color: #a61e4d;
        transform: translateY(-1px);
    }
    
    /* Mobile Menu Toggle Button */
    #mobileMenuToggle {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1060;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    
    #mobileMenuToggle:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 8px rgba(0,0,0,0.15);
    }
    
    #mobileMenuToggle i {
        font-size: 1.5rem;
    }
</style>

<div class="sidebar min-vh-100 d-flex flex-column" id="userSidebar">
    
    <div class="sidebar-brand d-flex align-items-center p-4 position-relative">
        <div class="sidebar-brand-icon">
            <i class="bi bi-shield-check fs-5"></i>
        </div>
        <div class="sidebar-brand-text">
            <h6 class="fw-bold text-white">User Portal</h6>
            <small class="text-white opacity-75">Audit System</small>
        </div>
        <button id="sidebarToggle" class="d-none d-lg-flex" title="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="flex-grow-1 overflow-y-auto sidebar-nav mt-2">
        <nav class="nav flex-column">

            <div class="nav-item">
                <a class="nav-link <?php echo is_user_nav_active($current_page, '/dashboard.php'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/user/dashboard.php" title="Dashboard">
                    <div class="nav-link-icon"><i class="bi bi-grid-1x2-fill"></i></div>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <?php 
                    $is_survey_active = (strpos($current_page, '/user/survey/') !== false || strpos($current_page, '/assessment.php') !== false) ? 'active' : '';
                ?>
                <a class="nav-link <?php echo $is_survey_active; ?>" 
                href="<?php echo BASE_URL; ?>/modules/user/survey/index.php" title="My Surveys">
                    <div class="nav-link-icon"><i class="bi bi-clipboard-check-fill"></i></div>
                    <span>My Surveys</span>
                </a>
            </div>

            <div class="nav-item">
                <?php 
                    $is_report_active = (strpos($current_page, '/user/report/') !== false) ? 'active' : '';
                ?>
                <a class="nav-link <?php echo $is_report_active; ?>" 
                   href="<?php echo BASE_URL; ?>/modules/user/report/index.php" title="My Reports">
                    <div class="nav-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
                    <span>My Reports</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link <?php echo is_user_nav_active($current_page, '/profile.php'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/user/profile.php" title="My Profile">
                    <div class="nav-link-icon"><i class="bi bi-person-fill"></i></div>
                    <span>My Profile</span>
                </a>
            </div>

        </nav>
    </div>

    <div class="sidebar-footer p-4">
        <a href="<?php echo BASE_URL; ?>/modules/auth/logout.php" class="btn-logout" title="Sign Out">
            <i class="bi bi-box-arrow-right"></i> <span>Sign Out</span>
        </a>
    </div>
</div>

<!-- Mobile Menu Toggle Button -->
<button id="mobileMenuToggle" title="Menu">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('userSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    // Desktop toggle functionality
    const isCollapsed = localStorage.getItem('sb|sidebar-toggle') === 'true';
    if (isCollapsed && window.innerWidth >= 992) {
        body.classList.add('sb-collapsed');
    }

    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sb-collapsed');
            localStorage.setItem('sb|sidebar-toggle', body.classList.contains('sb-collapsed'));
        });
    }
    
    // Mobile toggle functionality
    if(mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });
    }
    
    // Close sidebar when clicking overlay
    if(overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }
    
    // Close sidebar when clicking a link on mobile
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if(window.innerWidth < 992) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if(window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
});
</script>