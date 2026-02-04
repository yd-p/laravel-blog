<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Hooks\HookManager;
use App\Hooks\HookDiscovery;

/**
 * 钩子管理命令
 */
class HookCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'hook {action} {--hook=} {--group=} {--id=}';

    /**
     * 命令描述
     */
    protected $description = '管理应用钩子';

    protected HookManager $hookManager;
    protected HookDiscovery $hookDiscovery;

    public function __construct(HookManager $hookManager, HookDiscovery $hookDiscovery)
    {
        parent::__construct();
        $this->hookManager = $hookManager;
        $this->hookDiscovery = $hookDiscovery;
    }

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listHooks(),
            'stats' => $this->showStats(),
            'discover' => $this->discoverHooks(),
            'clear-cache' => $this->clearCache(),
            'enable' => $this->enableHook(),
            'disable' => $this->disableHook(),
            'remove' => $this->removeHook(),
            'test' => $this->testHook(),
            default => $this->showHelp(),
        };
    }

    /**
     * 列出钩子
     */
    protected function listHooks(): int
    {
        $hookName = $this->option('hook');
        $group = $this->option('group');

        $hooks = $this->hookManager->getHooks($hookName, $group);

        if (empty($hooks)) {
            $this->info('没有找到钩子');
            return 0;
        }

        $this->info('钩子列表:');
        $this->line('');

        foreach ($hooks as $name => $hookList) {
            $this->line("<fg=cyan>钩子名称:</> {$name}");
            
            if (is_array($hookList)) {
                foreach ($hookList as $hookId => $hook) {
                    $status = $hook['enabled'] ? '<fg=green>启用</>' : '<fg=red>禁用</>';
                    $group = $hook['group'] ?? 'default';
                    $priority = $hook['priority'];
                    $callCount = $hook['call_count'];
                    
                    $this->line("  ID: {$hookId}");
                    $this->line("  状态: {$status}");
                    $this->line("  分组: {$group}");
                    $this->line("  优先级: {$priority}");
                    $this->line("  调用次数: {$callCount}");
                    $this->line("  最后调用: " . ($hook['last_called'] ?? '从未调用'));
                    $this->line('');
                }
            }
        }

        return 0;
    }

    /**
     * 显示统计信息
     */
    protected function showStats(): int
    {
        $stats = $this->hookManager->getStats();

        $this->info('钩子统计信息:');
        $this->line('');
        $this->line("总钩子数: {$stats['total_hooks']}");
        $this->line("启用钩子数: {$stats['enabled_hooks']}");
        $this->line("禁用钩子数: {$stats['disabled_hooks']}");
        $this->line("总调用次数: {$stats['total_calls']}");
        $this->line('');

        if (!empty($stats['groups'])) {
            $this->info('分组统计:');
            foreach ($stats['groups'] as $group => $groupStats) {
                $this->line("  {$group}: {$groupStats['count']} 个钩子, {$groupStats['calls']} 次调用");
            }
            $this->line('');
        }

        if (!empty($stats['hook_names'])) {
            $this->info('钩子名称:');
            foreach ($stats['hook_names'] as $name) {
                $this->line("  - {$name}");
            }
        }

        return 0;
    }

    /**
     * 发现钩子
     */
    protected function discoverHooks(): int
    {
        $this->info('开始发现钩子...');
        
        $this->hookDiscovery->rediscover();
        
        $this->info('钩子发现完成');
        
        return $this->showStats();
    }

    /**
     * 清除缓存
     */
    protected function clearCache(): int
    {
        $this->hookManager->clearCache();
        $this->info('钩子缓存已清除');
        return 0;
    }

    /**
     * 启用钩子
     */
    protected function enableHook(): int
    {
        $hookName = $this->option('hook');
        $hookId = $this->option('id');

        if (!$hookName || !$hookId) {
            $this->error('请指定钩子名称和ID: --hook=钩子名称 --id=钩子ID');
            return 1;
        }

        if ($this->hookManager->toggle($hookName, $hookId, true)) {
            $this->info("钩子 {$hookName}#{$hookId} 已启用");
            return 0;
        }

        $this->error("启用钩子失败，请检查钩子名称和ID是否正确");
        return 1;
    }

    /**
     * 禁用钩子
     */
    protected function disableHook(): int
    {
        $hookName = $this->option('hook');
        $hookId = $this->option('id');

        if (!$hookName || !$hookId) {
            $this->error('请指定钩子名称和ID: --hook=钩子名称 --id=钩子ID');
            return 1;
        }

        if ($this->hookManager->toggle($hookName, $hookId, false)) {
            $this->info("钩子 {$hookName}#{$hookId} 已禁用");
            return 0;
        }

        $this->error("禁用钩子失败，请检查钩子名称和ID是否正确");
        return 1;
    }

    /**
     * 移除钩子
     */
    protected function removeHook(): int
    {
        $hookName = $this->option('hook');
        $hookId = $this->option('id');
        $group = $this->option('group');

        if ($group) {
            // 按分组移除
            $count = $this->hookManager->removeByGroup($group);
            $this->info("已移除分组 '{$group}' 下的 {$count} 个钩子");
            return 0;
        }

        if (!$hookName) {
            $this->error('请指定钩子名称: --hook=钩子名称 [--id=钩子ID]');
            return 1;
        }

        if ($this->hookManager->remove($hookName, $hookId)) {
            if ($hookId) {
                $this->info("钩子 {$hookName}#{$hookId} 已移除");
            } else {
                $this->info("钩子 {$hookName} 下的所有钩子已移除");
            }
            return 0;
        }

        $this->error("移除钩子失败，请检查钩子名称和ID是否正确");
        return 1;
    }

    /**
     * 测试钩子
     */
    protected function testHook(): int
    {
        $hookName = $this->option('hook');

        if (!$hookName) {
            $this->error('请指定要测试的钩子名称: --hook=钩子名称');
            return 1;
        }

        $this->info("测试钩子: {$hookName}");
        
        $result = $this->hookManager->execute($hookName, 'test_data', now());
        
        $this->line('');
        $this->info('执行结果:');
        $this->line("钩子名称: {$result->getHookName()}");
        $this->line("执行数量: {$result->getExecutedCount()}");
        $this->line("执行时间: " . number_format($result->getExecutionTime() * 1000, 2) . " ms");
        $this->line("成功率: " . number_format($result->getSuccessRate(), 2) . "%");
        
        if ($result->hasErrors()) {
            $this->line('');
            $this->error('执行错误:');
            foreach ($result->getErrors() as $hookId => $error) {
                $this->line("  {$hookId}: {$error['error']}");
            }
        }

        if (!empty($result->getResults())) {
            $this->line('');
            $this->info('执行结果:');
            foreach ($result->getResults() as $hookId => $hookResult) {
                $this->line("  {$hookId}: " . json_encode($hookResult, JSON_UNESCAPED_UNICODE));
            }
        }

        return 0;
    }

    /**
     * 显示帮助信息
     */
    protected function showHelp(): int
    {
        $this->info('钩子管理命令帮助:');
        $this->line('');
        $this->line('可用操作:');
        $this->line('  list        列出钩子');
        $this->line('  stats       显示统计信息');
        $this->line('  discover    发现并注册钩子');
        $this->line('  clear-cache 清除钩子缓存');
        $this->line('  enable      启用钩子');
        $this->line('  disable     禁用钩子');
        $this->line('  remove      移除钩子');
        $this->line('  test        测试钩子');
        $this->line('');
        $this->line('选项:');
        $this->line('  --hook=名称  指定钩子名称');
        $this->line('  --group=分组 指定钩子分组');
        $this->line('  --id=ID     指定钩子ID');
        $this->line('');
        $this->line('示例:');
        $this->line('  php artisan hook list');
        $this->line('  php artisan hook list --group=auth');
        $this->line('  php artisan hook stats');
        $this->line('  php artisan hook discover');
        $this->line('  php artisan hook enable --hook=user.login --id=hook_abc123');
        $this->line('  php artisan hook test --hook=user.login');

        return 0;
    }
}