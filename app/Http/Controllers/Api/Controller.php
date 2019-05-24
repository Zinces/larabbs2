<?php

namespace App\Http\Controllers\Api;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller extends BaseController
{
    use Helpers;

    /**
     * 返回成功
     */
    public function success($data,$page = null)
    {
        $result['message'] = 'success';
        $result['code'] = (int)80000;
        $result['data'] = $data ? $data : [];
        if($page){
            $result['page'] = $page;
        }
        return $result;
    }

    /**
     * 返回错误
     * @param string $message
     */
    public function error($message)
    {
        $result['message'] = $message;
        $result['code'] = (int)80001;
        return $result;
    }

    public function errorResponse($statusCode, $message=null, $code=0)
    {
        throw new HttpException($statusCode, $message, null, [], $code);
    }
}
