<?php

namespace Plugins\Post\Http\Controllers;

use App\Hooks\Facades\Hook;
use Illuminate\Routing\Controller;
use Plugins\Post\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        // 执行钩子
        $result = Hook::execute('test', 'Hello World');

        if ($result->isSuccessful()) {
            $data = $result->getFirstResult();
            dump($data);
        }
        // return 123;
    }
}
