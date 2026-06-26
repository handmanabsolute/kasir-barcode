<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    /**
     * Authenticate using username or email.
     */
    public function __invoke(Request $request): ?User
    {
        $login = $request->input(Fortify::username());

        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                Fortify::username() => [__('Username/email atau password salah.')],
            ]);
        }

        return $user;
    }
}
