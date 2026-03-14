<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class PluginMarketService
{
    // 远程市场 API，可在 config/plugin_market.php 中覆盖
    protected string $marketUrl;
    protected int $cacheTtl = 3600; // 1小时缓存

    public function __construct()
    {
        // 默认指向本地 Spring Boot 插件市场服务（yudao-boot-mini）
        $this->marketUrl = config('plugin_market.api_url', 'http://localhost:48080/plugin/info/open');
    }

    /**
     * 获取市场插件列表（带缓存）
     */
    public function getMarketPlugins(string $category = '', string $search = ''): array
    {
        $cacheKey = 'plugin_market_list_' . md5($category . $search);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($category, $search) {
            return $this->fetchFromRemote($category, $search);
        });
    }

    /**
     * 从远程 API 拉取插件列表
     * yudao API: GET /plugin/info/open/list?category=xxx
     * 返回格式: { code: 0, data: [...] }
     */
    protected function fetchFromRemote(string $category = '', string $search = ''): array
    {
        try {
            $params = array_filter(['category' => $category]);
            $response = Http::timeout(10)->get($this->marketUrl . '/list', $params);

            if ($response->successful() && $response->json('code') === 0) {
                $plugins = $response->json('data', []);
                // 搜索过滤（服务端暂不支持 search，客户端过滤）
                if ($search) {
                    $plugins = array_values(array_filter($plugins, fn($p) =>
                        Str::contains(strtolower(($p['name'] ?? '') . ($p['description'] ?? '')), strtolower($search))
                    ));
                }
                // 统一字段映射：yudao 用 pluginId，本地用 id
                return array_map(fn($p) => array_merge($p, ['id' => $p['pluginId'] ?? $p['id'] ?? '']), $plugins);
            }
        } catch (\Throwable $e) {
            logger()->warning('插件市场 API 请求失败: ' . $e->getMessage());
        }

        return $this->getFallbackPlugins($search, $category);
    }

    /**
     * 获取插件详情
     * yudao API: GET /plugin/info/open/get?pluginId=xxx
     */
    public function getPluginDetail(string $pluginId): ?array
    {
        $cacheKey = 'plugin_market_detail_' . $pluginId;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($pluginId) {
            try {
                $response = Http::timeout(10)->get($this->marketUrl . '/get', ['pluginId' => $pluginId]);
                if ($response->successful() && $response->json('code') === 0) {
                    $plugin = $response->json('data');
                    if ($plugin) {
                        return array_merge($plugin, ['id' => $plugin['pluginId'] ?? $plugin['id'] ?? '']);
                    }
                }
            } catch (\Throwable $e) {
                logger()->warning('获取插件详情失败: ' . $e->getMessage());
            }

            // 降级：从本地示例数据中查找
            $all = $this->getFallbackPlugins();
            foreach ($all as $plugin) {
                if (($plugin['id'] ?? '') === $pluginId) {
                    return $plugin;
                }
            }
            return null;
        });
    }

    /**
     * 获取所有分类
     * yudao API: GET /plugin/category/open/list
     */
    public function getCategories(): array
    {
        return Cache::remember('plugin_market_categories', $this->cacheTtl, function () {
            try {
                // 分类接口在不同路径
                $catUrl = Str::replaceLast('/info/open', '/category/open', $this->marketUrl);
                $response = Http::timeout(10)->get($catUrl . '/list');
                if ($response->successful() && $response->json('code') === 0) {
                    return array_map(fn($c) => [
                        'id'   => $c['code'] ?? $c['id'] ?? '',
                        'name' => $c['name'] ?? '',
                    ], $response->json('data', []));
                }
            } catch (\Throwable $e) {}

            return [
                ['id' => 'all',       'name' => '全部'],
                ['id' => 'content',   'name' => '内容管理'],
                ['id' => 'seo',       'name' => 'SEO'],
                ['id' => 'social',    'name' => '社交'],
                ['id' => 'ecommerce', 'name' => '电商'],
                ['id' => 'security',  'name' => '安全'],
                ['id' => 'analytics', 'name' => '统计分析'],
                ['id' => 'utility',   'name' => '工具'],
            ];
        });
    }

    /**
     * 从 URL 下载并安装插件
     */
    public function installFromUrl(string $downloadUrl, string $pluginFolder): array
    {
        $tempFile = sys_get_temp_dir() . '/plugin_' . Str::random(10) . '.zip';

        try {
            // 下载 ZIP
            $response = Http::timeout(120)->withOptions(['sink' => $tempFile])->get($downloadUrl);

            if (!$response->successful()) {
                return ['success' => false, 'message' => '下载失败，HTTP ' . $response->status()];
            }

            return $this->installFromZip($tempFile, $pluginFolder);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => '下载异常: ' . $e->getMessage()];
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * 从本地 ZIP 文件安装插件
     */
    public function installFromZip(string $zipPath, string $pluginFolder = ''): array
    {
        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'ZIP 文件不存在'];
        }

        $tempDir = sys_get_temp_dir() . '/plugin_extract_' . Str::random(10);

        try {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                return ['success' => false, 'message' => '无法打开 ZIP 文件'];
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // 找到插件根目录（含 plugin.json 或 composer.json）
            $pluginRoot = $this->findPluginRoot($tempDir);
            if (!$pluginRoot) {
                return ['success' => false, 'message' => '无效的插件结构，未找到 plugin.json'];
            }

            // 读取 plugin.json 获取 folder 名
            $manifestPath = $pluginRoot . '/plugin.json';
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                $pluginFolder = $manifest['folder'] ?? $pluginFolder ?: basename($pluginRoot);
            } else {
                $pluginFolder = $pluginFolder ?: basename($pluginRoot);
            }

            $targetPath = base_path('plugins/' . $pluginFolder);

            if (File::isDirectory($targetPath)) {
                return ['success' => false, 'message' => "插件 [{$pluginFolder}] 已存在，请先删除旧版本"];
            }

            File::copyDirectory($pluginRoot, $targetPath);

            return ['success' => true, 'message' => "插件 [{$pluginFolder}] 安装成功", 'folder' => $pluginFolder];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => '安装异常: ' . $e->getMessage()];
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /**
     * 查找插件根目录
     */
    protected function findPluginRoot(string $dir): ?string
    {
        if (file_exists($dir . '/plugin.json') || file_exists($dir . '/composer.json')) {
            return $dir;
        }
        foreach (File::directories($dir) as $sub) {
            if (file_exists($sub . '/plugin.json') || file_exists($sub . '/composer.json')) {
                return $sub;
            }
        }
        return null;
    }

    /**
     * 清除市场缓存
     */
    public function clearCache(): void
    {
        Cache::forget('plugin_market_categories');
        // 通配符清除需要用 tags 或手动管理，这里简单处理
        Cache::flush();
    }

    /**
     * 降级示例数据（API 不可用时展示）
     */
    protected function getFallbackPlugins(string $search = '', string $category = ''): array
    {
        $plugins = [
            [
                'id'          => 'post-plugin',
                'name'        => 'Post 文章系统',
                'folder'      => 'Post',
                'version'     => '1.0.0',
                'description' => '完整的文章发布系统，支持分类、标签、评论、Markdown 编辑。',
                'author'      => ['name' => 'LH Team', 'url' => ''],
                'category'    => 'content',
                'downloads'   => 1280,
                'rating'      => 4.8,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['文章', '博客', 'CMS'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v1.0.0\n- 初始版本发布",
            ],
            [
                'id'          => 'seo-toolkit',
                'name'        => 'SEO 工具包',
                'folder'      => 'SeoToolkit',
                'version'     => '2.1.0',
                'description' => '自动生成 sitemap、meta 标签管理、Open Graph 支持、结构化数据注入。',
                'author'      => ['name' => 'SEO Labs', 'url' => ''],
                'category'    => 'seo',
                'downloads'   => 3420,
                'rating'      => 4.6,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['SEO', 'Sitemap', 'Meta'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v2.1.0\n- 新增结构化数据支持",
            ],
            [
                'id'          => 'social-share',
                'name'        => '社交分享',
                'folder'      => 'SocialShare',
                'version'     => '1.3.2',
                'description' => '一键分享到微信、微博、Twitter、Facebook 等主流社交平台。',
                'author'      => ['name' => 'Share Team', 'url' => ''],
                'category'    => 'social',
                'downloads'   => 2100,
                'rating'      => 4.4,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['分享', '社交', '微信'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v1.3.2\n- 修复微信分享兼容性",
            ],
            [
                'id'          => 'analytics-pro',
                'name'        => '访问统计 Pro',
                'folder'      => 'AnalyticsPro',
                'version'     => '3.0.1',
                'description' => '实时访客统计、PV/UV 分析、热力图、来源追踪，内置仪表盘 Widget。',
                'author'      => ['name' => 'Analytics Co', 'url' => ''],
                'category'    => 'analytics',
                'downloads'   => 5600,
                'rating'      => 4.9,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['统计', '分析', 'PV'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v3.0.1\n- 性能优化",
            ],
            [
                'id'          => 'security-guard',
                'name'        => '安全防护',
                'folder'      => 'SecurityGuard',
                'version'     => '1.5.0',
                'description' => '登录防暴力破解、IP 黑名单、操作审计日志、双因素认证支持。',
                'author'      => ['name' => 'SecureTeam', 'url' => ''],
                'category'    => 'security',
                'downloads'   => 4200,
                'rating'      => 4.7,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['安全', '2FA', '审计'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v1.5.0\n- 新增双因素认证",
            ],
            [
                'id'          => 'media-gallery',
                'name'        => '媒体画廊',
                'folder'      => 'MediaGallery',
                'version'     => '2.0.0',
                'description' => '瀑布流图片画廊、视频播放器、文件管理器，支持 WebP 自动转换。',
                'author'      => ['name' => 'Media Labs', 'url' => ''],
                'category'    => 'utility',
                'downloads'   => 1890,
                'rating'      => 4.5,
                'cover'       => '',
                'screenshots' => [],
                'download_url'=> '',
                'homepage'    => '',
                'tags'        => ['媒体', '图片', '画廊'],
                'requires'    => '>=1.0.0',
                'changelog'   => "v2.0.0\n- 重构画廊组件",
            ],
        ];

        // 搜索过滤
        if ($search) {
            $plugins = array_filter($plugins, fn($p) =>
                Str::contains(strtolower($p['name'] . $p['description']), strtolower($search))
            );
        }

        // 分类过滤
        if ($category && $category !== 'all') {
            $plugins = array_filter($plugins, fn($p) => ($p['category'] ?? '') === $category);
        }

        return array_values($plugins);
    }
}
