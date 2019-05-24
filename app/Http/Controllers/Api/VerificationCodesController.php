<?php

namespace App\Http\Controllers\Api;



use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Support\Facades\Cache;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request)
    {
        $captchaData = Cache::get($request->captcha_key);
        if (!$captchaData){
            return $this->response->error('图形验证码失效',422);
        }
        if (!hash_equals($captchaData['code'],$request->captcha_code)){
            Cache::forget($request->captcha_key);
            return $this->response->error('图形验证码错误',422);
        }

        $code = str_pad(mt_rand(1,9999),4,0,STR_PAD_LEFT);
        $easySms = app('easysms');
        if (!app()->environment('production')) {
            $code = '1234';
        }else{
            try{
                $easySms->send($captchaData['phone'],[
                    'template' => env('ALIYUN_TEMPLATE'),
                    'data' => [
                        'name' => $code,
                        'Bird' => $code,
                    ],
                ]);
            }catch (\Exception $exception){
                $exception->getMessage();
                return $this->response->array(['message'=>'短信发送失败']);
            }
        }

        $key = 'verificationCode_'.str_random(15);
        $expiredAt = now()->addMinutes(10);
        Cache::put($key,['phone'=>$captchaData['phone'],'code'=>$code],$expiredAt);

        return $this->response->array([
            'expiredKey' => $key,
            'expiredAt' => $expiredAt->toDateTimeString()
        ])->setStatusCode(201);
    }

}
