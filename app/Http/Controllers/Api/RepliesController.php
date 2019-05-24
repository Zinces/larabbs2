<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ReplyRequest;
use App\Models\Reply;
use App\Models\Topic;
use App\Models\User;
use App\Transformers\ReplyTransformer;
use Dingo\Api\Transformer\Factory;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function index(Topic $topic,Request $request)
    {
        //不用dingo自带的预加载,自己用预加载
        app(Factory::class)->disableEagerLoading();
        $replies = $topic->Replies()->paginate(20);
        if ($request->include) {
            $replies->load($request->include);
        }
        return $this->response->paginator($replies,new ReplyTransformer());
    }

    public function userIndex(User $user)
    {
        $replies = $user->replies()->paginate(10);
        return $this->response->paginator($replies,new ReplyTransformer());
    }


    public function store(ReplyRequest $request,Reply $reply, Topic $topic)
    {
        $reply->content = $request->input('content');
        $reply->topic()->associate($topic);
        $reply->user()->associate($this->user());
        $reply->save();
        return $this->response->item($reply,new ReplyTransformer())->setStatusCode(201);
    }

    public function destroy(Topic $topic, Reply $reply)
    {
        if ($reply->topic_id != $topic->id){
            return $this->response->error('不要搞事',201);
        }
        $this->authorize('destroy',$reply);
        $reply->delete();
        return $this->response->noContent();
    }
}
