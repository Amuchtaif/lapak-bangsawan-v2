<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Background backdrop, show/hide based on modal state. -->
    <div id="deleteModalBackdrop"
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity duration-300 ease-out opacity-0"></div>

    <div class="fixed inset-0 z-[10000] w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel, show/hide based on modal state. -->
            <div id="deleteModalPanel"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-slate-800 text-left shadow-xl transition-all duration-300 ease-out opacity-0 scale-95 sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white dark:bg-slate-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-icons-round text-red-600 dark:text-red-500">warning</span>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white"
                                id="modal-title">Hapus Item</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Apakah Anda yakin ingin menghapus
                                    item ini? Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <a id="confirmDeleteBtn" href="#"
                        class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">Hapus</a>
                    <button type="button" onclick="closeDeleteModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-gray-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(url) {
        event.preventDefault();
        const modal = document.getElementById('deleteModal');
        const backdrop = document.getElementById('deleteModalBackdrop');
        const panel = document.getElementById('deleteModalPanel');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        confirmBtn.href = url;
        modal.classList.remove('hidden');

        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const backdrop = document.getElementById('deleteModalBackdrop');
        const panel = document.getElementById('deleteModalPanel');

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');

        // Wait for transition to finish before hiding
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>