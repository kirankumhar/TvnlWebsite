<aside class="left-sidebar with-vertical">
    <div style="display: flex; flex-direction: column; height: 100%;">
        <!-- Brand Logo -->
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="<?= $base_url ?>/dashboard_view.php" class="text-nowrap logo-img text-decoration-none">
                <h3 class="mb-0"><?= $app_name; ?></h3>
            </a>
            <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none d-block d-xl-none" id="mobileSidebarToggle">
                <i class="ti ti-x"></i>
            </a>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="list-unstyled mb-0">
                <!-- Dashboard Section -->
                <li class="nav-small-cap">
                    <span>DASHBOARD</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link" href="<?= $base_url ?>/dashboard_view.php" data-page="dashboard">
                        <i class="ti ti-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- <?php if (hasModuleRow($pdo, $userId, 'domain')) { ?>
                    <li class="nav-small-cap">
                        <span>DOMAINS</span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#domainsMenu" aria-expanded="false">
                            <i class="ti ti-world"></i>
                            <span>Add & Manage</span>
                        </a>
                        <ul class="collapse" style="padding:0px !important;" id="domainsMenu">
                            <?php if (canCreate($pdo, $userId, 'domain')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/add-domain.php" data-page="add-domain">
                                        <i class="ti ti-plus"></i>
                                        <span>Add Domain</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-domain.php" data-page="manage-domain">
                                    <i class="ti ti-list"></i>
                                    <span>Manage Domain</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?> -->
                <li class="nav-small-cap">
                    <span>SLIDER</span>
                </li>

                <li class="sidebar-item mt-2">
                    <a class="sidebar-link has-arrow" 
                       href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#sliderMenu" aria-expanded="false">
                        <i class="ti ti-photo fs-5"></i>
                        <span class="hide-menu">Slider</span>
                    </a>
                    <ul aria-expanded="false" class="collapse" id="sliderMenu" style="padding:0px !important;"> 
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/create-slider.php">
                                <i class="ti ti-file-plus"></i>
                                <span class="hide-menu">Add Slider</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-sliders.php">
                                <i class="ti ti-files"></i>
                                <span class="hide-menu">Manage Slider</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <?php if (hasModuleRow($pdo, $userId, 'category')) { ?>
                    <!-- Category & Subcategory Section -->
                    <li class="nav-small-cap">
                        <span>CATEGORY & SUBCATEGORY</span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#categoryMenu" aria-expanded="false">
                            <i class="ti ti-category"></i>
                            <span>Category & more</span>
                        </a>
                        <ul class="collapse" style="padding:0px !important;" id="categoryMenu">
                            <?php if (canCreate($pdo, $userId, 'category')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/create-category.php" data-page="create-category">
                                        <i class="ti ti-plus"></i>
                                        <span>Create Category</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-category.php" data-page="manage-category">
                                    <i class="ti ti-list"></i>
                                    <span>Manage Category</span>
                                </a>
                            </li>
                            <?php if (canCreate($pdo, $userId, 'category')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/create-sub-category.php" data-page="create-sub-category">
                                        <i class="ti ti-plus"></i>
                                        <span>Create Sub-Category</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-sub-category.php" data-page="manage-sub-category">
                                    <i class="ti ti-list"></i>
                                    <span>Manage Sub-Category</span>
                                </a>
                            </li>
                            <?php if (canCreate($pdo, $userId, 'category')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/create-child-sub-category.php" data-page="create-child-sub-category">
                                        <i class="ti ti-plus"></i>
                                        <span>Create Child-Sub-Cate</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-child-sub-category.php" data-page="manage-child-sub-category">
                                    <i class="ti ti-list"></i>
                                    <span>Manage Child-Sub-Cate</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (hasModuleRow($pdo, $userId, 'news')) { ?>
                    <!-- News Section -->
                    <li class="nav-small-cap">
                        <span>NEWS</span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#newsMenu" aria-expanded="false">
                            <i class="ti ti-news"></i>
                            <span>News</span>
                        </a>
                        <ul class="collapse" style="padding:0px !important;" id="newsMenu">
                            <?php if (canCreate($pdo, $userId, 'news')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/post-news.php" data-page="post-news">
                                        <i class="ti ti-plus"></i>
                                        <span>Post News</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-news.php" data-page="manage-news">
                                    <i class="ti ti-list"></i>
                                    <span>Manage News</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (hasModuleRow($pdo, $userId, 'notices')) { ?>
                    <!-- General Postings Section -->
                    <li class="nav-small-cap">
                        <span>GENERAL POSTINGS</span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#noticesMenu" aria-expanded="false">
                            <i class="ti ti-file-text"></i>
                            <span>Notices</span>
                        </a>
                        <ul class="collapse" style="padding:0px !important;" id="noticesMenu">
                            <?php if (canCreate($pdo, $userId, 'notices')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/create-notice.php" data-page="create-notice">
                                        <i class="ti ti-plus"></i>
                                        <span>Post Notice</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-notices.php" data-page="manage-notices">
                                    <i class="ti ti-list"></i>
                                    <span>Manage Notice</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                

                <?php if (hasModuleRow($pdo, $userId, 'mediaPressclip') || hasModuleRow($pdo, $userId, 'mediaPhoto') || hasModuleRow($pdo, $userId, 'mediavideo')) { ?>
                    <!-- Gallery Section -->
                    <li class="nav-small-cap">
                        <span>GALLERY</span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#galleryMenu" aria-expanded="false">
                            <i class="ti ti-photo"></i>
                            <span>Galleries & manage</span>
                        </a>
                        <ul class="collapse" style="padding:0px !important;" id="galleryMenu">
                            <?php if (canCreate($pdo, $userId, 'mediaPressclip') || canCreate($pdo, $userId, 'mediaPhoto') || canCreate($pdo, $userId, 'mediavideo')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link" href="<?= $base_url ?>/post-album.php" data-page="post-album">
                                        <i class="ti ti-plus"></i>
                                        <span>Post Albums</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-photos.php" data-page="manage-photos">
                                    <i class="ti ti-photo"></i>
                                    <span>Manage Photos</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-videos.php" data-page="manage-videos">
                                    <i class="ti ti-video"></i>
                                    <span>Manage Videos</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= $base_url ?>/manage-press-clips.php" data-page="manage-press-clips">
                                    <i class="ti ti-clipboard"></i>
                                    <span>Manage Press Clips</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Tenders Section -->
                <li class="nav-small-cap">
                    <span>TENDERS</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#tendersMenu" aria-expanded="false">
                        <i class="ti ti-file-invoice"></i>
                        <span>Tenders Notice</span>
                    </a>
                    <ul class="collapse" style="padding:0px !important;" id="tendersMenu">
                        <li class="sidebar-item"> 
                            <a class="sidebar-link" href="<?= $base_url ?>/add-department.php">
                                <i class="ti ti-plus"></i>    
                                <span>Add Department</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-departments.php" data-page="manage-departments">
                                <i class="ti ti-list"></i>
                                <span>Manage Departments</span>
                            </a>
                        </li>
                        <hr>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/add-tender.php" data-page="create-tender">
                                <i class="ti ti-plus"></i>
                                <span>Post Tender</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-tenders.php" data-page="manage-tenders">
                                <i class="ti ti-list"></i>
                                <span>Manage Tenders</span>
                            </a>
                        </li>
                        <hr>
                        <li class="sidebar-item"> 
                            <a class="sidebar-link" href="<?= $base_url ?>/create-corrigendum.php" data-page="post-corrigendum">
                                <i class="ti ti-plus"></i>
                                <span>Post Corrigendum</span>
                            </a>
                        </li>
                        <li class="sidebar-item"> 
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-corrigendums.php" data-page="">
                                <i class="ti ti-list"></i>
                                <span>Manage Corrigendum</span>
                            </a>
                        </li>
                        <hr>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?=  $base_url ?>/create-extension.php" data-page="">
                                <i class="ti ti-plus"></i>
                                <span>Post Extension</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-extensions.php" data-page="">
                                <i class="ti ti-list"></i>
                                <span>Manage Extensions</span>
                            </a>
                        </li>
                        <hr>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/create-cancellation.php">
                                <i class="ti ti-plus"></i>
                                <span>Post Cancellation</span>
                            </a> 
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-cancellation.php">
                                <i class="ti ti-list"></i>
                                <span>Manage Cancellation</span>
                            </a> 
                        </li>
                    </ul>
                </li>

                <!-- Settings Section -->
                <?php if (hasModuleRow($pdo, $userId, 'permission')) { ?>
                    <li class="nav-small-cap">
                        <span>SETTINGS</span>
                    </li>
                    <?php if ($authRole == 'superadmin') { ?>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/user_logs.php" data-page="dashboard">
                                <i class="ti ti-activity"></i>
                                <span>Activity Logs</span>
                            </a>
                        </li>
                    <?php } ?>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= $base_url ?>/permission-list.php" data-page="dashboard">
                            <i class="ti ti-shield-lock"></i>
                            <span>Access Control</span>
                        </a>
                    </li>
                <?php } ?>


                <?php if (hasModuleRow($pdo, $userId, 'users')) { ?>
                    <!-- User Management Section -->
                    <li class="nav-small-cap">
                        <span>USER MANAGEMENT</span>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow"
                            href="javascript:void(0)"
                            data-bs-toggle="collapse"
                            data-bs-target="#userModule"
                            aria-expanded="false">

                            <i class="ti ti-users"></i>
                            <span>User Management</span>
                        </a>

                        <ul class="collapse p-0" id="userModule">
                            <?php if (canCreate($pdo, $userId, 'users')) : ?>
                                <li class="sidebar-item">
                                    <a class="sidebar-link"
                                        href="<?= $base_url ?>/add-user.php"
                                        data-page="add-user">
                                        <i class="ti ti-user-plus"></i>
                                        <span>Add User</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="sidebar-item">
                                <a class="sidebar-link"
                                    href="<?= $base_url ?>/manage-user.php"
                                    data-page="manage-user">
                                    <i class="ti ti-list-details"></i>
                                    <span>User List</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <!-- Profile Section -->
                <li class="nav-small-cap">
                    <span>PROFILE</span>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#profileMenu" aria-expanded="false">
                        <i class="ti ti-user"></i>
                        <span>Profile & Settings</span>
                    </a>
                    <ul class="collapse" style="padding:0px !important;" id="profileMenu">
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/manage-profile.php" data-page="manage-profile">
                                <i class="ti ti-user-circle"></i>
                                <span>Manage Profile</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="<?= $base_url ?>/update-password.php" data-page="update-password">
                                <i class="ti ti-key"></i>
                                <span>Update Password</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- User Profile Section -->
        <div class="fixed-profile">
            <div class="d-flex align-items-center gap-3">
                <div class="john-img">
                    <img src="<?= $base_url ?>/assets/images/profile/user-1.jpg" class="rounded-circle" width="40" height="40" alt="User" />
                </div>
                <div class="john-title flex-grow-1">
                    <h6 class="mb-0 fw-semibold"><?= $username; ?></h6>
                </div>
                <a href="<?= $base_url ?>/src/controllers/LogoutController.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout">
                    <i class="ti ti-power"></i>
                </a>
            </div>
        </div>
    </div>
</aside>