<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminTagUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tag' => 'sometimes|min:2|max:50|unique:tags,tag,'.$this->id,
            'slug' => 'sometimes|min:2|max:50|unique:tags,slug,'.$this->id,
        ];
    }
}
