<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * 首页
     */
    public function index()
    {
        // 最新文章
        $latestPosts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->latest()
            ->take(6)
            ->get();

        // 热门文章
        $popularPosts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->popular()
            ->take(5)
            ->get();

        // 分类
        $categories = Category::enabled()
            ->withCount(['publishedPosts'])
            ->ordered()
            ->take(8)
            ->get();

        // return view('web.home', compact('latestPosts', 'popularPosts', 'categories'));
    }

    /**
     * 文章列表页
     */
    public function posts(Request $request)
    {
        $query = Post::published()->with(['author:id,name', 'category:id,name,slug']);

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

        $posts = $query->paginate(12);
        $categories = Category::enabled()->ordered()->get();

        // return view('web.posts.index', compact('posts', 'categories'));
    }

    /**
     * 文章详情页
     */
    public function post($slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['author:id,name', 'category:id,name,slug'])
            ->first();

        if (!$post) {
            abort(404, '文章不存在或未发布');
        }

        // 增加阅读量
        $post->incrementViewCount();

        // 获取相关文章
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()
            ->take(5)
            ->get();

        // 上一篇和下一篇
        $prevPost = Post::published()
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->first();

        $nextPost = Post::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->first();

        // return view('web.posts.show', compact('post', 'relatedPosts', 'prevPost', 'nextPost'));
    }

    /**
     * 分类页面
     */
    public function category($slug, Request $request)
    {
        $category = Category::enabled()
            ->where('slug', $slug)
            ->with(['children' => function ($query) {
                $query->enabled()->ordered();
            }])
            ->first();

        if (!$category) {
            abort(404, '分类不存在');
        }

        $query = $category->publishedPosts()->with(['author:id,name']);

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

        $posts = $query->paginate(12);

        // return view('web.categories.show', compact('category', 'posts'));
    }

    /**
     * 搜索页面
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $keyword = $request->q;
        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->search($keyword)
            ->latest()
            ->paginate(12);

        // return view('web.search', compact('posts', 'keyword'));
    }

    /**
     * 归档页面
     */
    public function archive()
    {
        $archives = Post::published()
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(published_at), MONTH(published_at)')
            ->orderByRaw('YEAR(published_at) DESC, MONTH(published_at) DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'count' => $item->count,
                    'date' => sprintf('%04d-%02d', $item->year, $item->month)
                ];
            });

        // return view('web.archive', compact('archives'));
    }

    /**
     * 按日期归档
     */
    public function archiveByDate($year, $month, Request $request)
    {
        $posts = Post::published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->latest()
            ->paginate(12);

        $date = sprintf('%04d年%02d月', $year, $month);

        // return view('web.archive-date', compact('posts', 'date', 'year', 'month'));
    }
}