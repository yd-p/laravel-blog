<div style="max-height:65vh;overflow-y:auto;overscroll-behavior:contain;padding:1rem 1.25rem;">

    {{-- 元信息 --}}
    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:1rem;">
        {{ implode('  ·  ', array_filter([
            $record->category?->name,
            $record->author?->name,
            $record->published_at?->format('Y-m-d'),
            '👁 ' . number_format($record->view_count),
        ])) }}
    </p>

    {{-- 标签 --}}
    @if($record->tags->isNotEmpty())
        <p style="margin-bottom:1rem;">
            <span style="color:#6b7280;font-size:0.875rem;">标签：</span>
            {{ $record->tags->pluck('name')->join('  ·  ') }}
        </p>
    @endif

    {{-- 摘要 --}}
    @if(filled($record->getRawOriginal('excerpt')))
        <blockquote style="border-left:3px solid #e5e7eb;padding-left:1rem;color:#6b7280;margin-bottom:1rem;">
            {{ $record->excerpt }}
        </blockquote>
    @endif

    {{-- 正文（Markdown 渲染） --}}
    <div style="line-height:1.75;word-break:break-word;">
        {!! \Filament\Support\Markdown::block($record->content ?? '')->toHtml() !!}
    </div>

</div>
