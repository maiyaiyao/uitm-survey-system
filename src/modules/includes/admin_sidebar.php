<?php
    // Detect current script path
    $current_page = $_SERVER['SCRIPT_NAME'] ?? '';

    // --- Helper Functions for Active State ---
    if (!function_exists('is_nav_active')) {
        function is_nav_active(string $current_page, string $link_path): string {
            return str_ends_with(strtolower($current_page), strtolower($link_path)) ? 'active' : '';
        }
    }

    if (!function_exists('is_module_active')) {
        function is_module_active(string $current_page, string $module_path): string {
            return str_contains(strtolower($current_page), strtolower($module_path)) ? 'active' : '';
        }
    }

    // Determine if the "Parameter Settings" dropdown should be open
    $is_domain_active = is_module_active($current_page, '/domain/');
    $is_criteria_active = is_module_active($current_page, '/criteria/');
    $is_element_active = is_module_active($current_page, '/element/');
    $is_score_active = is_module_active($current_page, '/score/');
    $is_settings_main_active = str_contains($current_page, 'parameter-settings.php');
    $is_control_active = is_module_active($current_page, '/control/');
    

    // Expand if any child is active OR if the main settings page is active
    $is_param_expanded = $is_domain_active || $is_criteria_active || $is_element_active || $is_score_active || $is_control_active || $is_settings_main_active;
    
    $param_collapse_class = $is_param_expanded ? 'show' : '';
    
    // Logic for the chevron button
    $chevron_class = $is_param_expanded ? '' : 'collapsed';
?>

<style>
    /* Force width on desktop */
    @media (min-width: 992px) {
        .sidebar {
            width: 270px !important;
            flex: 0 0 270px !important;
            transition: width 0.3s ease;
        }
        .main-content-wrapper {
            margin-left: 270px !important;
            width: calc(100% - 270px) !important;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* --- COLLAPSED STATE STYLES --- */
        body.sb-collapsed .sidebar {
            width: 80px !important;
            flex: 0 0 80px !important;
        }
        body.sb-collapsed .main-content-wrapper {
            margin-left: 80px !important;
            width: calc(100% - 80px) !important;
        }
        
        /* 1. Hide Text Elements */
        body.sb-collapsed .sidebar .sidebar-brand-text,
        body.sb-collapsed .sidebar .nav-link span,
        body.sb-collapsed .sidebar .sidebar-search,
        body.sb-collapsed .sidebar .btn-logout span,
        body.sb-collapsed .sidebar .sidebar-brand small {
            display: none !important;
        }

        /* 2. Fix Dropdown/Chevron Wrappers */
        /* This fixes the "Parameter Settings" alignment issue */
        body.sb-collapsed .sidebar .d-flex.align-items-stretch {
            display: block !important; /* Stop flex behavior */
            width: 100%;
        }
        /* Hide the chevron link entirely */
        body.sb-collapsed .sidebar .d-flex.align-items-stretch > a:last-child {
            display: none !important; 
        }
        /* Force the icon link to center */
        body.sb-collapsed .sidebar .d-flex.align-items-stretch > a:first-child {
            width: 100%;
            justify-content: center !important;
            padding: 1rem 0 !important;
        }

        /* 3. Center Standard Icons */
        body.sb-collapsed .sidebar .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            text-align: center;
        }
        body.sb-collapsed .sidebar .nav-link-icon {
            margin-right: 0;
        }
        
        /* 4. Fix Brand Alignment */
        body.sb-collapsed .sidebar .sidebar-brand {
            justify-content: center;
            padding: 1rem 0;
            flex-direction: column;
            gap: 10px;
        }
        body.sb-collapsed .sidebar .sidebar-brand-icon {
            margin-right: 0;
        }
        
        /* 5. Hide Nested Menus */
        body.sb-collapsed .sb-sidenav-menu-nested {
            display: none !important; 
        }
        
        /* 6. Reposition Toggle Button in Collapsed Mode */
        body.sb-collapsed #sidebarToggle {
            position: relative; /* Move into flow or absolute relative to header */
            top: auto;
            right: auto;
            left: auto;
            margin-top: 10px;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
        }
    }

    /* Medium screens / Split screen (768px - 991px) - Auto-collapse sidebar */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .sidebar {
            width: 80px !important;
            flex: 0 0 80px !important;
        }
        
        .main-content-wrapper {
            margin-left: 80px !important;
            width: calc(100% - 80px) !important;
        }
        
        /* Hide text elements on medium screens */
        .sidebar .sidebar-brand-text,
        .sidebar .nav-link span,
        .sidebar .sidebar-search,
        .sidebar .btn-logout span,
        .sidebar .sidebar-brand small,
        .sidebar .sb-sidenav-menu-nested {
            display: none !important;
        }
        
        /* Center icons */
        .sidebar .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        
        .sidebar .nav-link-icon {
            margin-right: 0;
        }
        
        .sidebar .sidebar-brand {
            justify-content: center;
            padding: 1rem 0;
            flex-direction: column;
        }
        
        .sidebar .sidebar-brand-icon {
            margin-right: 0;
        }
        
        /* Hide toggle button on medium screens (always collapsed) */
        #sidebarToggle {
            display: none !important;
        }
        
        /* Hide chevron on medium screens */
        .sidebar .d-flex.align-items-stretch > a:last-child {
            display: none !important;
        }
        
        .sidebar .d-flex.align-items-stretch > a:first-child {
            width: 100%;
            justify-content: center !important;
            padding: 1rem 0 !important;
        }
    }

    /* --- STANDARD STYLES --- */
    .sidebar {
        position: fixed; 
        top: 0; 
        bottom: 0; 
        left: 0;
        width: 16.6667%;
        z-index: 1000;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        color: white;
        overflow-y: auto;
        padding-right: 0 !important;
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
    }

    .sidebar-brand-text h5,
    .sidebar-brand-text small {
        color: #ffffff;
        margin: 0;
        white-space: nowrap;
    }

    .sidebar-brand-icon {
        min-width: 48px;
        height: 48px;
        font-size: 24px;
        background: rgba(255,255,255,0.15);
        color: white;
        border-radius: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 12px; 
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
        top: 24px; /* Align with icon center */
        right: 15px;
    }
    #sidebarToggle:hover {
        background: rgba(255,255,255,0.3);
    }

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

    .nav-link .chevron-icon {
        transition: transform 0.2s ease;
        margin-left: auto;
    }
    .nav-link:not(.collapsed) .chevron-icon {
        transform: rotate(180deg);
    }

    .sb-sidenav-menu-nested {
        background-color: rgba(0, 0, 0, 0.1);
    }
    .sb-sidenav-menu-nested .nav-link {
        padding-left: 3.5rem; 
        font-size: 0.9em;
    }

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
</style>

