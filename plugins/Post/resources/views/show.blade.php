@extends('layouts.app')

@section('content')
<div class="post-detail">
    {{-- 插件钩子：文章内容前 --}}
    @plugin_hook('post.before_content')
    
    <article class="post">
        <header class="post-header">
            <h1>{{ $post->title }}</h1>
            <div class="post-meta">
                <span class="author">作者: {{ $post->author->name }}</span>
                <span class="date">发布时间: {{ $post->published_at->format('Y-m-d') }}</span>
                <span class="views">阅读量: {{ $post->view_count }}</span>
            </div>
        </header>
        
        @if($post->thumbnail)
        <div class="post-thumbnail">
            <img src="{{ $post->thumbnail }}" alt="{{ $post->title }}">
        </div>
        @endif
        
        <div class="post-content">
            {!! $post->content !!}
        </div>
        
        @if($post->tags->count() > 0)
        <div class="post-tags">
            <strong>标签:</strong>
            @foreach($post->tags as $tag)
                <span class="tag" style="background-color: {{ $tag->color }}">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>
        @endif
    </article>
    
    {{-- 插件钩子：文章内容后 --}}
    @plugin_hook('post.after_content')
    
    {{-- 相关文章 --}}
    @if(isset($latest_posts) && $latest_posts->count() > 0)
    <div class="related-posts">
        <h3>最新文章</h3>
        <ul>
            @foreach($latest_posts as $latestPost)
                <li>
                    <a href="/posts/{{ $latestPost->slug }}">{{ $latestPost->title }}</a>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

<aside class="sidebar">
    {{-- 插件钩子：侧边栏 --}}
    @plugin_hook('post.sidebar')
    
    {{-- 分类列表 --}}
    @if(isset($post_categories) && $post_categories->count() > 0)
    <div class="categories-widget">
        <h3>文章分类</h3>
        <ul>
            @foreach($post_categories as $category)
                <li>
                    <a href="/categories/{{ $category->slug }}">
                        {{ $category->name }} ({{ $category->posts_count }})
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</aside>
@endsection
