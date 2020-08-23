<?php

namespace App\Http\Requests\API\Forum;

use App\Models\Forum;
use Illuminate\Foundation\Http\FormRequest;

class Priorities extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('updatePriorities', Forum::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*.id' => 'string|required',
            '*.priority' => 'integer|required',
        ];
    }
}
