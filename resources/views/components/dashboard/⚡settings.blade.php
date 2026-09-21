<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
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
    public int $stat_members = 0;
    public int $stat_districts = 0;
    public int $stat_countries = 0;
    public int $stat_sister_orgs = 0;

    public function mount(): void
    {
        // Nullable columns come back as null, but the typed properties above expect strings / ints.
        foreach (Setting::current()->only($this->fillableKeys()) as $key => $value) {
            $this->{$key} = $value ?? (is_int($this->{$key}) ? 0 : '');
        }
    }

    protected function fillableKeys(): array
    {
        return (new Setting())->getFillable();
    }

    protected function rules(): array
    {
        return [
            'site_name_np' => ['required', 'string', 'max:255'],
            'site_name_en' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
            'flag_file' => ['nullable', 'image', 'max:2048'],
            'map_embed_url' => ['nullable', 'string', 'max:1000'],
            'president_photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $this->validate();

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
    }
};
?>

<div class="max-w-4xl">
    <h1 class="text-xl font-bold text-navy">Site Settings</h1>
    <p class="mt-1 text-sm text-gray-500">These values power the header, footer, contact page and map across the entire site.</p>

    @if (session('dashboard-status'))
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('dashboard-status') }}</div>
    @endif

    <form wire:submit="save" class="mt-6 space-y-8">
        <section class="card">
            <h2 class="font-bold text-navy">Organization Identity</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Site Name (Nepali)</label>
                    <input type="text" wire:model="site_name_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Site Name (English)</label>
                    <input type="text" wire:model="site_name_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Tagline (Nepali)</label>
                    <input type="text" wire:model="tagline_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Tagline (English)</label>
                    <input type="text" wire:model="tagline_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Logo</label>
                    <input type="file" wire:model="logo_file" accept="image/*" class="mt-1 w-full text-sm">
                    @error('logo_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if($logo_file)
                    <img src="{{ $logo_file->temporaryUrl() }}" class="mt-2 h-12 w-12 rounded-full object-cover">
                    @elseif($logo_url)
                    <img src="{{ $logo_url }}" class="mt-2 h-12 w-12 rounded-full object-cover">
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
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Address (Nepali)</label>
                    <input type="text" wire:model="address_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Address (English)</label>
                    <input type="text" wire:model="address_en" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-gray-700">Google Map Embed URL</label>
                    <input type="text" wire:model="map_embed_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">Social Media Links</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Facebook URL</label>
                    <input type="text" wire:model="facebook_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Instagram URL</label>
                    <input type="text" wire:model="instagram_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">YouTube URL</label>
                    <input type="text" wire:model="youtube_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Twitter URL</label>
                    <input type="text" wire:model="twitter_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">TikTok URL</label>
                    <input type="text" wire:model="tiktok_url" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Footer Credit Line</label>
                    <input type="text" wire:model="footer_credit" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="font-bold text-navy">Homepage Content</h2>
            <div class="mt-4 grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">About (short, English)</label>
                    <textarea wire:model="about_short_en" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">President Message (Nepali)</label>
                    <textarea wire:model="president_message_np" rows="4" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon"></textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">President Name &amp; Title</label>
                        <input type="text" wire:model="president_name_np" class="np mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
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
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Districts</label>
                        <input type="number" wire:model="stat_districts" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Countries</label>
                        <input type="number" wire:model="stat_countries" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700">Sister Orgs</label>
                        <input type="number" wire:model="stat_sister_orgs" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
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

        document.querySelectorAll('.ckeditor-field').forEach((el) => {
            if (el.dataset.ckInitialized) return;
            el.dataset.ckInitialized = '1';

            NepalRichEditor.init(el, (html) => wire.set(el.dataset.field, html, false));
        });
    </script>
    @endscript
</div>
