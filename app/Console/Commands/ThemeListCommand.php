<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThemeService;

class ThemeListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '列出所有可用主题';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        $themes = $themeService->getAvailableThemes();
        $current = $themeService->getCurrentTheme();

        if (empty($themes)) {
            $this->warn('没有找到可用的主题');
            return Command::SUCCESS;
        }

        $this->info('可用主题列表:');
        $this->newLine();

        $tableData = [];
        foreach ($themes as $slug => $theme) {
            $isCurrent = $slug === $current;
            $type = $theme['type'] ?? 'system';
            $plugin = $theme['plugin'] ?? '-';
            
            $tableData[] = [
                $isCurrent ? '→' : ' ',
                $slug,
                $theme['name'] ?? $slug,
                $theme['version'] ?? '1.0.0',
                $type === 'plugin' ? "插件 ({$plugin})" : '系统',
                $theme['description'] ?? '-',
            ];
        }

        $this->table(
            ['', 'Slug', '名称', '版本', '类型', '描述'],
            $tableData
        );

        $this->newLine();
        $this->info("当前主题: {$current}");

        return Command::SUCCESS;
    }
}
