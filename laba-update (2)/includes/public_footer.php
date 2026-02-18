<footer class="w-full border-t border-[#f0f1f5] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] py-12">
    <div class="px-4 md:px-10 lg:px-40 flex justify-center">
        <div class="w-full max-w-[1200px] flex flex-col gap-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2 flex flex-col gap-4">
                    <div class="flex items-center gap-2 text-[#111318] dark:text-white">
                        <div class="size-8">
                            <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo">
                        </div>
                        <span class="text-lg font-bold">Lapak Bangsawan</span>
                    </div>
                    <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm max-w-xs">
                        Protein hewani kualitas premium yang diantar langsung ke depan pintu Anda
                        dengan jaminan kesegaran.
                    </p>
                </div>
                <div class="flex flex-col gap-4">
                    <h4 class="text-[#111318] dark:text-white font-bold">Belanja</h4>
                    <div class="flex flex-col gap-2 text-sm text-[#606e8a] dark:text-[#a0aec0]">
                        <a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>public/market">Ayam</a>
                        <a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>public/market">Ikan</a>
                        <a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>public/market">Makanan
                            Laut</a>
                        <a class="hover:text-primary transition-colors" href="<?= BASE_URL ?>public/market">Makanan
                            Beku</a>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <h4 class="text-[#111318] dark:text-white font-bold">Kontak</h4>
                    <div class="flex flex-col gap-3 text-sm text-[#606e8a] dark:text-[#a0aec0]">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px]">mail</span>
                            <span><?= get_setting('contact_email', 'lapakbangsawan@gmail.com') ?></span>
                        </div>
                        <a href="https://wa.me/<?= get_setting('contact_wa', '62859110022099') ?>"
                            class="flex items-center gap-3 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[20px]">chat</span>
                            <span>+<?= get_setting('contact_wa', '62859110022099') ?></span>
                        </a>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-[20px] mt-0.5">location_on</span>
                            <span><?= get_setting('contact_address', 'Jl. Wanagati, Karyamulya, Kesambi, Kota Cirebon, Jawa Barat') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="border-t border-[#f0f1f5] dark:border-[#2d3748] pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-[#606e8a] dark:text-[#a0aec0]">©
                    <?= date('Y') ?> Lapak Bangsawan. Hak cipta
                    dilindungi undang-undang.
                </p>
                <div class="flex gap-4">
                    <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors" target="_blank"
                        href="https://www.facebook.com/lapakbangsawan">
                        <!-- Facebook -->
                        <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors" target="_blank"
                        href="<?= get_setting('social_instagram', 'https://instagram.com/lapakbangsawan') ?>">
                        <!-- Instagram -->
                        <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z" />
                        </svg>
                    </a>
                    <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors" target="_blank"
                        href="https://tiktok.com/lapakbangsawan">
                        <!-- TikTok -->
                        <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.65-1.58-1.02v6.1c0 4.64-5.27 8.49-10.05 6.36-2.61-1.14-4.23-4.14-3.72-7.06.67-4.05 5.56-6.17 9.17-3.96v4.3c-1.92-1.07-4.14-.15-4.57 2.07-.44 2.29 1.69 4.37 3.96 3.86 1.48-.34 2.45-1.83 2.37-3.35V.02z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>