<?php

namespace App\Http\Controllers\Api;


use App\Transformers\CategoryTransformer;
use App\Models\Category;
use Carbon\Carbon;

class CategoriesController extends Controller
{
    public function index()
    {
        return $this->response->collection(Category::all(),new CategoryTransformer());
    }
}
