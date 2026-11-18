<x-app-layout>
    <x-slot:title>Add staff member | Lekobosi</x-slot:title>

    <x-slot name="header">
        <a href="{{ route('admin.staff.index') }}" class="text-sm text-stone-500 hover:text-brand-700">Staff accounts</a>
        <h1 class="mt-1 text-2xl font-semibold">Add a staff member</h1>
    </x-slot>

    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-5 rounded-xl border border-stone-200 bg-white p-6 sm:p-8" novalidate>
            @csrf
            <x-site.field name="name" label="Full name" autocomplete="off" required />
            <x-site.field name="email" label="Email address" type="email" autocomplete="off" required
                          hint="We'll email them a link to choose their own password. You won't need to know it." />
            <x-site.field name="phone" label="Phone number" type="tel" placeholder="0772 123456" required />
            <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
                <a href="{{ route('admin.staff.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-brand">Create account</button>
            </div>
        </form>
    </div>
</x-app-layout>
