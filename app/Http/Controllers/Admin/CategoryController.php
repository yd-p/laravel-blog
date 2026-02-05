<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * 显示分类列表
     */
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount('posts')
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * 显示创建分类表单
     */
    public function create()
    {
        $parentCategories = Category::enabled()
            ->orderBy('sort', 'desc')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * 存储新分类
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:150|unique:categories,slug',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        // 自动生成slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // 确保slug唯一
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', '分类创建成功！');
    }

    /**
     * 显示分类详情
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'posts' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * 显示编辑分类表单
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::enabled()
            ->where('id', '!=', $category->id)
            ->orderBy('sort', 'desc')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * 更新分类
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('categories', 'slug')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:200',
            'seo_keywords' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        // 防止设置自己为父分类
        if ($validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => '不能设置自己为父分类']);
        }

        // 自动生成slug
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', '分类更新成功！');
    }

    /**
     * 删除分类
     */
    public function destroy(Category $category)
    {
        // 检查是否有子分类
        if ($category->children()->count() > 0) {
            return back()->withErrors(['error' => '该分类下还有子分类，无法删除']);
        }

        // 检查是否有文章
        if ($category->posts()->count() > 0) {
            return back()->withErrors(['error' => '该分类下还有文章，无法删除']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', '分类删除成功！');
    }

    /**
     * 批量操作
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:enable,disable,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        $categories = Category::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'enable':
                $categories->update(['status' => 1]);
                $message = '批量启用成功！';
                break;
            case 'disable':
                $categories->update(['status' => 0]);
                $message = '批量禁用成功！';
                break;
            case 'delete':
                // 检查是否可以删除
                foreach ($categories->get() as $category) {
                    if ($category->children()->count() > 0 || $category->posts()->count() > 0) {
                        return back()->withErrors(['error' => '部分分类下还有子分类或文章，无法删除']);
                    }
                }
                $categories->delete();
                $message = '批量删除成功！';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * 获取分类树（AJAX）
     */
    public function tree()
    {
        $categories = Category::getTree();
        return response()->json($categories);
    }
}