<x-layouts.auth title="Reset Password">
    @if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">New Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Confirm New Password</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <button type="submit" class="btn-maroon w-full justify-center">Reset Password</button>
    </form>
</x-layouts.auth>
