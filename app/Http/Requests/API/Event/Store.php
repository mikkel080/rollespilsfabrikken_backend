<?php

namespace App\Http\Requests\API\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Store extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create',[Event::class, $this->calendar]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'         => 'required|string',
            'description'   => 'required|string',
            'recurrence'    => Rule::in(['daily', 'weekly', 'monthly']),
            'start'         => 'required|date_format:d-m-Y H:i:s',
            'end'           => 'required|date_format:d-m-Y H:i:s'
        ];
    }
}
