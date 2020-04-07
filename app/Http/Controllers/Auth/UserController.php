<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\LoggedInUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\API\Auth\User\UpdateUsername;

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
}
