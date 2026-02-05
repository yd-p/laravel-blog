<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 获取文章列表
     */
    public function index(Request $request)
    {
        $query = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id']);

        // 分类筛选
        if ($request->filled('category')) {
            $category = Category::enabled()->where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 搜索
        if ($request->filled('search')) {
            $query->search($request->search);
        }

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
            'data' => $posts,
            'message' => '获取文章列表成功'
        ]);
    }

    /**
     * 获取文章详情
     */
    public function show($slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['author:id,name', 'category:id,name,slug'])
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => '文章不存在或未发布'
            ], 404);
        }

        // 增加阅读量
        $post->incrementViewCount();

        // 获取相关文章
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->select(['id', 'title', 'slug', 'thumbnail', 'published_at'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'post' => $post,
                'related_posts' => $relatedPosts
            ],
            'message' => '获取文章详情成功'
        ]);
    }

    /**
     * 获取热门文章
     */
    public function popular(Request $request)
    {
        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id'])
            ->popular()
            ->take($request->get('limit', 10))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => '获取热门文章成功'
        ]);
    }

    /**
     * 获取最新文章
     */
    public function latest(Request $request)
    {
        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id'])
            ->latest()
            ->take($request->get('limit', 10))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => '获取最新文章成功'
        ]);
    }

    /**
     * 搜索文章
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id'])
            ->search($request->q)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => '搜索完成'
        ]);
    }

    /**
     * 获取文章归档
     */
    public function archive(Request $request)
    {
        $query = Post::published()
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(published_at), MONTH(published_at)')
            ->orderByRaw('YEAR(published_at) DESC, MONTH(published_at) DESC');

        $archives = $query->get()->map(function ($item) {
            return [
                'year' => $item->year,
                'month' => $item->month,
                'count' => $item->count,
                'date' => sprintf('%04d-%02d', $item->year, $item->month)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $archives,
            'message' => '获取文章归档成功'
        ]);
    }

    /**
     * 根据年月获取文章
     */
    public function archiveByDate($year, $month, Request $request)
    {
        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'view_count', 'author_id'])
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => "获取 {$year}年{$month}月 文章成功"
        ]);
    }
}