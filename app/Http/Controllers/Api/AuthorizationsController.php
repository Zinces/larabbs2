<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Http\Requests\Api\WeappAuthorizationRequest;
use App\Traits\PassportToken;
use League\OAuth2\Server\Exception\OAuthServerException;
use Zend\Diactoros\Response as Psr7Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationsController extends Controller
{
    use PassportToken;
    public function socialStore(SocialAuthorizationRequest $request, $type)
    {
        if (!in_array($type,['weixin'])) return $this->response->errorBadRequest();

        $driver = Socialite::driver($type);

        try{
            if ($code = $request->code){
                $response = $driver->getAccessTokenResponse($code);
                $token = array_get($response,'access_token');
            }else{
                $token = $request->access_token;
                if ($type == 'weixin'){
                    $driver->setOpenId($request->openid);
                }
            }
            $oauthUser = $driver->userFromToken($token);
        }catch (\Exception $exception){
            return $this->response->errorUnauthorized('参数错误');
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }
                break;
        }
        //jwt
        $token = Auth::guard('api')->fromUser($user);
        return $this->respondWithToken($token)->setStatusCode(201);
        //passport
//        $result = $this->getBearerTokenByUser($user, '1', false);
//        return $this->response->array($result)->setStatusCode(201);
    }
    //jwt
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;
        filter_var($username,FILTER_VALIDATE_EMAIL) ? $credentials['email'] = $username : $credentials['phone'] = $username;
        $credentials['password'] = $request->password;
        if (!$token = Auth::guard('api')->attempt($credentials)){
            return $this->response->errorUnauthorized('用户名或者密码错误');
        }
        return $this->respondWithToken($token)->setStatusCode(201);
    }

    //passport 的密码授权模式 OAuthServerException
//    public function store(AuthorizationServer $server, ServerRequestInterface $serverRequest)
//    {
//        try {
//            return $server->respondToAccessTokenRequest($serverRequest, new Psr7Response)->withStatus(201);
//        } catch(OAuthServerException $e) {
//            return $this->response->errorUnauthorized($e->getMessage());
//        }
//    }

    //jwt 刷新
    public function update()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    //jwt
    public function destroy()
    {
        Auth::guard('api')->logout();
        return $this->response->noContent();
    }

    // passport
//    public function destroy()
//    {
//        if (!empty($this->user())) {
//            $this->user()->token()->revoke();
//            return $this->response->noContent();
//        } else {
//            return $this->response->errorUnauthorized('The token is invalid.');
//        }
//    }

    public function weappStore(WeappAuthorizationRequest $request)
    {
        $code = $request->code;
        // 根据 code 获取微信 openid 和 session_keyminiProgram
        $miniProgram = \EasyWeChat::miniProgram();

        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])){
            return $this->response->errorUnauthorized('code不正确或者已过期');
        }
        // 找到 openid 对应的用户
        $user = User::where('weapp_openid', $data['openid'])->first();

        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户则需要提交用户名密码进行用户绑定
        if (!$user) {
            // 如果未提交用户名密码，403 错误提示
            if (!$request->username) {
                return $this->response->errorForbidden('用户不存在');
            }

            $username = $request->username;

            // 用户名可以是邮箱或电话
            filter_var($username, FILTER_VALIDATE_EMAIL) ?
                $credentials['email'] = $username :
                $credentials['phone'] = $username;

            $credentials['password'] = $request->password;

            // 验证用户名和密码是否正确
            if (!Auth::guard('api')->once($credentials)) {
                return $this->response->errorUnauthorized('用户名或密码错误');
            }

            // 获取对应的用户
            $user = Auth::guard('api')->getUser();
            $attributes['weapp_openid'] = $data['openid'];
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        $token = Auth::guard('api')->fromUser($user);

        return $this->respondWithToken($token)->setStatusCode(201);

    }

    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
