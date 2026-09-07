<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        abort_if($user->role === 'admin', 403, 'Gli account amministratore non possono essere eliminati da questa procedura.');

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
