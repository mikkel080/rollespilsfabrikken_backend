<?php

namespace App\Http\Controllers\Auth;

// Helpers
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\SignupRequest;
use App\Models\User;
use App\Notifications\API\Auth\ActivationEmail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Notifications

// Requests

// Models

// Packages


class AuthController extends Controller
{
    /**
     * Register user
     *
     * @param string username
     * @param string email
     * @param string password
     * @return string activation_token // TODO: REMOVE THIS
     */

    public function signup(SignupRequest $request) {
        $user = $request->validated();
        $user['password'] = Hash::make($user['password']);
        $user['activation_token'] = Str::random(60);

        $user = User::create($user);

        $avatar = (new \Laravolt\Avatar\Avatar)->create($user->username)->getImageObject()->encode('png');
        Storage::disk('local')->put('public/avatars/' . $user->id . '/avatar.png', (string) $avatar);

        $user->notify(new ActivationEmail($user));

        return response()->json([
            'message' => 'Successfully created user',
            'token' => $user->activation_token, // TODO: REMOVE BEFORE PRODUCTION
        ], 201);
    }

    /**
     * Activate account from email token
     *
     * @param string token
     * @return JsonResponse
     */
    public function activate($token) {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid activation token'
            ], 404);
        }

        $user->active = true;
        $user->activation_token = '';
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json([
            'message' => 'Activated',
            'user' => $user
        ]);
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return string access_token
     * @return string token_type
     * @return string expires_at
     */
    // TODO: ADD ERROR MESSAGES FOR NON ACTIVATED ACCOUNT
    // TODO: RESEND MAIL OPTION
    public function login(LoginRequest $request) {
        $credentials = Arr::only($request->validated(), ['email', 'password']);
        $credentials['active'] = 1;
        $credentials['deleted_at'] = null;

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }



        $user = $request->user();

        $createdToken = $user->createToken('Personal Access Token');
        $token = $createdToken->token;

        if ($request->validated()['remember_me']) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'access_token' => $createdToken->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $createdToken->token->expires_at
            )->toDateTime()
        ], 200);
    }

    /**
     * Log the user out
     *
     * @param Request $request
     * @return string message
     */
    public function logout(Request $request) {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out!'
        ], 200);
    }

    /**
     * Fetch the authed user
     *
     * @return JsonResponse
     */
    public function user(Request $request) {
        return response()->json($request->user());
    }
}
