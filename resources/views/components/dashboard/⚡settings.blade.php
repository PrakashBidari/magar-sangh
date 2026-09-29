<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $logo_file;
    public $flag_file;
    public $president_photo;

    public string $site_name_np = '';
    public string $site_name_en = '';
    public string $tagline_np = '';
    public string $tagline_en = '';
    public string $logo_url = '';
    public string $flag_url = '';
    public string $phone = '';
    public string $email = '';
    public string $address_np = '';
    public string $address_en = '';
    public string $map_embed_url = '';
    public string $facebook_url = '';
    public string $instagram_url = '';
    public string $youtube_url = '';
    public string $twitter_url = '';
    public string $tiktok_url = '';
    public string $footer_credit = '';
    public string $history_content = '';
    public string $mission_vision_content = '';
    public string $constitution_content = '';
    public string $about_short_np = '';
    public string $about_short_en = '';
    public string $president_message_np = '';
    public string $president_name_np = '';
    public string $president_photo_url = '';
    public ?int $stat_members = 0;
    public ?int $stat_districts = 0;
    public ?int $stat_countries = 0;
    public ?int $stat_sister_orgs = 0;

    public function mount(): void
    {
        // Nullable columns come back as null, but the typed properties above expect strings / ints.
        foreach (Setting::current()->only($this->fillableKeys()) as $key => $value) {
            $this->{$key} = $value ?? (str_starts_with($key, 'stat_') ? 0 : '');
        }
    }

    protected function fillableKeys(): array
    {
        return (new Setting())->getFillable();
    }

    protected function rules(): array
    {
        // Every field is optional; the rules only reject values that are malformed or too long to store.
        $text = ['nullable', 'string', 'max:255'];
        $link = ['nullable', 'url', 'max:255'];
        $count = ['nullable', 'integer', 'min:0', 'max:4294967295'];

        return [
            'site_name_np' => $text,
            'site_name_en' => $text,
            'tagline_np' => $text,
            'tagline_en' => $text,
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_np' => $text,
            'address_en' => $text,
            'map_embed_url' => ['nullable', 'url', 'max:1000'],
            'facebook_url' => $link,
            'instagram_url' => $link,
            'youtube_url' => $link,
            'twitter_url' => $link,
            'tiktok_url' => $link,
            'footer_credit' => $text,
            'about_short_en' => ['nullable', 'string', 'max:5000'],
            'president_message_np' => ['nullable', 'string', 'max:5000'],
            'president_name_np' => $text,
            'stat_members' => $count,
            'stat_districts' => $count,
            'stat_countries' => $count,
            'stat_sister_orgs' => $count,
            'logo_file' => ['nullable', 'image', 'max:2048'],
            'flag_file' => ['nullable', 'image', 'max:2048'],
            'president_photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function messages(): array
    {
        return [
            '*.url' => 'Enter a full link starting with http:// or https://.',
            '*.image' => 'Choose an image file (JPG, PNG, WEBP or GIF).',
            'logo_file.max' => 'The logo must be 2 MB or smaller.',
            'flag_file.max' => 'The flag image must be 2 MB or smaller.',
            'president_photo.max' => 'The photo must be 2 MB or smaller.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'site_name_np' => 'Site Name (Nepali)', 'site_name_en' => 'Site Name (English)',
            'tagline_np' => 'Tagline (Nepali)', 'tagline_en' => 'Tagline (English)',
            'address_np' => 'Address (Nepali)', 'address_en' => 'Address (English)',
            'map_embed_url' => 'Google Map Embed URL', 'facebook_url' => 'Facebook URL', 'instagram_url' => 'Instagram URL',
            'youtube_url' => 'YouTube URL', 'twitter_url' => 'Twitter URL', 'tiktok_url' => 'TikTok URL',
            'footer_credit' => 'Footer Credit Line', 'about_short_en' => 'About (short, English)',
            'president_message_np' => 'President Message', 'president_name_np' => 'President Name & Title',
            'stat_members' => 'Members', 'stat_districts' => 'Districts', 'stat_countries' => 'Countries', 'stat_sister_orgs' => 'Sister Orgs',
            'logo_file' => 'logo', 'flag_file' => 'flag image', 'president_photo' => 'president photo',
        ];
    }

    public function save(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch('settings-result');

            throw $e;
        }

        // Blank number boxes count as zero.
        foreach (['stat_members', 'stat_districts', 'stat_countries', 'stat_sister_orgs'] as $stat) {
            $this->{$stat} ??= 0;
        }

        $uploads = [
            'logo_file' => ['logo_url', 'branding'],
            'flag_file' => ['flag_url', 'branding'],
            'president_photo' => ['president_photo_url', 'president'],
        ];

        foreach ($uploads as $fileProp => [$urlProp, $folder]) {
            if ($this->{$fileProp}) {
                $this->{$urlProp} = Storage::disk('public')->url($this->{$fileProp}->store($folder, 'public'));
                $this->reset($fileProp);
            }
        }

        Setting::current()->update($this->only($this->fillableKeys()));

        session()->flash('dashboard-status', 'Settings updated successfully.');
        $this->dispatch('settings-result');
    }
};
?>

