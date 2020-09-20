<?php

namespace App\Http\Requests\API\Comment;

use Illuminate\Foundation\Http\FormRequest;

class File extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('addFile', [$this->comment]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'files' => 'array',
            'files.*' => 'file|required',
            'file_deletions' => 'array',
            'file_deletions.*' => 'string|required',
        ];
    }
}
