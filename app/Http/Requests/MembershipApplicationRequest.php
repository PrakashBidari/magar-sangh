<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\MembershipType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Used for both the member's application form (create) and the admin's edit form (update).
 * On update the existing files are kept unless a new one is uploaded.
 */
class MembershipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** The application being edited, or null when a member is applying. */
    protected function membership(): ?Membership
    {
        $membership = $this->route('membership');

        return $membership instanceof Membership ? $membership : null;
    }

    public function rules(): array
    {
        $existing = $this->membership();
        $provinces = config('nepal.provinces');
        $districts = $provinces[$this->input('province')] ?? []; // district => municipalities
        $type = MembershipType::find($this->input('membership_type_id'));

        $typeRule = Rule::exists('membership_types', 'id');
        if (! $existing) {
            $typeRule = $typeRule->where('is_active', true);
        }

        $voucherNeeded = $type?->requiresPayment() && ! $existing?->voucher_path;

        return [
            'membership_type_id' => ['required', $typeRule],
            'full_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'after:1900-01-01', 'before:today'],
            'permanent_address' => ['required', 'string', 'max:255'],
            'current_address' => ['required', 'string', 'max:255'],
            'province' => ['required', Rule::in(array_keys($provinces))],
            'district' => ['required', Rule::in(array_keys($districts))],
            'municipality' => ['required', Rule::in($districts[$this->input('district')] ?? [])],
            'ward_no' => ['required', 'integer', 'min:1', 'max:35'],
            'mobile' => ['required', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'],
            'email' => ['required', 'email', 'max:255'],
            'occupation' => ['required', 'string', 'max:255'],
            'photo' => [$existing ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'voucher' => [$voucherNeeded ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'declaration' => [$existing ? 'nullable' : 'accepted'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * The validated form as model attributes. Uploaded files are stored (and the ones they
     * replace deleted): photo and signature on the public disk, the voucher on the private disk.
     */
    public function applicationData(?Membership $existing = null): array
    {
        $data = $this->safe()->only([
            'membership_type_id', 'full_name', 'surname', 'date_of_birth', 'permanent_address', 'current_address',
            'province', 'district', 'municipality', 'ward_no', 'mobile', 'email', 'occupation',
        ]);

        foreach (['photo' => 'photo_url', 'signature' => 'signature_url'] as $input => $column) {
            if ($this->hasFile($input)) {
                if ($existing?->{$column} && str_starts_with($existing->{$column}, '/storage/')) {
                    Storage::disk('public')->delete(Str::after($existing->{$column}, '/storage/'));
                }
                $data[$column] = '/storage/'.$this->file($input)->store('memberships/'.$input.'s', 'public');
            } elseif ($existing && $input === 'signature' && $this->boolean('remove_signature')) {
                if ($existing->signature_url) {
                    Storage::disk('public')->delete(Str::after($existing->signature_url, '/storage/'));
                }
                $data[$column] = null;
            }
        }

        if ($this->hasFile('voucher')) {
            if ($existing?->voucher_path) {
                Storage::disk('local')->delete($existing->voucher_path);
            }
            $data['voucher_path'] = $this->file('voucher')->store('membership-vouchers', 'local');
        }

        return $data;
    }

    public function attributes(): array
    {
        return [
            'membership_type_id' => 'membership type',
            'date_of_birth' => 'date of birth',
            'ward_no' => 'ward number',
            'photo' => 'photograph',
            'voucher' => 'payment voucher',
            'declaration' => 'declaration',
        ];
    }

    public function messages(): array
    {
        return [
            'district.in' => 'Please choose a district that belongs to the selected province.',
            'municipality.in' => 'Please choose a municipality / rural municipality that belongs to the selected district.',
            'declaration.accepted' => 'You must accept the declaration to submit your application.',
            'mobile.regex' => 'Enter a valid mobile number, for example 98XXXXXXXX.',
            'voucher.required' => 'This membership has a fee. Please upload your payment voucher.',
        ];
    }
}
