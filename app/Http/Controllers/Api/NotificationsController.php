<?php

namespace App\Http\Controllers\Api;

use App\Transformers\NotificationTransformer;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications = $this->user->notifications()->paginate(10);

        return $this->response->paginator($notifications,new NotificationTransformer());
    }

    public function stats()
    {
        return $this->response->array([
            'unread_count'=>$this->user()->notification_count
        ]);
    }

    public function read(DatabaseNotification $notification)
    {
        $notification->id ? $this->user->markRead($notification) : $this->user->markRead();

        return $this->response->noContent();
    }
}
