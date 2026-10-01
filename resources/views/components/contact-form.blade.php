<?php

use App\Models\ContactMessage;
use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $data = $this->validate();

        ContactMessage::create($data);

        $this->reset('name', 'email', 'subject', 'message');
        $this->sent = true;
    }
};
?>

<div>
    <h2 class="text-lg font-bold text-navy">Send Us a Message</h2>

    @if ($sent)
    <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        Thank you! Your message has been sent successfully.
    </div>
    @endif

    <form wire:submit="submit" class="mt-4 space-y-4">
        <div>
            <label class="text-sm font-semibold text-gray-700">Name</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" wire:model="email" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Subject</label>
            <input type="text" wire:model="subject" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Message</label>
            <textarea wire:model="message" rows="5" class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon"></textarea>
            @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" class="btn-maroon w-full justify-center">
            <span wire:loading.remove>Send Message</span>
            <span wire:loading>Sending...</span>
        </button>
    </form>
</div>
