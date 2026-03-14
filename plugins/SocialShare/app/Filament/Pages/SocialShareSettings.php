<?php

namespace Plugins\SocialShare\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;

class SocialShareSettings extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationLabel = '分享插件设置';
    protected static ?string $title = '社交分享设置';
    protected static string|\UnitEnum|null $navigationGroup = '系统设置';
    protected static ?int $navigationSort = 20;
    protected static bool $shouldRegisterNavigation = false;

    // 平台开关
    public bool $wechat_enabled   = true;
    public bool $weibo_enabled    = true;
    public bool $twitter_enabled  = true;
    public bool $facebook_enabled = true;
    public bool $copy_enabled     = true;

    // 微信 AppID
    public string $wechat_appid = '';

    // 按钮样式：round / square
    public string $button_style = 'round';

    // 是否显示文字标签
    public bool $show_labels = true;

    public function getView(): string
    {
        return 'social-share::filament.pages.social-share-settings';
    }

    public function mount(): void
    {
        $config = config('social-share');

        $this->wechat_enabled   = $config['platforms']['wechat']['enabled']   ?? true;
        $this->weibo_enabled    = $config['platforms']['weibo']['enabled']    ?? true;
        $this->twitter_enabled  = $config['platforms']['twitter']['enabled']  ?? true;
        $this->facebook_enabled = $config['platforms']['facebook']['enabled'] ?? true;
        $this->copy_enabled     = $config['platforms']['copy']['enabled']     ?? true;
        $this->wechat_appid     = $config['wechat_appid']  ?? '';
        $this->button_style     = $config['button_style']  ?? 'round';
        $this->show_labels      = $config['show_labels']   ?? true;
    }

    public function save(): void
    {
        $configPath = base_path('plugins/SocialShare/config/social-share.php');

        $content = "<?php\n\nreturn [\n"
            . "    'platforms' => [\n"
            . "        'wechat'   => ['label' => '微信',     'enabled' => " . ($this->wechat_enabled   ? 'true' : 'false') . "],\n"
            . "        'weibo'    => ['label' => '微博',     'enabled' => " . ($this->weibo_enabled    ? 'true' : 'false') . "],\n"
            . "        'twitter'  => ['label' => 'Twitter',  'enabled' => " . ($this->twitter_enabled  ? 'true' : 'false') . "],\n"
            . "        'facebook' => ['label' => 'Facebook', 'enabled' => " . ($this->facebook_enabled ? 'true' : 'false') . "],\n"
            . "        'copy'     => ['label' => '复制链接', 'enabled' => " . ($this->copy_enabled     ? 'true' : 'false') . "],\n"
            . "    ],\n"
            . "    'wechat_appid' => env('WECHAT_APPID', " . var_export($this->wechat_appid, true) . "),\n"
            . "    'button_style' => " . var_export($this->button_style, true) . ",\n"
            . "    'show_labels'  => " . ($this->show_labels ? 'true' : 'false') . ",\n"
            . "];\n";

        File::put($configPath, $content);

        Notification::make()
            ->title('设置已保存')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('保存设置')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }
}
