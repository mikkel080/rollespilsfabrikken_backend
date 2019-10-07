<?php

namespace App\Http\Controllers;

// Classes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

// Notifications
use App\Notifications\API\Auth\ActivationEmail;

// Requests
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\SignupRequest;

// Models
use App\Models\User;

// Packages
use Carbon\Carbon;
use Avatar;


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
        $user = new User([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activation_token' => Str::random(60),
        ]);

        $user->save();

        $avatar = Avatar::create($user->username)->getImageObject()->encode('png');
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
     * @return json User
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

        return $user;
    }

    /**
     * Login user
     *
     * @param string email
     * @param string password
     * @param string remember_me
     * @return string access_token
     * @return string token_type
     * @return string expires_at
     */
    public function login(LoginRequest $request) {
        $creds = request(['email', 'password']);
        $creds['active'] = 1;
        $creds['deleted_at'] = null;

        if (!Auth::attempt($creds)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();

        $createdToken = $user->createToken('Personal Access Token');
        $token = $createdToken->token;

        if ($request->remember_me) {
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
     * @return json User
     */
    public function user(Request $request) {
        return response()->json($request->user());
    }
}
