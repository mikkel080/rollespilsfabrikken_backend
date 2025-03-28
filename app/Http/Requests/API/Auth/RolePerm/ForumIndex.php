<?php

namespace App\Http\Requests\API\Auth\RolePerm;

use App\Models\RolePerm;
use Illuminate\Foundation\Http\FormRequest;

class ForumIndex extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('viewAnyForum', RolePerm::class);
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
