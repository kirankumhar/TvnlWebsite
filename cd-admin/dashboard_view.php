<?php

require_once __DIR__ . '/src/helpers/session_helper.php';
requireLogin(); // This will redirect if not logged in or session expired

require_once __DIR__ . '/layouts/header.php';

// thought of the day
include('./src/utils/utlis.php');

// dashboard details
include('./src/utils/dashboard.php');
?>
<div class="container-fluid">
    <div class="row g-3">
        <!-- Time & Thought -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap dashboard-header">
                    <div>
                        <h4 class="mb-0 fw-semibold" id="liveTime">--:--</h4>
                        <small class="text-muted" id="liveDate">Loading...</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Thought of the Day</small>
                        <span class="fw-medium fst-italic" id="thoughtText">
                            "<?= $todayThought; ?>"
                        </span>
                    </div>
                </div>

                <?php if ($isExpired == false && $daysLeft < 10) { ?>
                    <div class="system-alert-footer animate-attention">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-alert-triangle"></i>
                            <span>
                                Your password will expire in <strong><?= $daysLeft; ?> days</strong>.
                                Please reset your password before expiry.
                                <strong>Failure to do so will require reset through the System Administrator.</strong>
                                <a href="<?= $base_url ?>/update-password.php">Reset now</a>
                            </span>

                        </div>
                        <?php if ($daysLeft > 5) { ?>
                            <button class="alert-close" onclick="this.parentElement.remove()">
                                <i class="ti ti-x"></i>
                            </button>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>
<div class="container-fluid mt-4">
    <div class="row">
        <?php foreach ($cards as $card) { ?>
            <div class="col-sm-6 col-xl-3 mb-3">
                <a href="<?= $base_url . '/' . $card['url']; ?>">
                    <div class="card overflow-hidden rounded-2 shadow-sm">

                        <div class="card-header p-2 text-center"
                            style="background:<?= $card['color']; ?>;color:#fff;">
                            <h6 class="fw-semibold"><?= $card['title']; ?></h6>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2">
                            <h2 class="mb-0"><?= $card['count']; ?></h2>
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-1">
                                <i class="<?= $card['icon']; ?> fs-3"
                                    style="color:<?= $card['color']; ?>"></i>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
        <?php } ?>
    </div>
</div>


<script>
    /* Live Time & Date */
    function updateTime() {
        const now = new Date();
        document.getElementById('liveTime').innerText =
            now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

        document.getElementById('liveDate').innerText =
            now.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>