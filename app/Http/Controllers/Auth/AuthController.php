<?php

namespace App\Http\Controllers\Auth;

// Helpers
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\SignupRequest;
use App\Http\Requests\API\Auth\ResendEmailRequest;
use App\Http\Resources\User\LoggedInUser;
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
use Laravolt\Avatar\Avatar;


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

        $user = (new User)->create($user);

        $avatar = (new Avatar)
            ->create($user->username)
            ->getImageObject()
            ->encode('png');

        Storage::disk('local')->put('public/avatars/' . $user->uuid . '/avatar.png', (string) $avatar);

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

        $user = (new User)->where('email', '=', $credentials['email'])->firstOrFail();

        if ($user['active'] !== 1) {
            return response()->json([
                'message' => 'Konto er ikke aktiveret'
            ], 401);
        }

        if ($user['deleted_at'] !== null) {
            return response()->json([
                'message' => 'Konto er bannet eller slettet'
            ], 401);
        }

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = $request->user();

        $token = $user->createToken('API token');

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer'
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
        return response()->json([
            'message' => 'success',
            'user' => new LoggedInUser(auth()->user())
        ]);
    }

    public function resendEmail(ResendEmailRequest $request) {
        $email = $request->validated()['email'];

        $user = (new User)->where('email', '=', $email)->firstOrFail();
        $user['activation_token'] = Str::random(60);
        $user->save();

        $user->notify(new ActivationEmail($user));

        return response()->json([
            'message' => 'Successfully resend email',
        ], 201);
    }
}
