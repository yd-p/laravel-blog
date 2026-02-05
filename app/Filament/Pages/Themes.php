<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use App\Models\Theme;
use App\Models\ThemeOptions;
use Illuminate\Support\Facades\Artisan;

class Themes extends Page implements HasForms
{
    use InteractsWithForms;

    public array $themes = [];

    private string $themes_folder = '';

    protected string $view = 'filament.pages.themes';

    protected static ?string $navigationLabel = '主题';

    public ?array $formData = [];

    public function mount(): void
    {
        $this->themes_folder = base_path('themes');
        $this->updateThemes();
        $this->refreshThemes();
    }

    /**
     * 刷新数据库中已安装且文件夹存在的主题列表
     */
    private function refreshThemes(): void
    {
        $all_themes = Theme::all();
        $this->themes = [];
        foreach ($all_themes as $theme) {
            if (file_exists(base_path('themes/' . $theme->folder))) {
                $this->themes[] = $theme;
            }
        }
    }

    /**
     * 扫描主题文件夹，获取所有有效主题信息
     *
     * @return object 主题集合对象
     */
    private function getThemesFromFolder(): object
    {
        $themes = [];

        if (!file_exists($this->themes_folder)) {
            mkdir($this->themes_folder, 0755, true);
        }

        $scandirectory = scandir($this->themes_folder);

        if (is_array($scandirectory)) {
            foreach ($scandirectory as $folder) {
                // 排除 . 和 .. 并且确认是目录
                if ($folder === '.' || $folder === '..') {
                    continue;
                }

                $folderPath = $this->themes_folder . '/' . $folder;
                if (!is_dir($folderPath)) {
                    continue;
                }

                $json_file = $folderPath . '/theme.json';
                if (file_exists($json_file)) {
                    $theme_data = json_decode(file_get_contents($json_file), true);
                    if (is_array($theme_data)) {
                        $theme_data['folder'] = $folder;
                        $themes[$folder] = (object)$theme_data;
                    }
                }
            }
        }

        return (object)$themes;
    }

    /**
     * 更新主题信息到数据库
     */
    private function updateThemes(): void
    {
        $themes = $this->getThemesFromFolder();
        foreach ($themes as $theme) {
            if (isset($theme->folder)) {
                $theme_exists = Theme::where('folder', $theme->folder)->first();
                if (!isset($theme_exists->id)) {
                    $version = $theme->version ?? '';
                    Theme::create([
                        'name' => $theme->name,
                        'folder' => $theme->folder,
                        'version' => $version,
                    ]);
                } else {
                    $theme_exists->name = $theme->name;
                    $theme_exists->version = $theme->version ?? '';
                    $theme_exists->save();
                }
            }
        }
    }

    /**
     * 激活指定主题
     */
    public function activate(string $theme_folder): void
    {
        $theme = Theme::where('folder', $theme_folder)->first();

        if (isset($theme->id)) {
            $this->deactivateThemes();
            $theme->active = 1;
            $theme->save();

            $this->writeThemeJson($theme_folder);

            Notification::make()
                ->title('启用成功 ' . $theme_folder . ' 主题')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('未找到主题文件夹，请确认该主题已完成安装')
                ->danger()
                ->send();
        }

        // 清理缓存
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        $this->refreshThemes();
    }

    /**
     * 写入当前激活主题配置文件
     */
    private function writeThemeJson(string $themeName): void
    {
        $themeJsonPath = base_path('themes/theme.json');
        $themeJsonContent = json_encode(['name' => $themeName], JSON_PRETTY_PRINT);
        File::put($themeJsonPath, $themeJsonContent);
    }

    /**
     * 取消所有主题激活状态
     */
    private function deactivateThemes(): void
    {
        Theme::query()->update(['active' => 0]);
    }

