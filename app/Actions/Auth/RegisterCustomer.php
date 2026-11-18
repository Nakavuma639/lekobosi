<?php

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisterCustomer
{
    /**
     * Create a customer account. The role is always forced to customer.
     */
    public function handle(string $name, string $email, string $phone, string $password): User
    {
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = $password;
        $user->role = UserRole::Customer;
        $user->is_active = true;
        $user->save();

        event(new Registered($user));

        return $user;
    }
}
