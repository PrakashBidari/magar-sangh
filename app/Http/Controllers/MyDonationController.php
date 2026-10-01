<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ResourceController;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "My Donations" for every signed-in user: add a donation, follow its review, and
 * fix and resubmit it when an admin returns it. Reuses the dashboard donation form.
 */
class MyDonationController extends ResourceController
{
    protected function key(): string
    {
        return 'donations';
    }

    /** Members must attach the paid bank voucher, and cannot remove it. */
    protected function cfg(): array
    {
        $cfg = parent::cfg();

        foreach ($cfg['fields'] as $i => $field) {
            if ($field['name'] === 'voucher_url') {
                $cfg['fields'][$i]['required_on_create'] = true;
            }
        }

        return $cfg;
    }

    /** Only the signed-in user's own donations. */
    protected function query(): Builder
    {
        return Donation::query()->whereBelongsTo(request()->user())->latest('id');
    }

    public function index(): View
    {
        $donations = $this->query()->get();

        return view('dashboard.donations.mine', [
            'donations' => $donations,
            'approvedTotal' => $donations->where('status', Donation::APPROVED)->sum('amount'),
        ]);
    }

    public function create(): View
    {
        return $this->formView(null);
    }

    /** Editing is only open once an admin has returned the donation for correction. */
    public function edit(int|string $id): View|RedirectResponse
    {
        $donation = $this->find($id);

        if (! $donation->isReturned()) {
            return $this->notEditable($donation);
        }

        return $this->formView($donation);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $donation = $this->find($id);

        if (! $donation->isReturned()) {
            return $this->notEditable($donation);
        }

        return parent::update($request, $id);
    }

    public function show(int|string $id): View
    {
        return view('dashboard.donations.mine-show', ['donation' => $this->find($id)]);
    }

    private function notEditable(Donation $donation): RedirectResponse
    {
        return redirect()->route('dashboard.my-donations.show', $donation->id)->with('dashboard-error', $donation->isApproved()
            ? 'This donation is already approved and can no longer be changed.'
            : 'This donation is waiting for admin review. You can edit it only if an admin returns it for correction.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        abort(404);
    }

    /** Every save goes (back) to the admins for review; the return note stays so they can see what was asked. */
    protected function persist(Request $request, ?Model $model): Model
    {
        $donation = parent::persist($request, $model);

        $donation->forceFill([
            'user_id' => $model?->user_id ?? $request->user()->id,
            'status' => Donation::PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ])->save();

        return $donation;
    }

    protected function formView(?Model $model): View
    {
        $view = parent::formView($model);

        // Pre-fill the donor with the member's own name on a new donation.
        if (! $model) {
            $view->with('values', ['donor_name' => request()->user()->name] + $view->getData()['values']);
        }

        return $view->with([
            'cfg' => ['label' => 'My Donations'] + $view->getData()['cfg'],
            'notice' => $model?->isReturned()
                ? 'An admin returned this donation for correction'.($model->review_note ? ': "'.$model->review_note.'"' : '.').' Fix it and save to send it for review again.'
                : null,
            'submitLabel' => $model ? 'Save & Resubmit' : 'Submit Donation',
        ]);
    }

    protected function formAction(?Model $model): string
    {
        return $model
            ? route('dashboard.my-donations.update', $model->getKey())
            : route('dashboard.my-donations.store');
    }

    protected function listUrl(): string
    {
        return route('dashboard.my-donations.index');
    }

    protected function done(string $verb): RedirectResponse
    {
        return redirect($this->listUrl())->with('dashboard-status', $verb === 'created'
            ? 'Your donation has been submitted. It will be shown as approved once an admin checks it.'
            : 'Your donation has been updated and sent for review again.');
    }
}
