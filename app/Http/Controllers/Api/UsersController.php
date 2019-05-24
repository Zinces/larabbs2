<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\Image;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    public function activedIndex(User $user)
    {
        $activedSum = $user->getActiveUsers();

        return $this->success($activedSum);
    }

    public function store(UserRequest $request)
    {
        $verification_key = Cache::get($request->verification_key);
        if (!$verification_key){
            return $this->response->error('验证码已失效',422);
        }
        if (!hash_equals($verification_key['code'],$request->verification_code)){
            return $this->response->error('验证码错误',422);
        }
        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password)
        ]);
        Cache::forget($request->verification_key);
        return $this->response->created();
    }

    public function me()
    {
        return $this->response->item($this->user(),new UserTransformer);
    }

    public function update(UserRequest $request)
    {
        $user = $this->user();
        $attributes = $request->only(['name', 'email', 'introduction','registration_id']);
        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);

            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return $this->response->item($user, new UserTransformer());
    }
}
