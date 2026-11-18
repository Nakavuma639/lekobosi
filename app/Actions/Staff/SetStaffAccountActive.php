<?php

namespace App\Actions\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetStaffAccountActive
{
    /**
     * Enable or disable a staff account. Disabling signs the person out
     * everywhere straight away: their sessions are deleted and their
     * "remember me" token is replaced.
     */
    public function handle(User $staff, bool $active): User
    {
        $staff->is_active = $active;

        if (! $active) {
            $staff->setRememberToken(Str::random(60));
        }

        $staff->save();

        if (! $active && config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $staff->id)
                ->delete();
        }

        return $staff;
    }
}
