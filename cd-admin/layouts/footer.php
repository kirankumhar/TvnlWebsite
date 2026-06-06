            <!-- </div> -->
        </div>
        
        <!-- Footer -->
        <footer class="py-3 px-3 text-center">
            <p class="mb-0 fs-6">Technology Partner <img src="<?= $base_url ?>/assets/images/logos/insta-logo.jpg"
                            width="30px" alt="ced"> <b><span class="footer-text">COMPUTER Ed.</span></b></p>
        </footer>
    </div>
</div>

<script>
        function handleColorTheme(e) {
                document.documentElement.setAttribute("data-color-theme", e);
        }
</script>
<!-- Dark Overlay for Mobile Sidebar -->
<div class="dark-transparent"></div>

<script>
  setTimeout(() => {
    document.querySelectorAll('.alert-success').forEach(alert => {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 300); // remove after fade
    });
  }, 4000);
</script>
<script>
function closeAlert(button) {
    const alertBox = button.closest('.alert');
    if (!alertBox) return;

    // Fade out animation
    alertBox.classList.remove('show');
    alertBox.style.transition = 'opacity 0.3s ease';
    alertBox.style.opacity = '0';

    // Remove element after animation
    setTimeout(() => {
        alertBox.remove();
    }, 300);
}
</script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5.3.0 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- DataTables -->
<script src="<?= $base_url ?>/assets/libs/DataTables/datatables.min.js"></script>

<!-- Modern Admin JS -->
<script src="<?= $base_url ?>/assets/js/modern-admin.js"></script>

<!-- Page Specific Scripts -->
<?php if (isset($embed_script)): ?>
<script src="<?= $base_url . '/assets/js/' . $embed_script ?>"></script>
<?php endif; ?>

</body>

</html>