<div class="max-w-4xl">
    <h1 class="text-xl font-bold text-navy">Site Settings</h1>
    <p class="mt-1 text-sm text-gray-500">These values power the header, footer, contact page and map across the entire site.</p>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif

    @if ($errors->any())
    <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <div class="font-semibold">Settings were not saved. Please fix the following:</div>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <p class="mt-2 text-xs text-gray-400">All fields are optional.</p>

    <form wire:submit="save" class="mt-6 space-y-8">
        <section class="card">
            <h2 class="font-bold text-navy">Organization Identity</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Site Name (Nepali)</label>
                    <input type="text" wire:model="site_name_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('site_name_np') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Site Name (English)</label>
                    <input type="text" wire:model="site_name_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('site_name_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Tagline (Nepali)</label>
                    <input type="text" wire:model="tagline_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('tagline_np') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Tagline (English)</label>
                    <input type="text" wire:model="tagline_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('tagline_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Logo</label>
                    <input type="file" wire:model="logo_file" accept="image/*" class="mt-1 w-full text-sm">
                    @error('logo_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if($logo_file)
                    <img src="{{ $logo_file->temporaryUrl() }}" class="mt-2 h-16 w-auto object-contain">
                    @elseif($logo_url)
                    <img src="{{ $logo_url }}" class="mt-2 h-16 w-auto object-contain">
                    @endif
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Flag Image</label>
                    <input type="file" wire:model="flag_file" accept="image/*" class="mt-1 w-full text-sm">
                    @error('flag_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if($flag_file)
                    <img src="{{ $flag_file->temporaryUrl() }}" class="mt-2 h-8 w-auto object-contain">
                    @elseif($flag_url)
                    <img src="{{ $flag_url }}" class="mt-2 h-8 w-auto object-contain">
                    @endif
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">Contact Details</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Phone</label>
                    <input type="text" wire:model="phone" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Address (Nepali)</label>
                    <input type="text" wire:model="address_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('address_np') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Address (English)</label>
                    <input type="text" wire:model="address_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('address_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-gray-700">Google Map Embed URL</label>
                    <input type="text" wire:model="map_embed_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('map_embed_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">Social Media Links</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Facebook URL</label>
                    <input type="text" wire:model="facebook_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('facebook_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Instagram URL</label>
                    <input type="text" wire:model="instagram_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('instagram_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">YouTube URL</label>
                    <input type="text" wire:model="youtube_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('youtube_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Twitter URL</label>
                    <input type="text" wire:model="twitter_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('twitter_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">TikTok URL</label>
                    <input type="text" wire:model="tiktok_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('tiktok_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Footer Credit Line</label>
                    <input type="text" wire:model="footer_credit" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('footer_credit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">Homepage Content</h2>
            <div class="mt-4 grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">About (short, English)</label>
                    <textarea wire:model="about_short_en" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon"></textarea>
                    @error('about_short_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">President Message (Nepali)</label>
                    <textarea wire:model="president_message_np" rows="4" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon"></textarea>
                    @error('president_message_np') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">President Name &amp; Title</label>
                        <input type="text" wire:model="president_name_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('president_name_np') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">President Photo</label>
                        <input type="file" wire:model="president_photo" accept="image/*" class="mt-1 w-full text-sm">
                        @error('president_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @if ($president_photo)
                        <img src="{{ $president_photo->temporaryUrl() }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                        @elseif ($president_photo_url)
                        <img src="{{ $president_photo_url }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Members</label>
                        <input type="number" wire:model="stat_members" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('stat_members') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Districts</label>
                        <input type="number" wire:model="stat_districts" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('stat_districts') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Countries</label>
                        <input type="number" wire:model="stat_countries" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('stat_countries') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Sister Orgs</label>
                        <input type="number" wire:model="stat_sister_orgs" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    @error('stat_sister_orgs') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">About Us Pages</h2>
            <p class="mt-1 text-xs text-gray-500">Shown on the About Us menu: Our History, Mission &amp; Vision and Constitution. The editor supports images, font sizes, colours, tables and video embeds.</p>
            <div class="mt-4 space-y-6">
                <div wire:ignore>
                    <label class="text-sm font-semibold text-gray-700">Our History</label>
                    <textarea class="ckeditor-field" data-field="history_content">{{ $history_content }}</textarea>
                </div>
                <div wire:ignore>
                    <label class="text-sm font-semibold text-gray-700">Mission &amp; Vision</label>
                    <textarea class="ckeditor-field" data-field="mission_vision_content">{{ $mission_vision_content }}</textarea>
                </div>
                <div wire:ignore>
                    <label class="text-sm font-semibold text-gray-700">Constitution</label>
                    <textarea class="ckeditor-field" data-field="constitution_content">{{ $constitution_content }}</textarea>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" class="btn-maroon">
                <span wire:loading.remove>Save Settings</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </form>

    @script
    <script>
        const wire = $wire;
        // The banners sit at the top of the page while the Save button is at the bottom.
        $wire.on('settings-result', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        document.querySelectorAll('.ckeditor-field').forEach((el) => {
            if (el.dataset.ckInitialized) return;
            el.dataset.ckInitialized = '1';

            NepalRichEditor.init(el, (html) => wire.set(el.dataset.field, html, false));
        });
    </script>
    @endscript
</div>
