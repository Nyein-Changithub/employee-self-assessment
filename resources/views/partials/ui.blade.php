    <dialog id="confirm-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl p-0 shadow-xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V6m4 5v6m4-6v6"/></svg>
                </div>
                <div class="min-w-0">
                    <h2 id="confirm-title" class="text-lg font-semibold text-slate-900"></h2>
                    <p id="confirm-item" class="mt-1 truncate rounded bg-slate-100 px-2 py-1 text-sm font-medium text-slate-700"></p>
                    <p id="confirm-message" class="mt-2 text-sm text-slate-600"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="confirm-cancel" autofocus class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Cancel') }}</button>
                <button type="button" id="confirm-ok" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"></button>
            </div>
        </div>
    </dialog>
    <script>
        document.querySelectorAll('[data-autodismiss]').forEach((el) => {
            const dismiss = () => { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 500); };
            setTimeout(dismiss, 2000);
            el.querySelector('[data-close]')?.addEventListener('click', dismiss);
        });

        document.querySelectorAll('[data-copy]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const label = btn.querySelector('[data-label]');
                const original = label.textContent;
                try {
                    await navigator.clipboard.writeText(btn.dataset.copy);
                } catch (e) {
                    const t = document.createElement('textarea');
                    t.value = btn.dataset.copy; document.body.appendChild(t); t.select();
                    document.execCommand('copy'); t.remove();
                }
                label.textContent = btn.dataset.copied;
                btn.classList.add('bg-green-600', 'text-white', 'border-green-600');
                setTimeout(() => { label.textContent = original; btn.classList.remove('bg-green-600', 'text-white', 'border-green-600'); }, 2000);
            });
        });

        // Styled confirm dialog: <form data-confirm data-confirm-title data-confirm-message data-confirm-ok data-confirm-item>
        const dialog = document.getElementById('confirm-dialog');
        let pendingForm = null;
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (e) => {
                if (form.dataset.confirmed) return;
                e.preventDefault();
                pendingForm = form;
                document.getElementById('confirm-title').textContent = form.dataset.confirmTitle || '';
                document.getElementById('confirm-message').textContent = form.dataset.confirmMessage || '';
                document.getElementById('confirm-ok').textContent = form.dataset.confirmOk || 'OK';
                const item = document.getElementById('confirm-item');
                item.textContent = form.dataset.confirmItem || '';
                item.classList.toggle('hidden', !form.dataset.confirmItem);
                dialog.showModal();
            });
        });
        document.getElementById('confirm-cancel').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
        document.getElementById('confirm-ok').addEventListener('click', () => {
            dialog.close();
            if (pendingForm) { pendingForm.dataset.confirmed = '1'; pendingForm.submit(); }
        });

        // Edit buttons toggle their row's <details>
        document.querySelectorAll('[data-toggle-details]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const d = btn.closest('[data-row]').querySelector('details');
                d.open = !d.open;
            });
        });
    </script>
