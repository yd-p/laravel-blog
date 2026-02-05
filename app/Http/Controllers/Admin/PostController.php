<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * 显示文章列表
     */
    public function index(Request $request)
    {
        $query = Post::with(['category', 'author']);

        // 筛选条件
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $posts = $query->latest('created_at')->paginate(20);
        $categories = Category::enabled()->orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * 显示创建文章表单
     */
    public function create()
    {
        $categories = Category::enabled()->orderBy('sort', 'desc')->get();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * 存储新文章
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:250|unique:posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|string|max:255',
            'status' => 'required|in:1,2,3',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string',
        ]);

        // 自动生成slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // 确保slug唯一
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Post::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        // 设置作者
        $validated['author_id'] = auth()->id();

        // 如果状态为已发布但没有设置发布时间，则设置为当前时间
        if ($validated['status'] == Post::STATUS_PUBLISHED && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        Post::create($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', '文章创建成功！');
    }

    /**
     * 显示文章详情
     */
    public function show(Post $post)
    {
        $post->load(['category', 'author']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * 显示编辑文章表单
     */
    public function edit(Post $post)
    {
        $categories = Category::enabled()->orderBy('sort', 'desc')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * 更新文章
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'slug' => [
                'nullable',
                'string',
                'max:250',
                Rule::unique('posts', 'slug')->ignore($post->id)
            ],
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|string|max:255',
            'status' => 'required|in:1,2,3',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string',
        ]);

        // 自动生成slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // 如果状态改为已发布但没有设置发布时间，则设置为当前时间
        if ($validated['status'] == Post::STATUS_PUBLISHED && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')
            ->with('success', '文章更新成功！');
    }

    /**
     * 删除文章
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', '文章删除成功！');
    }

    /**
     * 发布文章
     */
    public function publish(Post $post)
    {
        $post->publish();

        return back()->with('success', '文章发布成功！');
    }

    /**
     * 撤回发布
     */
    public function unpublish(Post $post)
    {
        $post->unpublish();

        return back()->with('success', '文章撤回成功！');
    }

    /**
     * 批量操作
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete,draft',
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $posts = Post::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'publish':
                $posts->update([
                    'status' => Post::STATUS_PUBLISHED,
                    'published_at' => now()
                ]);
                $message = '批量发布成功！';
                break;
            case 'unpublish':
                $posts->update([
                    'status' => Post::STATUS_DRAFT,
                    'published_at' => null
                ]);
                $message = '批量撤回成功！';
                break;
            case 'draft':
                $posts->update(['status' => Post::STATUS_DRAFT]);
                $message = '批量设为草稿成功！';
                break;
            case 'delete':
                $posts->delete();
                $message = '批量删除成功！';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * 上传图片
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $image = $request->file('image');
        $filename = time() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('uploads/posts', $filename, 'public');

        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path)
        ]);
    }
}