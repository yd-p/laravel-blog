<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_keywords',
        'seo_description',
        'sort',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 获取父分类
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * 获取子分类
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort', 'desc');
    }

    /**
     * 获取所有子分类（递归）
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * 获取分类下的文章
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * 获取已发布的文章
     */
    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', 2)->whereNotNull('published_at');
    }

    /**
     * 获取分类路径
     */
    public function getPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * 检查是否为顶级分类
     */
    public function isRoot(): bool
    {
        return $this->parent_id === 0;
    }

    /**
     * 检查是否有子分类
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * 获取启用的分类
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 获取顶级分类
     */
    public function scopeRoot($query)
    {
        return $query->where('parent_id', 0);
    }

    /**
     * 按排序排列
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'desc')->orderBy('id', 'asc');
    }

    /**
     * 获取分类树
     */
    public static function getTree(): array
    {
        $categories = self::enabled()->ordered()->get();
        return self::buildTree($categories);
    }

    /**
     * 构建分类树
     */
    private static function buildTree($categories, $parentId = 0): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->children_tree = self::buildTree($categories, $category->id);
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
}