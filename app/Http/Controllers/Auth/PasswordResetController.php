<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\API\Auth\PasswordResetRequest;
use App\Notifications\API\Auth\PasswordResetSuccess;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User\User as UserResource;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     *
     * @param Request $request
     * @return JsonResponse [string] message
     */
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = (new User)->where('email', $request->email)->firstOrFail();

        $passwordReset = (new PasswordReset)->updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
            ]
        );
        if ($user && $passwordReset)
            $user->notify(new PasswordResetRequest($passwordReset->token));

        return response()->json([
            'message' => 'Email med link sendt!'
        ], 200);
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return JsonResponse [string] message
     * @throws Exception
     */
    public function find($token)
    {
        $passwordReset = (new PasswordReset)->where('token', '=', $token)->firstOrFail();

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'message' => 'Linket er udløbet'
            ], 404);
        }

        return response()->json($passwordReset);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return JsonResponse [string] message
     * @throws Exception
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->firstOrFail();

        $user = User::where('email', $passwordReset->email)->firstOrFail();

        $user->password = Hash::make($request['password']);
        $user->save();
        $user->notify(new PasswordResetSuccess());

        $passwordReset->delete();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh())
        ]);
    }
}
