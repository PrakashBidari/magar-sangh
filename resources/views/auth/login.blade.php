<x-layouts.auth title="Login">
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

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-maroon focus:ring-maroon">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="font-semibold text-maroon hover:underline">Forgot password?</a>
        </div>
        <button type="submit" class="btn-maroon w-full justify-center">Login</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Not a member yet? <a href="{{ route('register') }}" class="font-semibold text-maroon hover:underline">Register</a>
    </p>
</x-layouts.auth>
