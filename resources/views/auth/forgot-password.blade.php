<x-layouts.auth title="Forgot Password">
    @if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('status'))
    <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">{{ session('status') }}</div>
    @endif

    <p class="mb-4 text-sm text-gray-500">Enter your email address and we'll send you a password reset link.</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <button type="submit" class="btn-maroon w-full justify-center">Send Reset Link</button>
    </form>
</x-layouts.auth>
