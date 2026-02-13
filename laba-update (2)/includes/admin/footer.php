<footer class="mt-12 py-8 border-t border-slate-200 dark:border-slate-800/50">
    <div
        class="flex flex-col md:flex-row items-center justify-between gap-4 opacity-70 hover:opacity-100 transition-opacity duration-500">
        <div class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">
            &copy; <?= date('Y') ?> <span class="text-slate-700 dark:text-slate-200">Lapak Bangsawan</span>. All Rights
            Reserved.
        </div>

        <div class="flex items-center gap-4">
            <span class="text-[9px] font-medium text-slate-400 italic">Created with <span class="text-red-400">❤</span>
                by Abu Aufar</span>
            <div class="h-3 w-px bg-slate-200 dark:bg-slate-700"></div>
            <span class="font-mono text-[9px] text-slate-300 dark:text-slate-700 uppercase">v2.4.2</span>
        </div>
    </div>
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

<!-- Custom Select Component Logic -->
<style>
    .custom-select-active .selected-icon {
        transform: rotate(180deg);
    }

    .custom-select-options {
        z-index: 1000;
        max-width: calc(100vw - 2rem);
    }

    .selected-label,
    .custom-option {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initCustomSelects();
    });

    function initCustomSelects() {
        // Toggle dropdown
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('.custom-select-trigger');
            if (trigger) {
                const wrapper = trigger.closest('.custom-select-wrapper');
                const options = wrapper.querySelector('.custom-select-options');

                // Close other dropdowns
                document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                    if (w !== wrapper) {
                        w.classList.remove('custom-select-active');
                        w.querySelector('.custom-select-options').classList.add('hidden', 'opacity-0', 'translate-y-2');
                    }
                });

                // Toggle this one
                const isActive = wrapper.classList.toggle('custom-select-active');
                if (isActive) {
                    options.classList.remove('hidden');
                    setTimeout(() => {
                        options.classList.remove('opacity-0', 'translate-y-2');
                    }, 10);
                } else {
                    options.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => {
                        options.classList.add('hidden');
                    }, 200);
                }
                return;
            }

            // Close when clicking outside
            if (!e.target.closest('.custom-select-wrapper')) {
                document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                    w.classList.remove('custom-select-active');
                    const options = w.querySelector('.custom-select-options');
                    options.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => {
                        options.classList.add('hidden');
                    }, 200);
                });
            }
        });

        // Handle option selection
        document.addEventListener('click', function (e) {
            const option = e.target.closest('.custom-option');
            if (option) {
                const wrapper = option.closest('.custom-select-wrapper');
                const val = option.dataset.value;
                const label = option.innerText;
                const input = wrapper.querySelector('input[type="hidden"]') || wrapper.querySelector('select');
                const labelDisplay = wrapper.querySelector('.selected-label');

                // Update UI
                if (labelDisplay) labelDisplay.innerText = label;

                // Update Value
                if (input) {
                    input.value = val;
                    // Trigger change event if it's a hidden select
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Close dropdown
                wrapper.classList.remove('custom-select-active');
                const options = wrapper.querySelector('.custom-select-options');
                options.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    options.classList.add('hidden');
                }, 200);

                // Re-style options
                wrapper.querySelectorAll('.custom-option').forEach(opt => {
                    opt.classList.remove('bg-primary/10', 'text-primary', 'font-bold');
                });
                option.classList.add('bg-primary/10', 'text-primary', 'font-bold');

                // Extra: Handle custom onchange logic (like in market.php or products.php filters)
                if (wrapper.dataset.onchange) {
                    const actionString = wrapper.dataset.onchange.replace(/%val%/g, val);
                    try {
                        const actionFn = new Function('val', 'this.val = val; ' + actionString);
                        actionFn.call(wrapper, val);
                    } catch (err) {
                        console.error('Error executing custom onchange:', err);
                    }
                }
            }
        });
    }
</script>