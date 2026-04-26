<?php

namespace App\Modules\Auth\Application\Actions\Auth;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutUserUseCase
{
    public function execute(Request $request): void
    {
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            $accessToken?->delete();
        }
    }
}
