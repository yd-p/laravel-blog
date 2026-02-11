<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Plugins\PluginsManager;
use Filament\Pages\Page;

class Plugins extends Page
{
    use HasPageShield;

    protected string $view = 'filament.pages.plugins';
    protected static ?string $navigationLabel = '插件';

    public array $plugins = [];

    public function mount(): void
    {
        $this->refreshPlugins();
    }

    private function refreshPlugins(): void
    {
        $this->plugins = $this->getPluginsFromFolder();
    }

    private function getPluginsFromFolder(): array
    {
        $plugins = [];
        $pluginRoot = base_path('plugins');
        if (!is_dir($pluginRoot)) {
            mkdir($pluginRoot, 0755, true);
            return $plugins;
        }

        $iterator = new \DirectoryIterator($pluginRoot);

        foreach ($iterator as $folder) {
            if ($folder->isDot() || !$folder->isDir()) {
                continue;
            }

            $folderName = $folder->getFilename();
            $manifestPath = $pluginRoot . '/' . $folderName . '/plugin.json';
            if (File::exists($manifestPath)) {
                try {
                    $info = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
                    if (!empty($info['name'])) {
                        $info['folder'] = $folderName;
                        $info['active'] = $this->isPluginActive($folderName);
                        $plugins[$folderName] = $info;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return $plugins;
    }

    private function getPluginClassFromFolder(string $folder): string
    {
        $studly = Str::studly($folder);
        return "Plugins\\{$studly}\\{$studly}Plugin";
    }

    private function isPluginActive(string $folder): bool
    {
        return in_array($folder, $this->getInstalledPlugins(), true);
    }

    private function getInstalledPlugins(): array
    {
        $path = base_path('plugins/installed.json');

        if (!File::exists($path)) {
            return [];
        }

        $data = File::json($path, true);
        return is_array($data) ? $data : [];
    }

    private function updateInstalledPlugins(array $plugins): void
    {
        File::put(base_path('plugins/installed.json'), json_encode(array_values($plugins), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function activate(string $pluginFolder): void
    {
        $installed = $this->getInstalledPlugins();

        if (!in_array($pluginFolder, $installed)) {
            $installed[] = $pluginFolder;
            $this->updateInstalledPlugins($installed);
            $this->runPostActivation($pluginFolder);
        }

        $this->refreshPlugins();
    }

    private function runPostActivation(string $pluginFolder): void
    {
        $pluginClass = $this->getPluginClassFromFolder($pluginFolder);

        if (!class_exists($pluginClass)) return;

        $plugin = new $pluginClass(app());

        if (method_exists($plugin, 'getPostActivationCommands')) {
            foreach ((array)$plugin->getPostActivationCommands() as $command) {
                is_string($command) ? Artisan::call($command) : (is_callable($command) ? $command() : null);
            }
        }

        $migrationPath = base_path("plugins/{$pluginFolder}/database/migrations");

        if (File::isDirectory($migrationPath)) {
            Artisan::call('migrate', [
                '--path' => "plugins/{$pluginFolder}/database/migrations",
                '--force' => true,
            ]);
        }
    }

    public function deactivate(string $pluginFolder): void
    {
        $plugins = array_values(array_diff($this->getInstalledPlugins(), [$pluginFolder]));
        $this->updateInstalledPlugins($plugins);
        $this->refreshPlugins();
    }

    public function deletePluginAction(): Action
    {
        return Action::make('deletePluginAction')
            ->label('删除插件')
            ->iconButton()
            ->color('danger')
            ->tooltip('删除此插件')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('确认')
            ->modalCancelActionLabel('取消')
            ->modalDescription('确定要删除吗？')
            ->action(function (array $arguments) {
               $this->deactivate($arguments['folder']);
               $path = base_path("plugins/{$arguments['folder']}");
               if (File::exists($path)) {
                   File::deleteDirectory($path);
               }
               $this->refreshPlugins();
                $pluginsManager = app(PluginsManager::class);
                $pluginsManager->delete($arguments['name']);
                Notification::make()
                    ->body('插件'.$arguments['name'].'已删除')
                    ->danger()
                    ->send();
            });
    }

    public function disablePluginAction(): Action
    {
        return Action::make('disablePluginAction')
            ->label('禁用插件')
            ->icon('heroicon-s-x-circle')
            ->color('danger')
            ->tooltip('禁用此插件')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('确认')
            ->modalCancelActionLabel('取消')
            ->modalDescription('确定要禁用吗？')
            ->action(function (array $arguments) {
                $pluginsManager = app(PluginsManager::class);
                $pluginsManager->disable($arguments['name']);
                $this->refreshPlugins();
               $this->deactivate($arguments['folder']);
                Notification::make()
                    ->body('插件'.$arguments['name'].'已禁用')
                    ->danger()
                    ->send();
            });
    }

    public function activePluginAction(): Action
    {
        return Action::make('activePluginAction')
            ->label('启用插件')
            ->icon('heroicon-s-check-circle')
            ->tooltip('启用此插件')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('确认')
            ->modalCancelActionLabel('取消')
            ->modalDescription('确定要启用吗？')
            ->action(function (array $arguments) {
               $this->activate($arguments['folder']);
                $pluginsManager = app(PluginsManager::class);
                $pluginsManager->enable($arguments['name']);
                $this->refreshPlugins();
                Notification::make()
                    ->body('插件'.$arguments['name'].'已启用')
                    ->success()
                    ->send();
            });
    }

}
