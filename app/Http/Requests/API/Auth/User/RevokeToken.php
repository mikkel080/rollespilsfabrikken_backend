<?php

namespace App\Http\Requests\API\Auth\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RevokeToken extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('revokeToken', [User::class, $this->token]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