    /**
     * 删除主题（默认主题不可删除）
     */
    public function deleteTheme(string $theme_folder): void
    {
        $theme = Theme::where('folder', $theme_folder)->first();

        if (!$theme) {
            Notification::make()
                ->title('未找到主题文件夹，请确认该主题已完成安装')
                ->danger()
                ->send();

            return;
        }

        // 如果是默认主题，拒绝删除
        if ($theme->default || $theme->id === 1) {
            Notification::make()
                ->title('默认主题不能被删除')
                ->danger()
                ->send();

            return;
        }
        $theme_location = base_path('themes') . '/' . $theme->folder;
        if (file_exists($theme_location)) {
            File::deleteDirectory($theme_location, false);
        }

        $theme->delete();
        $this->setDefaultTheme();
        $this->writeThemeJson('anchor');
        Notification::make()
            ->title('主题删除成功')
            ->success()
            ->send();

        $this->refreshThemes();
    }

    /**
     * 设置默认主题，通常默认主题 id=1
     */
    public function setDefaultTheme(int $id = 1): void
    {
        // 先清空所有主题的 default 字段
        Theme::query()->update(['default' => 0]);

        // 设置指定 id 为默认主题
        $theme = Theme::find($id);
        if ($theme) {
            $theme->default = 1;
            $theme->save();
        }
    }

    /**
     * 同步 theme.json 里的配置项到数据库
     */
    private function syncThemeConfigs(Theme $theme): void
    {
        $themeJsonFile = base_path("themes/{$theme->folder}/theme.json");

        if (!file_exists($themeJsonFile)) {
            return;
        }

        $themeData = json_decode(file_get_contents($themeJsonFile), true);

        if (!isset($themeData['config'])) {
            return;
        }

        foreach ($themeData['config'] as $key => $definition) {
            ThemeOptions::firstOrCreate(
                ['theme_id' => $theme->id, 'key' => $key],
                ['value' => $definition['default'] ?? null]
            );
        }
    }

    public function configureTheme(): Action
    {
        return Action::make('configureTheme')
            ->label('配置主题')
            ->icon('heroicon-o-cog-6-tooth')
            ->modalHeading('配置主题')
            ->form(function (array $arguments) {
                $themeId = $arguments['theme_id'] ?? null;
                $theme = Theme::findOrFail($themeId);
                // 先同步配置
                $this->syncThemeConfigs($theme);
                // 从数据库加载配置
                $configs = $theme->options()->pluck('value', 'key')->toArray();
                // 从 theme.json 取定义
                $themeJsonFile = base_path("themes/{$theme->folder}/theme.json");
                $themeData = file_exists($themeJsonFile) ? json_decode(file_get_contents($themeJsonFile), true) : [];
                $definitions = $themeData['config'] ?? [];

                $form = [];
                foreach ($definitions as $key => $definition) {
                    $label = $definition['label'] ?? $key;
                    $type  = $definition['type'] ?? 'text';
                    $value = $configs[$key] ?? ($definition['default'] ?? null);
                    switch ($type) {
                        case 'toggle':
                            $form[] = Toggle::make($key)
                                ->label($label)
                                ->default((bool) $value);
                            break;
                        case 'color':
                            $form[] = ColorPicker::make($key)
                                ->label($label)
                                ->default($value);
                            break;
                        case 'textarea':
                            $form[] = Textarea::make($key)
                                ->label($label)
                                ->default($value);
                            break;
                        case 'select':
                            $options = $definition['options'] ?? [];
                            $form[] = Select::make($key)
                                ->label($label)
                                ->options($options)
                                ->default($value);
                            break;
                        default:
                            $form[] = TextInput::make($key)
                                ->label($label)
                                ->default($value);
                    }
                }

                return $form;
            })
            ->action(function (array $data, array $arguments) {
                $themeId = $arguments['theme_id'] ?? null;
                $theme = Theme::findOrFail($themeId);

                foreach ($data as $key => $value) {
                    ThemeOptions::updateOrCreate(
                        ['theme_id' => $theme->id, 'key' => $key],
                        ['value' => $value]
                    );
                }

                Notification::make()
                    ->title("主题 {$theme->name} 配置已更新")
                    ->success()
                    ->send();
            });
    }
}
