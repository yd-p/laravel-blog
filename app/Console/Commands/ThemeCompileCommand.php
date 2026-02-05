<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThemeService;

class ThemeCompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:compile {theme?} {--all : Compile all themes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '编译主题资源文件';

    /**
     * Execute the console command.
     */
    public function handle(ThemeService $themeService): int
    {
        if ($this->option('all')) {
            $this->info('开始编译所有主题...');
            $results = $themeService->compileAllAssets();
            
            foreach ($results as $theme => $success) {
                if ($success) {
                    $this->info("✓ {$theme} 编译成功");
                } else {
                    $this->error("✗ {$theme} 编译失败");
                }
            }
            
            $this->info('所有主题编译完成！');
            return Command::SUCCESS;
        }

        $theme = $this->argument('theme') ?? $themeService->getCurrentTheme();
        
        $this->info("正在编译主题: {$theme}");
        
        if ($themeService->compileAssets($theme)) {
            $this->info("✓ 主题 {$theme} 编译成功！");
            return Command::SUCCESS;
        }

        $this->error("✗ 主题 {$theme} 编译失败！");
        return Command::FAILURE;
    }
}