<div class="col-md-3 col-lg-2 px-0 sidebar min-vh-100 d-flex flex-column" id="adminSidebar">
    
    <div class="sidebar-brand d-flex align-items-center p-4 position-relative">
        <div class="sidebar-brand-icon">
            <i class="bi bi-shield-lock-fill fs-5"></i>
        </div>
        <div class="sidebar-brand-text ms-3">
            <h5>Admin</h5>
            <small>UiTM Secure</small>
        </div>
        <button id="sidebarToggle" class="d-none d-lg-flex" title="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="sidebar-search px-4 pb-3">
        <form action="<?php echo BASE_URL; ?>/modules/admin/search/search-results.php" method="GET">
            <div class="input-group">
                <button class="btn" type="submit">
                    <i class="bi bi-search text-white"></i>
                </button>
                <input type="search" name="query" class="form-control ps-2 bg-transparent border-0 text-white"
                       placeholder="Search..."
                       value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
            </div>
        </form>
    </div>

    <div class="flex-grow-1 overflow-y-auto sidebar-nav">
        <nav class="nav flex-column">

            <div class="nav-item">
                <a class="nav-link <?php echo is_nav_active($current_page, '/dashboard.php'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/admin/dashboard.php" title="Dashboard">
                    <div class="nav-link-icon"><i class="bi bi-grid-1x2-fill"></i></div>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link <?php echo is_module_active($current_page, '/survey/'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/admin/survey/index.php" title="Surveys">
                    <div class="nav-link-icon"><i class="bi bi-clipboard-data-fill"></i></div>
                    <span>Surveys</span>
                </a>
            </div>

            <div class="nav-item">
                <div class="d-flex align-items-stretch">
                    <a class="nav-link flex-grow-1 <?php echo $is_settings_main_active ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>/modules/admin/parameter-settings.php"
                       title="Parameter Settings"> 
                       <div class="nav-link-icon"><i class="bi bi-sliders2"></i></div>
                       <span>Parameter Setting</span>
                    </a>

                    <a class="nav-link px-3 <?php echo $chevron_class; ?>" 
                       href="#" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#collapseParams" 
                       aria-expanded="<?php echo $is_param_expanded ? 'true' : 'false'; ?>" 
                       aria-controls="collapseParams"
                       style="border-left: none; padding-left: 10px;">
                        <div class="chevron-icon">
                            <i class="bi bi-chevron-down"></i>
                        </div>
                    </a>
                </div>
                
                <div class="collapse <?php echo $param_collapse_class; ?>" id="collapseParams" data-bs-parent="#adminSidebar">
                    <nav class="nav flex-column sb-sidenav-menu-nested">
                        <a class="nav-link <?php echo $is_domain_active; ?>" href="<?php echo BASE_URL; ?>/modules/admin/domain/index.php">
                            <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> <span>Domains</span>
                        </a>
                        <a class="nav-link <?php echo $is_criteria_active; ?>" href="<?php echo BASE_URL; ?>/modules/admin/criteria/index.php">
                            <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> <span>Criteria</span>
                        </a>
                        <a class="nav-link <?php echo $is_element_active; ?>" href="<?php echo BASE_URL; ?>/modules/admin/element/index.php">
                            <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> <span>Elements</span>
                        </a>
                        <a class="nav-link <?php echo $is_score_active; ?>" href="<?php echo BASE_URL; ?>/modules/admin/score/levels.php">
                            <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> <span>Global Levels</span>
                        </a>
                        <a class="nav-link <?php echo $is_control_active; ?>" href="<?php echo BASE_URL; ?>/modules/admin/control/index.php">
                            <i class="bi bi-circle-fill me-2" style="font-size: 6px;"></i> <span>ISO Controls</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <div class="nav-item">
                <a class="nav-link <?php echo is_nav_active($current_page, '/report/index.php'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/admin/report/index.php" title="Analytics">
                    <div class="nav-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
                    <span>Analytics</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link <?php echo is_module_active($current_page, '/user/'); ?>" 
                   href="<?php echo BASE_URL; ?>/modules/admin/user/index.php" title="User Management">
                    <div class="nav-link-icon"><i class="bi bi-people-fill"></i></div>
                    <span>Users</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const body = document.body;
    
    // Check local storage for preference
    const isCollapsed = localStorage.getItem('sb|sidebar-toggle') === 'true';
    if (isCollapsed) {
        body.classList.add('sb-collapsed');
    }

    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sb-collapsed');
            localStorage.setItem('sb|sidebar-toggle', body.classList.contains('sb-collapsed'));
        });
    }
});
</script>