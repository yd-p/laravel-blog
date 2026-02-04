<?php

namespace App\Hooks\Templates;

use App\Hooks\AbstractHook;
use Illuminate\Support\Facades\Cache;

/**
 * 缓存感知钩子模板
 * 
 * 支持缓存的钩子，可以缓存处理结果以提高性能
 * 
 * @hook cache.aware.hook
 * @priority 10
 * @group cache
 */
class CacheAwareHookTemplate extends AbstractHook
{
    protected string $description = '缓存感知钩子模板';
    protected int $priority = 10;

    // 缓存配置
    protected array $cacheConfig = [
        'enabled' => true,           // 是否启用缓存
        'ttl' => 3600,              // 缓存时间（秒）
        'prefix' => 'hook_cache_',  // 缓存键前缀
        'tags' => [],               // 缓存标签
        'invalidate_on_error' => true, // 错误时是否清除缓存
    ];

    /**
     * 处理缓存感知钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        [$key, $data, $options] = $this->extractArgs($args);

        // 应用缓存配置
        $this->applyCacheConfig($options);

        // 生成缓存键
        $cacheKey = $this->generateCacheKey($key, $data);

        // 尝试从缓存获取结果
        if ($this->cacheConfig['enabled']) {
            $cachedResult = $this->getCachedResult($cacheKey);
            if ($cachedResult !== null) {
                return $this->formatCachedResult($cachedResult);
            }
        }

        try {
            // 执行实际处理
            $result = $this->processData($key, $data, $options);

            // 缓存结果
            if ($this->cacheConfig['enabled']) {
                $this->cacheResult($cacheKey, $result);
            }

            return $this->formatResult($result, false);

        } catch (\Exception $e) {
            // 错误时清除缓存
            if ($this->cacheConfig['enabled'] && $this->cacheConfig['invalidate_on_error']) {
                $this->invalidateCache($cacheKey);
            }

            throw $e;
        }
    }

    /**
     * 提取参数
     */
    protected function extractArgs(array $args): array
    {
        $key = $args[0] ?? 'default';
        $data = $args[1] ?? null;
        $options = $args[2] ?? [];

        return [$key, $data, $options];
    }

    /**
     * 应用缓存配置
     */
    protected function applyCacheConfig(array $options): void
    {
        if (isset($options['cache_config'])) {
            $this->cacheConfig = array_merge($this->cacheConfig, $options['cache_config']);
        }
    }

    /**
     * 生成缓存键
     */
    protected function generateCacheKey(string $key, $data): string
    {
        $dataHash = $this->hashData($data);
        $hookClass = class_basename(static::class);
        
        return $this->cacheConfig['prefix'] . $hookClass . '_' . $key . '_' . $dataHash;
    }

    /**
     * 对数据进行哈希
     */
    protected function hashData($data): string
    {
        if (is_null($data)) {
            return 'null';
        }

        if (is_scalar($data)) {
            return md5((string) $data);
        }

        return md5(serialize($data));
    }

