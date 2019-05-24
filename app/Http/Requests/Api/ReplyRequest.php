<?php

namespace App\Http\Requests\Api;


class ReplyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()){
            case 'POST':
                return [
                    'content' => 'required|min:3'
                ];
                break;
        }
    }

    public function messages()
    {
        return [
          'content.required' => '内容不能为空',
          'content.min' => '内容过短'
        ];
    }
}
