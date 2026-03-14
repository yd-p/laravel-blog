<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = '站点设置';
    protected static ?string $title = '站点设置';
    protected static string|\UnitEnum|null $navigationGroup = '系统设置';
    protected static ?int $navigationSort = 5;

    // 基本信息
    public string $site_name = '';
    public string $site_description = '';
    public string $site_url = '';
    public string $site_logo = '';
    public string $site_favicon = '';
    public string $site_icp = '';
    public string $site_copyright = '';

    // SEO
    public string $seo_title_suffix = '';
    public string $seo_keywords = '';
    public string $seo_description = '';
    public string $seo_robots = 'index,follow';
    public bool   $seo_sitemap_enabled = true;
    public string $google_analytics_id = '';
    public string $baidu_analytics_id = '';
    public string $google_search_console = '';
    public string $baidu_search_console = '';

    // 社交媒体
    public string $social_weibo = '';
    public string $social_wechat_oa = '';
    public string $social_twitter = '';
    public string $social_github = '';

    // 联系方式
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $contact_address = '';

    // AI 配置
    public string $ai_provider = 'doubao';
    public string $doubao_api_key = '';
    public string $doubao_model = 'doubao-pro-32k';
    public string $doubao_polish_prompt = '你是一位专业的中文内容编辑，请对以下文章内容进行润色优化：保持原意不变，改善语言表达，使文章更流畅自然、专业易读。直接返回润色后的内容，不要添加任何解释。';
    public string $openai_api_key = '';
    public string $openai_model = 'gpt-4o-mini';
    public string $openai_base_url = 'https://api.openai.com/v1';

    public function getView(): string
    {
        return 'filament.pages.site-settings';
    }

    public function mount(): void
    {
        $keys = [
            'site_name', 'site_description', 'site_url', 'site_logo',
            'site_favicon', 'site_icp', 'site_copyright',
            'seo_title_suffix', 'seo_keywords', 'seo_description', 'seo_robots',
            'seo_sitemap_enabled', 'google_analytics_id', 'baidu_analytics_id',
            'google_search_console', 'baidu_search_console',
            'social_weibo', 'social_wechat_oa', 'social_twitter', 'social_github',
            'contact_email', 'contact_phone', 'contact_address',
            'ai_provider', 'doubao_api_key', 'doubao_model', 'doubao_polish_prompt',
            'openai_api_key', 'openai_model', 'openai_base_url',
        ];

        foreach ($keys as $key) {
            $value = SiteSetting::get($key);
            if ($value !== null) {
                $this->$key = $value;
            }
        }
    }

    public function save(): void
    {
        SiteSetting::setMany([
            'site_name'              => $this->site_name,
            'site_description'       => $this->site_description,
            'site_url'               => $this->site_url,
            'site_logo'              => $this->site_logo,
            'site_favicon'           => $this->site_favicon,
            'site_icp'               => $this->site_icp,
            'site_copyright'         => $this->site_copyright,
            'seo_title_suffix'       => $this->seo_title_suffix,
            'seo_keywords'           => $this->seo_keywords,
            'seo_description'        => $this->seo_description,
            'seo_robots'             => $this->seo_robots,
            'seo_sitemap_enabled'    => $this->seo_sitemap_enabled,
            'google_analytics_id'    => $this->google_analytics_id,
            'baidu_analytics_id'     => $this->baidu_analytics_id,
            'google_search_console'  => $this->google_search_console,
            'baidu_search_console'   => $this->baidu_search_console,
            'social_weibo'           => $this->social_weibo,
            'social_wechat_oa'       => $this->social_wechat_oa,
            'social_twitter'         => $this->social_twitter,
            'social_github'          => $this->social_github,
            'contact_email'          => $this->contact_email,
            'contact_phone'          => $this->contact_phone,
            'contact_address'        => $this->contact_address,
            'ai_provider'            => $this->ai_provider,
            'doubao_api_key'         => $this->doubao_api_key,
            'doubao_model'           => $this->doubao_model,
            'doubao_polish_prompt'   => $this->doubao_polish_prompt,
            'openai_api_key'         => $this->openai_api_key,
            'openai_model'           => $this->openai_model,
            'openai_base_url'        => $this->openai_base_url,
        ]);

        Notification::make()->title('设置已保存')->success()->send();
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
