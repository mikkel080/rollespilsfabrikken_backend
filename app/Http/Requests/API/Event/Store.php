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
            'description'   => 'nullable|string',
            'start'         => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'end'           => 'required|date_format:Y-m-d\TH:i:s.v\Z',
            'recurring'     => 'required|boolean',
            'recurrence'    => 'required_if:recurring,true',
            'recurrence.type'    => 'required_if:recurring,true|in:daily,weekly,monthly,yearly',
            //'recurrence.repeat_interval' => 'required_if:recurring,true|integer', // TODO: Find a better way
            'recurrence.end' => 'date_format:Y-m-d\TH:i:s.v\Z',
        ];
    }
}
