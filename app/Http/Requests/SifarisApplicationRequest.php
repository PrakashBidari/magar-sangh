<?php

namespace App\Http\Requests;

use App\Models\SifarisRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Used for both the user's sifaris form (create) and the admin's edit form (update).
 * On update the existing photo is kept unless a new one is uploaded.
 */
class SifarisApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** The request being edited, or null when a user is applying. */
    protected function existing(): ?SifarisRequest
    {
        $sifaris = $this->route('sifaris');

        return $sifaris instanceof SifarisRequest ? $sifaris : null;
    }

    public function rules(): array
    {
        return [
            'applicant_name' => ['required', 'string', 'max:100'],
            'parent_relation' => ['required', Rule::in(array_keys(SifarisRequest::PARENT_RELATIONS))],
            'parent_name' => ['required', 'string', 'max:100'],
            'grandparent_relation' => ['required', Rule::in(array_keys(SifarisRequest::GRANDPARENT_RELATIONS))],
            'grandparent_name' => ['required', 'string', 'max:100'],
            'province' => ['required', Rule::in(SifarisRequest::PROVINCES)],
            'district' => ['required', 'string', 'max:60'],
            'municipality' => ['required', 'string', 'max:100'],
            'ward_no' => ['required', 'string', 'max:10'],
            'mobile' => ['required', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'photo' => [$this->existing() ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'declaration' => [$this->existing() ? 'nullable' : 'accepted'],
        ];
    }

    /** The validated form as model attributes; a new photo is stored and the old one deleted. */
    public function sifarisData(): array
    {
        $data = $this->safe()->only([
            'applicant_name', 'parent_relation', 'parent_name', 'grandparent_relation', 'grandparent_name',
            'province', 'district', 'municipality', 'ward_no', 'mobile', 'email',
        ]);

        if ($this->hasFile('photo')) {
            $this->existing()?->deletePhoto();
            $data['photo_url'] = '/storage/'.$this->file('photo')->store('sifaris/photos', 'public');
        }

        return $data;
    }

    public function attributes(): array
    {
        return [
            'applicant_name' => 'applicant name',
            'parent_relation' => 'relation to father / husband',
            'parent_name' => 'father / husband name',
            'grandparent_relation' => 'relation to grandfather / father-in-law',
            'grandparent_name' => 'grandfather / father-in-law name',
            'municipality' => 'municipality / rural municipality',
            'ward_no' => 'ward number',
            'photo' => 'passport-size photo',
        ];
    }

    public function messages(): array
    {
        return [
            'declaration.accepted' => 'You must confirm that the details are true to submit the request.',
            'mobile.regex' => 'Enter a valid mobile number, for example 98XXXXXXXX.',
        ];
    }
}
