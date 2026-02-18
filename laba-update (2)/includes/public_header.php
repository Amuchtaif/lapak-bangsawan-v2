<?php
// includes/public_header.php
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<header
    class="sticky top-0 z-[90] bg-white/95 dark:bg-[#111318]/95 backdrop-blur-xl border-b border-slate-200/50 dark:border-white/5">
    <div class="max-w-[1400px] mx-auto px-4 md:px-10">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>public/home" class="flex items-center gap-3 group">
                <div
                    class="size-11 rounded-xl overflow-hidden shadow-lg shadow-primary/10 group-hover:scale-105 transition-transform text-primary">
                    <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-col">
                    <span
                        class="text-base font-black tracking-tight text-slate-900 dark:text-white leading-none">LAPAK</span>
                    <span class="text-[10px] font-bold text-primary tracking-[0.2em] leading-none mt-1">BANGSAWAN</span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-1">
                <?php
                $nav_items = [
                    'home' => ['label' => 'Beranda', 'desc' => 'Halaman Utama', 'icon' => 'home', 'url' => 'home'],
                    'market' => ['label' => 'Belanja', 'desc' => 'Produk Segar', 'icon' => 'shopping_basket', 'url' => 'market'],
                    'about' => ['label' => 'Tentang Kami', 'desc' => 'Kisah Kami', 'icon' => 'info', 'url' => 'about'],
                    'track' => ['label' => 'Lacak', 'desc' => 'Cek Status', 'icon' => 'local_shipping', 'url' => 'track']
                ];

                foreach ($nav_items as $key => $item):
                    $is_active = ($current_page == $key || ($current_page == 'index' && $key == 'home'));
                    ?>
                    <a href="<?= BASE_URL . $item['url'] ?>"
                        class="group flex items-center gap-3 px-4 py-2 rounded-xl transition-all <?= $is_active ? 'bg-primary/5' : 'hover:bg-slate-50 dark:hover:bg-white/5' ?>">
                        <div
                            class="size-9 rounded-lg flex items-center justify-center transition-all <?= $is_active ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-slate-100 dark:bg-white/5 text-slate-400 group-hover:text-primary' ?>">
                            <span class="material-symbols-outlined !text-[20px]"><?= $item['icon'] ?></span>
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-sm font-bold transition-colors <?= $is_active ? 'text-primary' : 'text-slate-700 dark:text-slate-300 group-hover:text-primary' ?>"><?= $item['label'] ?></span>
                            <span class="text-[9px] font-medium text-slate-400 tracking-tighter"><?= $item['desc'] ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>cart"
                    class="relative group p-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined !text-[28px]">shopping_cart</span>
                    <span id="cart-badge-header"
                        class="absolute top-1.5 right-1.5 size-2 bg-red-500 rounded-full border-2 border-white dark:border-[#111318] hidden"></span>
                </a>

                <button id="mobile-menu-btn"
                    class="lg:hidden p-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined !text-[32px]">menu</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu Components (Outside Header for fixed positioning) -->
<div id="mobile-menu-overlay"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[1000] hidden opacity-0 transition-opacity duration-300">
</div>

<div id="mobile-menu"
    class="fixed inset-y-0 right-0 z-[1001] w-[280px] bg-white dark:bg-[#1a202c] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col lg:hidden">
    <div
        class="p-6 border-b border-slate-100 dark:border-white/5 flex items-center justify-between bg-white dark:bg-[#1a202c]">
        <div class="flex flex-col">
            <span class="font-bold text-slate-900 dark:text-white">Menu Navigasi</span>
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest mt-0.5">Lapak Bangsawan</span>
        </div>
        <button id="close-menu-btn"
            class="size-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-white/5 text-slate-500 hover:text-red-500 transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <nav class="p-4 flex flex-col gap-2 overflow-y-auto bg-white dark:bg-[#1a202c]">
        <?php foreach ($nav_items as $key => $item):
            $is_active = ($current_page == $key || ($current_page == 'index' && $key == 'home'));
            ?>
            <a href="<?= BASE_URL . $item['url'] ?>"
                class="flex items-center gap-4 p-4 rounded-2xl transition-all <?= $is_active ? 'bg-primary text-white shadow-xl shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' ?>">
                <div
                    class="size-10 rounded-xl flex items-center justify-center <?= $is_active ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/5' ?>">
                    <span class="material-symbols-outlined"><?= $item['icon'] ?></span>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm leading-none"><?= $item['label'] ?></span>
                    <span class="text-[10px] mt-1.5 opacity-70 font-medium"><?= $item['desc'] ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<script>
    (function () {
        const initMenu = () => {
            const btn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');

            if (!btn || !menu || !overlay) return;

            const toggleMenu = (e) => {
                if (e) e.preventDefault();
                const isClosed = menu.classList.contains('translate-x-full');
                if (isClosed) {
                    menu.classList.remove('translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    setTimeout(() => {
                        overlay.classList.remove('opacity-0');
                        overlay.classList.add('opacity-100');
                    }, 10);
                } else {
                    menu.classList.add('translate-x-full');
                    overlay.classList.remove('opacity-100');
                    overlay.classList.add('opacity-0');
                    document.body.style.overflow = '';
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                    }, 300);
                }
            };

            btn.addEventListener('click', toggleMenu);
            if (closeBtn) closeBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMenu);
        } else {
            initMenu();
        }

        const updateBadge = () => {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const badge = document.getElementById('cart-badge-header');
            if (badge) {
                if (cart.length > 0) badge.classList.remove('hidden');
                else badge.classList.add('hidden');
            }
        };
        updateBadge();
        window.addEventListener('storage', updateBadge);
        window.updateCartBadge = updateBadge; // Make accessible globally
    })();
</script>