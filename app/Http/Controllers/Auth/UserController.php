<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\LoggedInUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\API\Auth\User\UpdateUsername;
use App\Http\Requests\API\Auth\User\Destroy;
use App\Http\Requests\API\Auth\User\DestroySelf;

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
}
