<x-layouts.auth title="Register">
    @if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-semibold text-gray-700">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Password</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-maroon focus:ring-maroon">
        </div>
        <button type="submit" class="btn-maroon w-full justify-center">Create Account</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Already have an account? <a href="{{ route('login') }}" class="font-semibold text-maroon hover:underline">Login</a>
    </p>
</x-layouts.auth>
