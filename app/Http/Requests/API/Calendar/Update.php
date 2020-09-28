<?php

namespace App\Http\Requests\API\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('update', $this->calendar);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'string',
            'colour' => 'required|string',
            'resources' => 'array',
            'resources.rooms' => 'bool',
            'resources.equipment' => 'bool',
        ];
    }
}
