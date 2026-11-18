<?php

namespace App\Actions\Staff;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class CreateStaffAccount
{
    /**
     * Create a staff account and email the staff member a link to choose their
     * own password, so the owner never handles it. The account gets a random
     * password nobody knows until then.
     *
     * @return array{user: User, linkSent: bool}
     */
    public function handle(string $name, string $email, string $phone): array
    {
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = Str::password(32);
        $user->role = UserRole::Staff;
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        return ['user' => $user, 'linkSent' => $this->sendPasswordLink($user)];
    }

    /**
     * Send the "set your password" email. A mail failure must not undo the
     * account; the owner can resend from the staff page.
     */
    public function sendPasswordLink(User $user): bool
    {
        try {
            return Password::broker()->sendResetLink(['email' => $user->email]) === Password::RESET_LINK_SENT;
        } catch (Throwable $exception) {
            Log::warning('Staff password link could not be sent.', ['user_id' => $user->id, 'exception' => $exception::class]);

            return false;
        }
    }
}
