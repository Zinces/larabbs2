<?php

namespace App\Http\Requests\Api;


class WeappAuthorizationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
          'code.required'=>'缺失参数code'
        ];
    }
}
