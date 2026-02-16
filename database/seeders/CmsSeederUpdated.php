<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Enums\PostStatus;
use App\Enums\TagStatus;
use Illuminate\Support\Str;

class CmsSeederUpdated extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建管理员用户
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理员',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // 创建分类
        $categories = [
            [
                'name' => '技术分享',
                'slug' => 'tech',
                'description' => '分享各种技术文章和教程',
                'sort' => 100,
                'status' => 1,
            ],
            [
                'name' => '生活随笔',
                'slug' => 'life',
                'description' => '记录生活中的点点滴滴',
                'sort' => 90,
                'status' => 1,
            ],
            [
                'name' => '学习笔记',
                'slug' => 'study',
                'description' => '学习过程中的心得体会',
                'sort' => 80,
                'status' => 1,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
            $createdCategories[] = $category;
        }

        // 为技术分享创建子分类
        $techCategory = $createdCategories[0];
        $subCategories = [
            [
                'parent_id' => $techCategory->id,
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'Laravel框架相关文章',
                'sort' => 50,
                'status' => 1,
            ],
            [
                'parent_id' => $techCategory->id,
                'name' => 'Vue.js',
                'slug' => 'vuejs',
                'description' => 'Vue.js前端框架文章',
                'sort' => 40,
                'status' => 1,
            ],
        ];

        foreach ($subCategories as $subCategoryData) {
            Category::firstOrCreate(
                ['slug' => $subCategoryData['slug']],
                $subCategoryData
            );
        }

        // 创建标签 - 使用枚举
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'color' => '#FF2D20', 'description' => 'Laravel框架相关', 'status' => TagStatus::ENABLED->value],
            ['name' => 'PHP', 'slug' => 'php', 'color' => '#777BB4', 'description' => 'PHP编程语言', 'status' => TagStatus::ENABLED->value],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'color' => '#4FC08D', 'description' => 'Vue.js前端框架', 'status' => TagStatus::ENABLED->value],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'color' => '#F7DF1E', 'description' => 'JavaScript编程语言', 'status' => TagStatus::ENABLED->value],
            ['name' => '数据库', 'slug' => 'database', 'color' => '#336791', 'description' => '数据库相关', 'status' => TagStatus::ENABLED->value],
            ['name' => '前端', 'slug' => 'frontend', 'color' => '#61DAFB', 'description' => '前端开发', 'status' => TagStatus::ENABLED->value],
            ['name' => '后端', 'slug' => 'backend', 'color' => '#68A063', 'description' => '后端开发', 'status' => TagStatus::ENABLED->value],
            ['name' => '教程', 'slug' => 'tutorial', 'color' => '#FF6B6B', 'description' => '教程文章', 'status' => TagStatus::ENABLED->value],
            ['name' => 'Docker', 'slug' => 'docker', 'color' => '#2496ED', 'description' => 'Docker容器技术', 'status' => TagStatus::ENABLED->value],
            ['name' => 'MySQL', 'slug' => 'mysql', 'color' => '#4479A1', 'description' => 'MySQL数据库', 'status' => TagStatus::ENABLED->value],
        ];

        $createdTags = [];
        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
            $createdTags[$tagData['slug']] = $tag;
        }

        // 创建示例文章 - 使用枚举
        $posts = [
            [
                'category_id' => $techCategory->id,
                'title' => 'Laravel CMS系统开发指南',
                'slug' => 'laravel-cms-development-guide',
                'excerpt' => '本文将详细介绍如何使用Laravel框架开发一个功能完整的CMS内容管理系统，包括后台管理、前端展示、API接口等功能。',
                'content' => "# Laravel CMS系统开发指南\n\n## 简介\n\nLaravel是一个优雅的PHP Web应用程序框架...",
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now()->subDays(5),
                'view_count' => 156,
                'author_id' => $admin->id,
                'seo_title' => 'Laravel CMS系统开发完整指南 - 从零到一',
                'seo_keywords' => 'Laravel, CMS, PHP, 开发指南, 内容管理系统',
                'seo_description' => '详细介绍Laravel CMS系统开发的完整流程，包括环境搭建、数据库设计、功能实现等。',
                'tags' => ['laravel', 'php', 'backend', 'tutorial'],
            ],
            [
                'category_id' => $createdCategories[1]->id,
                'title' => '程序员的日常生活',
                'slug' => 'programmer-daily-life',
                'excerpt' => '作为一名程序员，每天的生活都充满了挑战和乐趣。',
                'content' => "# 程序员的日常生活\n\n## 早晨时光...",
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now()->subDays(3),
                'view_count' => 89,
                'author_id' => $admin->id,
                'tags' => [],
            ],
            [
                'category_id' => $createdCategories[2]->id,
                'title' => 'Vue.js 3.0 学习笔记',
                'slug' => 'vuejs-3-learning-notes',
                'excerpt' => 'Vue.js 3.0 带来了许多新特性和改进。',
                'content' => "# Vue.js 3.0 学习笔记\n\n## Composition API...",
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now()->subDays(1),
                'view_count' => 234,
                'author_id' => $admin->id,
                'seo_title' => 'Vue.js 3.0 完整学习笔记 - Composition API详解',
                'seo_keywords' => 'Vue.js, Vue 3.0, Composition API, 前端开发',
                'seo_description' => 'Vue.js 3.0 学习笔记，详细介绍 Composition API、响应式系统等新特性。',
                'tags' => ['vuejs', 'javascript', 'frontend', 'tutorial'],
            ],
            [
                'category_id' => $techCategory->id,
                'title' => 'Docker容器化部署实践',
                'slug' => 'docker-containerization-practice',
                'excerpt' => 'Docker作为现代应用部署的标准工具。',
                'content' => "# Docker容器化部署实践\n\n## Docker简介...",
                'status' => PostStatus::PUBLISHED->value,
                'published_at' => now()->subHours(12),
                'view_count' => 67,
                'author_id' => $admin->id,
                'tags' => ['docker', 'backend', 'tutorial'],
            ],
            [
                'category_id' => $createdCategories[2]->id,
                'title' => 'MySQL性能优化总结',
                'slug' => 'mysql-performance-optimization',
                'excerpt' => '数据库性能优化是后端开发中的重要技能。',
                'content' => "# MySQL性能优化总结\n\n## 索引优化...",
                'status' => PostStatus::DRAFT->value,
                'published_at' => null,
                'view_count' => 0,
                'author_id' => $admin->id,
                'tags' => ['mysql', 'database', 'backend'],
            ],
        ];

        foreach ($posts as $postData) {
            $tags = $postData['tags'] ?? [];
            unset($postData['tags']);
            
            $post = Post::firstOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );

            // 关联标签
            if (!empty($tags)) {
                $tagIds = [];
                foreach ($tags as $tagSlug) {
                    if (isset($createdTags[$tagSlug])) {
                        $tagIds[] = $createdTags[$tagSlug]->id;
                    }
                }
                $post->tags()->sync($tagIds);
                
                // 更新标签的文章数量
                foreach ($tagIds as $tagId) {
                    $tag = Tag::find($tagId);
                    if ($tag) {
                        $tag->updatePostCount();
                    }
                }
            }
        }

        $this->command->info('CMS示例数据创建成功！（使用枚举）');
    }
}
