<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;

class LinksController extends Controller
{
    public function index(Link $link)
    {
        $links = $link->getAllCached();
        return $this->success($links);
    }
}
