
</div> <!-- End of container -->

<!-- Footer -->
<footer style="background: var(--primary-color); color: white; padding: 2rem; margin-top: 3rem; text-align: center;">
    <p style="margin-bottom: 0.5rem;">
        <strong>Algiers University - Attendance Management System</strong>
    </p>
    <p style="font-size: 0.9rem; opacity: 0.8;">
        &copy; <?php echo date('Y'); ?> Advanced Web Programming Project | All Rights Reserved
    </p>
</footer>

<script>
// Global jQuery ready
$(document).ready(function() {
    // Add any global JavaScript here
    
    // Example: Auto-hide alerts after 5 seconds
    $('.alert').each(function() {
        var $alert = $(this);
        setTimeout(function() {
            $alert.fadeOut('slow');
        }, 5000);
    });
});
</script>

</body>
</html>
