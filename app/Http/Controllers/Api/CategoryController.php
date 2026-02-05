<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * 获取分类列表
     */
    public function index(Request $request)
    {
        $query = Category::enabled()->with(['children' => function ($query) {
            $query->enabled()->ordered();
        }]);

        // 是否只获取顶级分类
        if ($request->boolean('root_only')) {
            $query->root();
        }

        // 是否包含文章数量
        if ($request->boolean('with_posts_count')) {
            $query->withCount(['publishedPosts']);
        }

        $categories = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => '获取分类列表成功'
        ]);
    }

    /**
     * 获取分类树
     */
    public function tree()
    {
        $categories = Category::getTree();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => '获取分类树成功'
        ]);
    }

    /**
     * 获取分类详情
     */
    public function show($slug)
    {
        $category = Category::enabled()
            ->where('slug', $slug)
            ->with(['parent', 'children' => function ($query) {
                $query->enabled()->ordered();
            }])
            ->withCount(['publishedPosts'])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => '分类不存在'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => '获取分类详情成功'
        ]);
    }

    /**
     * 获取分类下的文章
     */
    public function posts($slug, Request $request)
    {
        $category = Category::enabled()->where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => '分类不存在'
            ], 404);
        }

        $query = $category->publishedPosts()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id']);

        // 排序
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            default:
                $query->latest();
        }

        $posts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'posts' => $posts
            ],
            'message' => '获取分类文章成功'
        ]);
    }
}