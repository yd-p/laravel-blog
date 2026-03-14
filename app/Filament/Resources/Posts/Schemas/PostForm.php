<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Models\SiteSetting;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        // 基本信息 - 第一行
                        SelectTree::make('category_id')
                            ->label('文章分类')
                            ->relationship('category', 'name', 'parent_id')
                            ->placeholder('请选择文章所属分类')
                            ->withCount()
                            ->searchable()
                            ->defaultOpenLevel(99)
                            ->columnSpan(1),

                        Select::make('author_id')
                            ->label('文章作者')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->native(false)
                            ->placeholder('请选择文章作者')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('文章状态')
                            ->required()
                            ->options(PostStatus::toSelectArray())
                            ->default(PostStatus::DRAFT->value)
                            ->native(false)
                            ->columnSpan(1),

                        DateTimePicker::make('published_at')
                            ->label('发布时间')
                            ->placeholder('请选择文章发布时间（选填）')
                            ->native(false)
                            ->columnSpan(1),

                        // 第二行
                        TextInput::make('title')
                            ->label('文章标题')
                            ->required()
                            ->placeholder('请输入文章标题')
                            ->columnSpan(3),

                        TextInput::make('slug')
                            ->label('文章别名')
                            ->required()
                            ->placeholder('请输入文章别名（英文/数字/横杠，用于URL）')
                            ->columnSpan(1),

                        // 第三行
                        TextInput::make('thumbnail')
                            ->label('缩略图URL')
                            ->placeholder('请输入文章缩略图的完整URL（选填）')
                            ->columnSpan(3),

                        TextInput::make('view_count')
                            ->label('浏览次数')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->placeholder('初始浏览次数，默认0')
                            ->columnSpan(1),

                        // 内容区域
                        Textarea::make('excerpt')
                            ->label('文章摘要')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('请输入文章简短摘要（选填）'),

                        RichEditor::make('content')
                            ->label('文章内容')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('请输入文章正文内容')
                            ->hintActions([
                                Action::make('ai_polish')
                                    ->label('🤖 豆包润文')
                                    ->color('info')
                                    ->requiresConfirmation()
                                    ->modalHeading('AI 润文确认')
                                    ->modalDescription('将使用豆包 AI 对当前正文内容进行润色优化，原内容将被替换。确认继续？')
                                    ->modalSubmitActionLabel('开始润文')
                                    ->modalCancelActionLabel('取消')
                                    ->action(function ($get, $set) {
                                        $content = $get('content');
                                        if (empty(trim(strip_tags($content ?? '')))) {
                                            Notification::make()
                                                ->title('内容为空，无法润文')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        try {
                                            $polished = self::callAiPolish($content);
                                            $set('content', $polished);
                                            Notification::make()
                                                ->title('润文完成')
                                                ->success()
                                                ->send();
                                        } catch (\Throwable $e) {
                                            Notification::make()
                                                ->title('润文失败')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ]),

                        // SEO 设置
                        TextInput::make('seo_title')
                            ->label('SEO标题')
                            ->placeholder('请输入SEO标题（选填）')
                            ->columnSpan(2),

                        TextInput::make('seo_keywords')
                            ->label('SEO关键词')
                            ->placeholder('请输入SEO关键词，多个用逗号分隔（选填）')
                            ->columnSpan(2),

                        Textarea::make('seo_description')
                            ->label('SEO描述')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('请输入SEO描述（选填）'),
                    ])
                    ->columns(4),
            ]);
    }

    private static function callAiPolish(string $content): string
    {
        $provider = SiteSetting::get('ai_provider', 'doubao');
        $prompt   = SiteSetting::get('doubao_polish_prompt', '你是专业中文编辑，请润色以下文章内容，保持原意，使其更流畅专业，直接返回润色后内容：');

        if ($provider === 'openai') {
            $apiKey  = SiteSetting::get('openai_api_key', '');
            $model   = SiteSetting::get('openai_model', 'gpt-4o-mini');
            $baseUrl = rtrim(SiteSetting::get('openai_base_url', 'https://api.openai.com/v1'), '/');

            if (empty($apiKey)) {
                throw new \RuntimeException('OpenAI API Key 未配置，请前往站点设置填写');
            }

            $response = Http::withToken($apiKey)->timeout(60)
                ->post("{$baseUrl}/chat/completions", [
                    'model'    => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user',   'content' => strip_tags($content)],
                    ],
                ]);
        } else {
            $apiKey = SiteSetting::get('doubao_api_key', '');
            $model  = SiteSetting::get('doubao_model', 'doubao-pro-32k');

            if (empty($apiKey)) {
                throw new \RuntimeException('豆包 API Key 未配置，请前往站点设置填写');
            }

            $response = Http::withToken($apiKey)->timeout(60)
                ->post('https://ark.cn-beijing.volces.com/api/v3/chat/completions', [
                    'model'    => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user',   'content' => strip_tags($content)],
                    ],
                ]);
        }

        if ($response->failed()) {
            throw new \RuntimeException('AI 接口请求失败：' . $response->body());
        }

        return $response->json('choices.0.message.content', '');
    }
}