    /**
     * 从缓存获取结果
     */
    protected function getCachedResult(string $cacheKey)
    {
        try {
            if (!empty($this->cacheConfig['tags'])) {
                return Cache::tags($this->cacheConfig['tags'])->get($cacheKey);
            }

            return Cache::get($cacheKey);

        } catch (\Exception $e) {
            logger()->warning('缓存读取失败', [
                'hook' => static::class,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * 缓存结果
     */
    protected function cacheResult(string $cacheKey, $result): void
    {
        try {
            $cacheData = [
                'result' => $result,
                'cached_at' => now(),
                'hook' => static::class
            ];

            if (!empty($this->cacheConfig['tags'])) {
                Cache::tags($this->cacheConfig['tags'])
                     ->put($cacheKey, $cacheData, $this->cacheConfig['ttl']);
            } else {
                Cache::put($cacheKey, $cacheData, $this->cacheConfig['ttl']);
            }

            logger()->debug('结果已缓存', [
                'hook' => static::class,
                'cache_key' => $cacheKey,
                'ttl' => $this->cacheConfig['ttl']
            ]);

        } catch (\Exception $e) {
            logger()->warning('缓存写入失败', [
                'hook' => static::class,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 清除缓存
     */
    protected function invalidateCache(string $cacheKey): void
    {
        try {
            if (!empty($this->cacheConfig['tags'])) {
                Cache::tags($this->cacheConfig['tags'])->forget($cacheKey);
            } else {
                Cache::forget($cacheKey);
            }

            logger()->debug('缓存已清除', [
                'hook' => static::class,
                'cache_key' => $cacheKey
            ]);

        } catch (\Exception $e) {
            logger()->warning('缓存清除失败', [
                'hook' => static::class,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 处理数据
     */
    protected function processData(string $key, $data, array $options)
    {
        // TODO: 在这里实现你的数据处理逻辑
        
        // 示例处理：
        switch ($key) {
            case 'calculate':
                return $this->performCalculation($data, $options);
                
            case 'transform':
                return $this->transformData($data, $options);
                
            case 'validate':
                return $this->validateData($data, $options);
                
            default:
                return $this->defaultProcessing($key, $data, $options);
        }
    }

    /**
     * 执行计算
     */
    protected function performCalculation($data, array $options)
    {
        // TODO: 实现计算逻辑
        
        // 模拟复杂计算
        sleep(1); // 模拟耗时操作
        
        return [
            'calculation_result' => is_numeric($data) ? $data * 2 : 0,
            'calculated_at' => now()
        ];
    }

    /**
     * 转换数据
     */
    protected function transformData($data, array $options)
    {
        // TODO: 实现数据转换逻辑
        
        return [
            'original_data' => $data,
            'transformed_data' => is_array($data) ? array_reverse($data) : strtoupper((string) $data),
            'transformed_at' => now()
        ];
    }

    /**
     * 验证数据
     */
    protected function validateData($data, array $options)
    {
        // TODO: 实现数据验证逻辑
        
        $isValid = !empty($data);
        
        return [
            'is_valid' => $isValid,
            'validation_rules' => $options['rules'] ?? [],
            'validated_at' => now()
        ];
    }

    /**
     * 默认处理
     */
    protected function defaultProcessing(string $key, $data, array $options)
    {
        // TODO: 实现默认处理逻辑
        
        return [
            'key' => $key,
            'data' => $data,
            'processed_at' => now()
        ];
    }

    /**
     * 格式化缓存结果
     */
    protected function formatCachedResult(array $cachedData): array
    {
        return [
            'status' => 'success',
            'result' => $cachedData['result'],
            'from_cache' => true,
            'cached_at' => $cachedData['cached_at'],
            'timestamp' => now()
        ];
    }

    /**
     * 格式化结果
     */
    protected function formatResult($result, bool $fromCache = false): array
    {
        return [
            'status' => 'success',
            'result' => $result,
            'from_cache' => $fromCache,
            'timestamp' => now()
        ];
    }

    // 缓存管理方法

    /**
     * 清除所有相关缓存
     */
    public function clearAllCache(): void
    {
        if (!empty($this->cacheConfig['tags'])) {
            Cache::tags($this->cacheConfig['tags'])->flush();
        } else {
            // 清除带有特定前缀的缓存（需要Redis等支持模式匹配的驱动）
            $pattern = $this->cacheConfig['prefix'] . class_basename(static::class) . '_*';
            // 注意：这需要根据具体的缓存驱动实现
        }
    }

    /**
     * 预热缓存
     */
    public function warmupCache(array $keys, $data = null): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            try {
                $result = $this->handle($key, $data);
                $results[$key] = ['status' => 'success', 'result' => $result];
            } catch (\Exception $e) {
                $results[$key] = ['status' => 'error', 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * 设置缓存配置
     */
    public function setCacheConfig(array $config): self
    {
        $this->cacheConfig = array_merge($this->cacheConfig, $config);
        return $this;
    }

    /**
     * 获取缓存配置
     */
    public function getCacheConfig(): array
    {
        return $this->cacheConfig;
    }

    /**
     * 启用/禁用缓存
     */
    public function setCacheEnabled(bool $enabled): self
    {
        $this->cacheConfig['enabled'] = $enabled;
        return $this;
    }

    /**
     * 设置缓存TTL
     */
    public function setCacheTtl(int $ttl): self
    {
        $this->cacheConfig['ttl'] = $ttl;
        return $this;
    }

    /**
     * 设置缓存标签
     */
    public function setCacheTags(array $tags): self
    {
        $this->cacheConfig['tags'] = $tags;
        return $this;
    }

    /**
     * 参数验证
     */
    protected function validateArgs(...$args): bool
    {
        return count($args) >= 1;
    }
}