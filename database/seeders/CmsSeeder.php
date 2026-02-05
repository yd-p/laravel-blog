<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
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

        // 创建标签
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'color' => '#FF2D20', 'description' => 'Laravel框架相关', 'status' => 1],
            ['name' => 'PHP', 'slug' => 'php', 'color' => '#777BB4', 'description' => 'PHP编程语言', 'status' => 1],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'color' => '#4FC08D', 'description' => 'Vue.js前端框架', 'status' => 1],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'color' => '#F7DF1E', 'description' => 'JavaScript编程语言', 'status' => 1],
            ['name' => '数据库', 'slug' => 'database', 'color' => '#336791', 'description' => '数据库相关', 'status' => 1],
            ['name' => '前端', 'slug' => 'frontend', 'color' => '#61DAFB', 'description' => '前端开发', 'status' => 1],
            ['name' => '后端', 'slug' => 'backend', 'color' => '#68A063', 'description' => '后端开发', 'status' => 1],
            ['name' => '教程', 'slug' => 'tutorial', 'color' => '#FF6B6B', 'description' => '教程文章', 'status' => 1],
            ['name' => 'Docker', 'slug' => 'docker', 'color' => '#2496ED', 'description' => 'Docker容器技术', 'status' => 1],
            ['name' => 'MySQL', 'slug' => 'mysql', 'color' => '#4479A1', 'description' => 'MySQL数据库', 'status' => 1],
        ];

        $createdTags = [];
        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
            $createdTags[$tagData['slug']] = $tag;
        }

        // 创建示例文章
        $posts = [
            [
                'category_id' => $techCategory->id,
                'title' => 'Laravel CMS系统开发指南',
                'slug' => 'laravel-cms-development-guide',
                'excerpt' => '本文将详细介绍如何使用Laravel框架开发一个功能完整的CMS内容管理系统，包括后台管理、前端展示、API接口等功能。',
                'content' => "# Laravel CMS系统开发指南\n\n## 简介\n\nLaravel是一个优雅的PHP Web应用程序框架，它提供了丰富的功能和工具，使得开发CMS系统变得更加简单和高效。\n\n## 主要功能\n\n### 1. 用户管理\n- 用户注册和登录\n- 权限控制\n- 用户资料管理\n\n### 2. 内容管理\n- 文章发布和编辑\n- 分类管理\n- 标签系统\n\n### 3. 系统设置\n- 网站配置\n- SEO设置\n- 主题管理\n\n## 技术栈\n\n- **后端**: Laravel 10.x\n- **前端**: Bootstrap 5 + jQuery\n- **数据库**: MySQL 8.0\n- **缓存**: Redis\n\n## 开发步骤\n\n1. 环境搭建\n2. 数据库设计\n3. 模型创建\n4. 控制器开发\n5. 视图设计\n6. 路由配置\n7. 测试和部署\n\n## 总结\n\n通过本指南，您可以快速搭建一个功能完整的CMS系统。Laravel的强大功能和优雅语法让开发过程变得轻松愉快。",
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays(5),
                'view_count' => 156,
                'author_id' => $admin->id,
                'seo_title' => 'Laravel CMS系统开发完整指南 - 从零到一',
                'seo_keywords' => 'Laravel, CMS, PHP, 开发指南, 内容管理系统',
                'seo_description' => '详细介绍Laravel CMS系统开发的完整流程，包括环境搭建、数据库设计、功能实现等。',
            ],
            [
                'category_id' => $createdCategories[1]->id,
                'title' => '程序员的日常生活',
                'slug' => 'programmer-daily-life',
                'excerpt' => '作为一名程序员，每天的生活都充满了挑战和乐趣。从早晨的第一杯咖啡到深夜的最后一行代码，记录下这些美好的时光。',
                'content' => "# 程序员的日常生活\n\n## 早晨时光\n\n每天早上7点，闹钟准时响起。洗漱完毕后，第一件事就是泡一杯香浓的咖啡，然后打开电脑，查看昨晚的构建结果和邮件。\n\n## 工作时间\n\n### 上午\n- 9:00 - 站会，同步昨天的进度和今天的计划\n- 9:30 - 开始编码，处理优先级最高的任务\n- 11:00 - 代码审查，帮助同事解决技术问题\n\n### 下午\n- 14:00 - 午休后继续编码\n- 16:00 - 测试和调试\n- 17:30 - 整理文档，提交代码\n\n## 晚上时光\n\n下班后，有时会继续学习新技术，阅读技术博客，或者参与开源项目。偶尔也会和朋友聚餐，聊聊技术趋势和职业发展。\n\n## 周末安排\n\n周末通常会：\n- 整理一周的学习笔记\n- 写技术博客\n- 参加技术聚会\n- 放松娱乐，看电影或运动\n\n## 感悟\n\n程序员的生活虽然忙碌，但充满了创造的乐趣。每当解决一个复杂的问题，或者看到自己的代码在生产环境中稳定运行，那种成就感是无法言喻的。",
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays(3),
                'view_count' => 89,
                'author_id' => $admin->id,
            ],
            [
                'category_id' => $createdCategories[2]->id,
                'title' => 'Vue.js 3.0 学习笔记',
                'slug' => 'vuejs-3-learning-notes',
                'excerpt' => 'Vue.js 3.0 带来了许多新特性和改进，本文记录了学习过程中的重点知识和实践经验。',
                'content' => "# Vue.js 3.0 学习笔记\n\n## Composition API\n\nVue 3.0 最重要的新特性就是 Composition API，它提供了一种更灵活的方式来组织组件逻辑。\n\n### setup() 函数\n\n```javascript\nimport { ref, reactive, computed, onMounted } from 'vue'\n\nexport default {\n  setup() {\n    const count = ref(0)\n    const state = reactive({\n      name: 'Vue 3.0',\n      version: '3.0.0'\n    })\n    \n    const doubleCount = computed(() => count.value * 2)\n    \n    const increment = () => {\n      count.value++\n    }\n    \n    onMounted(() => {\n      console.log('组件已挂载')\n    })\n    \n    return {\n      count,\n      state,\n      doubleCount,\n      increment\n    }\n  }\n}\n```\n\n## 响应式系统\n\nVue 3.0 重写了响应式系统，使用 Proxy 替代了 Object.defineProperty。\n\n### ref 和 reactive\n\n- `ref`: 用于基本数据类型\n- `reactive`: 用于对象和数组\n\n## 性能提升\n\n- 更小的包体积\n- 更快的渲染速度\n- 更好的 Tree-shaking 支持\n\n## 总结\n\nVue 3.0 是一个重大的版本更新，虽然学习曲线有所增加，但带来的好处是显而易见的。推荐所有 Vue 开发者尽快升级到 3.0 版本。",
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subDays(1),
                'view_count' => 234,
                'author_id' => $admin->id,
                'seo_title' => 'Vue.js 3.0 完整学习笔记 - Composition API详解',
                'seo_keywords' => 'Vue.js, Vue 3.0, Composition API, 前端开发',
                'seo_description' => 'Vue.js 3.0 学习笔记，详细介绍 Composition API、响应式系统等新特性。',
            ],
            [
                'category_id' => $techCategory->id,
                'title' => 'Docker容器化部署实践',
                'slug' => 'docker-containerization-practice',
                'excerpt' => 'Docker作为现代应用部署的标准工具，本文分享在实际项目中使用Docker进行容器化部署的经验和最佳实践。',
                'content' => "# Docker容器化部署实践\n\n## Docker简介\n\nDocker是一个开源的容器化平台，它可以让开发者将应用程序及其依赖打包到一个轻量级、可移植的容器中。\n\n## 基础概念\n\n### 镜像 (Image)\n镜像是一个只读的模板，用来创建容器。\n\n### 容器 (Container)\n容器是镜像的运行实例。\n\n### Dockerfile\n用于构建镜像的文本文件。\n\n## 实践案例\n\n### Laravel应用容器化\n\n```dockerfile\nFROM php:8.2-fpm\n\n# 安装系统依赖\nRUN apt-get update && apt-get install -y \\\n    git \\\n    curl \\\n    libpng-dev \\\n    libonig-dev \\\n    libxml2-dev \\\n    zip \\\n    unzip\n\n# 安装PHP扩展\nRUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd\n\n# 安装Composer\nCOPY --from=composer:latest /usr/bin/composer /usr/bin/composer\n\n# 设置工作目录\nWORKDIR /var/www\n\n# 复制应用代码\nCOPY . /var/www\n\n# 安装依赖\nRUN composer install --no-dev --optimize-autoloader\n\n# 设置权限\nRUN chown -R www-data:www-data /var/www\n\nEXPOSE 9000\nCMD [\"php-fpm\"]\n```\n\n### docker-compose.yml\n\n```yaml\nversion: '3.8'\n\nservices:\n  app:\n    build: .\n    container_name: laravel-app\n    restart: unless-stopped\n    working_dir: /var/www\n    volumes:\n      - ./:/var/www\n    networks:\n      - laravel\n\n  nginx:\n    image: nginx:alpine\n    container_name: laravel-nginx\n    restart: unless-stopped\n    ports:\n      - \"80:80\"\n    volumes:\n      - ./:/var/www\n      - ./docker/nginx:/etc/nginx/conf.d\n    networks:\n      - laravel\n\n  mysql:\n    image: mysql:8.0\n    container_name: laravel-mysql\n    restart: unless-stopped\n    environment:\n      MYSQL_DATABASE: laravel\n      MYSQL_ROOT_PASSWORD: secret\n    volumes:\n      - mysql_data:/var/lib/mysql\n    networks:\n      - laravel\n\nvolumes:\n  mysql_data:\n\nnetworks:\n  laravel:\n    driver: bridge\n```\n\n## 部署流程\n\n1. 构建镜像\n2. 推送到镜像仓库\n3. 在生产服务器拉取镜像\n4. 启动容器\n\n## 最佳实践\n\n- 使用多阶段构建减小镜像体积\n- 合理使用缓存层\n- 设置健康检查\n- 使用非root用户运行容器\n- 定期更新基础镜像\n\n## 总结\n\nDocker容器化部署大大简化了应用的部署和运维工作，提高了开发效率和系统稳定性。",
                'status' => Post::STATUS_PUBLISHED,
                'published_at' => now()->subHours(12),
                'view_count' => 67,
                'author_id' => $admin->id,
            ],
            [
                'category_id' => $createdCategories[2]->id,
                'title' => 'MySQL性能优化总结',
                'slug' => 'mysql-performance-optimization',
                'excerpt' => '数据库性能优化是后端开发中的重要技能，本文总结了MySQL性能优化的常用方法和技巧。',
                'content' => "# MySQL性能优化总结\n\n## 索引优化\n\n### 1. 选择合适的索引类型\n- B-Tree索引：最常用的索引类型\n- Hash索引：适用于等值查询\n- 全文索引：用于文本搜索\n\n### 2. 索引设计原则\n- 为经常查询的列创建索引\n- 避免过多的索引\n- 考虑复合索引的顺序\n\n## 查询优化\n\n### 1. 使用EXPLAIN分析查询\n```sql\nEXPLAIN SELECT * FROM posts WHERE category_id = 1;\n```\n\n### 2. 避免全表扫描\n- 使用WHERE子句\n- 合理使用LIMIT\n- 避免SELECT *\n\n### 3. 优化JOIN查询\n- 使用适当的JOIN类型\n- 确保JOIN条件有索引\n- 控制JOIN的表数量\n\n## 配置优化\n\n### 1. 内存配置\n```ini\ninnodb_buffer_pool_size = 1G\nquery_cache_size = 256M\ntmp_table_size = 64M\n```\n\n### 2. 连接配置\n```ini\nmax_connections = 200\nconnect_timeout = 10\nwait_timeout = 28800\n```\n\n## 架构优化\n\n### 1. 读写分离\n- 主库处理写操作\n- 从库处理读操作\n- 使用中间件实现自动路由\n\n### 2. 分库分表\n- 垂直分库：按业务模块分离\n- 水平分表：按数据量分片\n\n### 3. 缓存策略\n- Redis缓存热点数据\n- 应用层缓存\n- 查询结果缓存\n\n## 监控和诊断\n\n### 1. 慢查询日志\n```ini\nslow_query_log = 1\nlong_query_time = 2\n```\n\n### 2. 性能监控工具\n- MySQL Workbench\n- Percona Toolkit\n- pt-query-digest\n\n## 总结\n\nMySQL性能优化是一个系统性工程，需要从多个维度进行考虑和实施。通过合理的索引设计、查询优化、配置调整和架构改进，可以显著提升数据库性能。",
                'status' => Post::STATUS_DRAFT,
                'published_at' => null,
                'view_count' => 0,
                'author_id' => $admin->id,
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::firstOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
            
            // 为文章添加标签
            if ($post->slug === 'laravel-cms-development-guide') {
                $post->tags()->sync([
                    $createdTags['laravel']->id,
                    $createdTags['php']->id,
                    $createdTags['backend']->id,
                    $createdTags['tutorial']->id,
                ]);
            } elseif ($post->slug === 'vuejs-3-learning-notes') {
                $post->tags()->sync([
                    $createdTags['vuejs']->id,
                    $createdTags['javascript']->id,
                    $createdTags['frontend']->id,
                    $createdTags['tutorial']->id,
                ]);
            } elseif ($post->slug === 'docker-containerization-practice') {
                $post->tags()->sync([
                    $createdTags['docker']->id,
                    $createdTags['backend']->id,
                    $createdTags['tutorial']->id,
                ]);
            } elseif ($post->slug === 'mysql-performance-optimization') {
                $post->tags()->sync([
                    $createdTags['mysql']->id,
                    $createdTags['database']->id,
                    $createdTags['backend']->id,
                ]);
            }
        }
        
        // 更新所有标签的文章数量
        foreach ($createdTags as $tag) {
            $tag->updatePostCount();
        }

        $this->command->info('CMS示例数据创建完成！');
        $this->command->info('管理员账号: admin@example.com');
        $this->command->info('管理员密码: password');
    }
}