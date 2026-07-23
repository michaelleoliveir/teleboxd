<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />


        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="flex flex-col gap-1">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full bg-tlbx-primary! text-white! hover:brightness-110!" data-test="login-button">
                    {{ __('Sign in') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-tlbx-muted rtl:space-x-reverse">
            <span>{{ __('New to Teleboxd?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Create an account') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
