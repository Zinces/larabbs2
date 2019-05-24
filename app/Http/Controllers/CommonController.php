<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommonController extends Controller
{
    public function topMenu(View $view)
    {
        //获取当前登录用户的菜单数组

        $result = Cache::rememberForever('top_menu', function() {
            return DB::table('categories')->select('id','name')->orderBy('id')->get();
        });

        $view->with(['result'=>$result]);
    }
}
