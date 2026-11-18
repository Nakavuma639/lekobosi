<x-app-layout>
    <x-slot:title>Staff | Lekobosi</x-slot:title>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Staff accounts</h1>
                <p class="mt-1 text-sm text-stone-500">Staff can enter bookings, record payments and run reports. Only you can approve, reject or void.</p>
            </div>
            <a href="{{ route('admin.staff.create') }}" class="btn-brand">Add staff member</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <p class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</p>
        @endif

        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white" aria-label="Accounts">
            <ul class="divide-y divide-stone-100">
                @foreach ($accounts as $account)
                    <li class="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
                        <div>
                            <p @class(['font-medium', 'text-stone-900' => $account->is_active, 'text-stone-400 line-through' => ! $account->is_active])>{{ $account->name }}</p>
                            <p class="text-sm text-stone-500">{{ $account->email }} &middot; {{ $account->phone ?? 'no phone' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-700">{{ $account->role->label() }}</span>
                            @unless ($account->is_active)
                                <span class="rounded-full bg-brand-100 px-2.5 py-1 text-xs font-semibold text-brand-800">Disabled</span>
                            @endunless

                            @can('update', $account)
                                @if ($account->is_active)
                                    <form method="POST" action="{{ route('admin.staff.password-link', $account) }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-stone-600 hover:text-brand-700">Resend password link</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.staff.update', $account) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}">
                                    <button type="submit" class="btn-outline py-1.5 text-xs {{ $account->is_active ? 'border-brand-300 text-brand-800' : '' }}">
                                        {{ $account->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-app-layout>
