<?php

namespace App\Http\Requests\Api;

use Dingo\Api\Http\FormRequest as Request;

class FormRequest extends Request
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

}
