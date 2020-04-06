<?php

namespace App\Http\Requests\API\Auth\Permission;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;

class IndexForum extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('viewAnyForum', Permission::class);
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
