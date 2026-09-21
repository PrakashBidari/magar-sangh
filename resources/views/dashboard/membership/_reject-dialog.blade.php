{{-- One shared "disapprove" dialog; buttons with data-reject="{url}" open it. --}}
<dialog id="reject-dialog" class="w-[92vw] max-w-md rounded-lg p-0 shadow-2xl backdrop:bg-black/50">
    <form method="POST" action="#" id="reject-form" class="p-5">
        @csrf
        <h3 class="text-lg font-bold text-navy">Disapprove application</h3>
        <p class="mt-1 text-sm text-gray-600">You are disapproving <strong id="reject-name"></strong>. The reason is shown to the applicant.</p>
        <label for="reject-reason" class="mt-4 block text-sm font-semibold text-gray-700">Reason <span class="font-normal text-gray-400">(optional)</span></label>
        <textarea id="reject-reason" name="reason" rows="3" maxlength="500" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon" placeholder="e.g. Payment voucher is not readable."></textarea>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="reject-cancel" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="rounded-md bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Disapprove</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    (function () {
        const dialog = document.getElementById('reject-dialog');
        const form = document.getElementById('reject-form');

        // Delegated so it keeps working for rows DataTables re-renders.
        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-reject]');
            if (!button) return;
            form.action = button.dataset.reject;
            document.getElementById('reject-name').textContent = button.dataset.name || 'this applicant';
            document.getElementById('reject-reason').value = '';
            dialog.showModal();
        });

        document.getElementById('reject-cancel').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    })();
</script>
@endpush
