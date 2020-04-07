<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\User\Ban;
use App\Http\Requests\API\Auth\User\Reset;
use App\Http\Requests\API\Auth\User\Unban;
use App\Http\Resources\User\LoggedInUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\API\Auth\User\UpdateUsername;
use App\Http\Requests\API\Auth\User\Destroy;
use App\Http\Requests\API\Auth\User\DestroySelf;
use App\Http\Resources\User\User as UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Fetch the authed user
     *
     * @return JsonResponse
     */
    public function user() {
        return response()->json([
            'message' => 'success',
            'user' => new LoggedInUser(auth()->user())
        ]);
    }

    /**
     * Update the authed users username
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUsername(UpdateUsername $request) {
        $user = auth()->user();

        $user->username = $request->validated()['username'];
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new LoggedInUser(auth()->user())
        ]);
    }

    /**
     * Delete the authed user
     *
     * @param DestroySelf $request
     * @return JsonResponse
     */
    public function destroySelf(DestroySelf $request) {
        auth()->user()->delete();

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Delete user
     *
     * @param Destroy $request
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(Destroy $request, User $user) {
        $user->delete();

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Ban user
     *
     * @param Ban $request
     * @param User $user
     * @return JsonResponse
     */
    public function ban(Ban $request, User $user) {
        $user->deleted_at = Carbon::now()->toDateTimeString();
        $user->save();

        $user
            ->tokens()
            ->each(function($item, $key) {
                $item->delete();
            });

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Unban user
     *
     * @param Unban $request
     * @param User $user
     * @return JsonResponse
     */
    public function unban(Unban $request, User $user) {
        $user->deleted_at = null;
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Op user
     *
     * @param Ban $request
     * @param User $user
     * @return JsonResponse
     */
    public function op(Ban $request, User $user) {
        $user->super_user = true;
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Deop user
     *
     * @param Unban $request
     * @param User $user
     * @return JsonResponse
     */
    public function deop(Unban $request, User $user) {
        $user->super_user = false;
        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Reset users credentials
     *
     * @param Reset $request
     * @param User $user
     * @return JsonResponse
     */
    public function reset(Reset $request, User $user) {
        $existingUser = User::where('email', '=', $request->validated()['email'])->first();

        if ($existingUser && $existingUser['id'] !== $user['id']) {
            return response()->json([
                'message' => 'Den email eksisterer allerede i systemet og hører ikke til den bruger der nulstilles'
            ], 400);
        }
        $user->email = $request->validated()['email'];
        $user->password = Hash::make(Str::random(40));

        $user->save();

        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user->refresh()),
        ]);
    }
}
