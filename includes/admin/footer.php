<footer class="mt-6 mb-4 border-t border-slate-200 dark:border-slate-800 pt-4 text-center text-xs text-slate-400">
    <p>&copy; <?php echo date('Y'); ?> Lapak Bangsawan E-commerce. Hak cipta dilindungi.</p>
</footer>
<?php include("modals.php"); ?>
<script>
    // Auto-close notifications
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            const alerts = document.querySelectorAll('.auto-close-alert');
            alerts.forEach(function (alert) {
                // Add opacity-0 class to trigger fade out if using Tailwind
                alert.classList.add('opacity-0');

                // Also set inline style just in case
                alert.style.opacity = '0';

                // Remove from DOM after transition
                setTimeout(function () {
                    alert.style.display = 'none';
                    alert.remove();
                }, 500);
            });
        }, 3000);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.createElement('div');

        // Create backdrop
        backdrop.className = 'fixed inset-0 bg-black/50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0';
        document.body.appendChild(backdrop);

        if (toggle && sidebar) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Toggle sidebar
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    // Show backdrop
                    backdrop.classList.remove('hidden');
                    // Small delay to allow display:block to apply before opacity transition
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                    }, 10);
                } else {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    // Hide backdrop
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => {
                        backdrop.classList.add('hidden');
                    }, 300);
                }
            });

            // Close when clicking backdrop or outside
            const closeSidebar = () => {
                if (window.innerWidth < 1024) { // Only on mobile
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => {
                        backdrop.classList.add('hidden');
                    }, 300);
                }
            };

            backdrop.addEventListener('click', closeSidebar);

            // Also close on route change or link click (optional but good)
            const links = sidebar.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        }
    });
</script>