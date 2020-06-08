<?php

namespace App\Http\Requests\API\Event;

use Illuminate\Foundation\Http\FormRequest;

class Show extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('view', [$this->event]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => 'required|date_format:d-m-Y H:i:s',
        ];
    }
}
