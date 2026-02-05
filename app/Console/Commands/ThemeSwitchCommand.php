<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThemeService;

class ThemeSwitchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:switch {theme}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '切换当前主题';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        $theme = $this->argument('theme');
        $themes = $themeService->getAvailableThemes();

        if (!isset($themes[$theme])) {
            $this->error("主题 '{$theme}' 不存在！");
            $this->info('可用主题:');
            foreach (array_keys($themes) as $availableTheme) {
                $this->line("  - {$availableTheme}");
            }
            return Command::FAILURE;
        }

        $themeService->setCurrentTheme($theme);
        
        $this->info("✓ 已切换到主题: {$theme}");
        
        // 询问是否编译资源
        if ($this->confirm('是否立即编译主题资源？', true)) {
            $this->call('theme:compile', ['theme' => $theme]);
        }

        // 清除缓存
        if ($this->confirm('是否清除缓存？', true)) {
            $this->call('cache:clear');
            $this->call('view:clear');
            $this->info('✓ 缓存已清除');
        }

        return Command::SUCCESS;
    }
}
