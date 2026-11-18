<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('lekobosi:create-admin')]
#[Description('Create (or promote) the owner admin account from ADMIN_SEED_* in .env')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var array{name: ?string, email: ?string, password: ?string} $seed */
        $seed = config('lekobosi.admin.seed');

        $validator = Validator::make($seed, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        if ($validator->fails()) {
            $this->error('Set ADMIN_SEED_NAME, ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD in .env first.');

            foreach ($validator->errors()->all() as $message) {
                $this->line("  - {$message}");
            }

            return self::FAILURE;
        }

        $email = mb_strtolower(trim($seed['email']));

        $user = User::query()->where('email', $email)->first() ?? new User;
        $isNew = ! $user->exists;

        $user->name = $seed['name'];
        $user->email = $email;
        $user->password = $seed['password'];
        $user->role = UserRole::Admin;
        $user->is_active = true;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info($isNew
            ? "Admin account created for {$email}."
            : "Existing account {$email} is now an active admin; password updated.");

        $this->comment('You can now remove ADMIN_SEED_PASSWORD from .env.');

        return self::SUCCESS;
    }
}
