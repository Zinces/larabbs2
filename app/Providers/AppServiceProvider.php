<?php

namespace App\Providers;

use App\Models\Link;
use App\Observers\LinkObserver;
use Carbon\Carbon;
use Dingo\Api\Facade\API;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppServiceProvider extends ServiceProvider
{
    /**ValidationHttpException
     * Register any application services.
     *
     * @return void Unprocessable Entity
     */
    public function register()
    {
        if (app()->isLocal()){
            $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        }
        API::error(function  (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException  $exception)  {
            throw new HttpException(404,  '404 Not Found',null,[],80001);
        });
        API::error(function (\Illuminate\Auth\Access\AuthorizationException $exception) {
            throw new HttpException(403,  '没有权限',null,[],80001);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
	{
		\App\Models\User::observe(\App\Observers\UserObserver::class);
		\App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
		\App\Models\Topic::observe(\App\Observers\TopicObserver::class);
		Link::observe(LinkObserver::class);

        Carbon::setLocale('zh');
    }
}
