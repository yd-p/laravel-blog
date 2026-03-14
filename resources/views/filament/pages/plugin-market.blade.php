<x-filament-panels::page>

    {{-- 顶部工具栏 --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        {{-- 搜索框 --}}
        <div style="position:relative;flex:1;min-width:200px;max-width:360px;">
            <div style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);pointer-events:none;">
                <x-filament::icon icon="heroicon-o-magnifying-glass" style="width:1rem;height:1rem;color:rgb(156 163 175);" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="搜索插件名称、描述..."
                style="width:100%;padding:0.5rem 0.75rem 0.5rem 2.25rem;border:1px solid rgb(209 213 219);border-radius:0.5rem;font-size:0.875rem;outline:none;background:#fff;"
                onfocus="this.style.borderColor='rgb(99 102 241)'"
                onblur="this.style.borderColor='rgb(209 213 219)'"
            >
        </div>

        {{-- 右侧操作按钮 --}}
        <div style="display:flex;gap:0.5rem;flex-shrink:0;">
            <button
                wire:click="refreshCache"
                style="display:flex;align-items:center;gap:0.375rem;padding:0.5rem 0.875rem;background:#fff;border:1px solid rgb(209 213 219);border-radius:0.5rem;font-size:0.8125rem;color:rgb(55 65 81);cursor:pointer;"
                onmouseover="this.style.background='rgb(249 250 251)'"
                onmouseout="this.style.background='#fff'"
                title="刷新缓存"
            >
                <x-filament::icon icon="heroicon-o-arrow-path" style="width:0.875rem;height:0.875rem;" />
                <span>刷新</span>
            </button>
            {{ $this->urlInstallAction }}
            {{ $this->uploadInstallAction }}
        </div>
    </div>

    {{-- 分类标签 --}}
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        @foreach($categories as $cat)
            @php $isActive = $activeCategory === $cat['id']; @endphp
            <button
                wire:click="filterByCategory('{{ $cat['id'] }}')"
                style="padding:0.375rem 0.875rem;border-radius:9999px;font-size:0.8125rem;font-weight:500;cursor:pointer;border:1px solid {{ $isActive ? 'rgb(99 102 241)' : 'rgb(229 231 235)' }};background:{{ $isActive ? 'rgb(99 102 241)' : '#fff' }};color:{{ $isActive ? '#fff' : 'rgb(55 65 81)' }};"
                onmouseover="@if(!$isActive) this.style.background='rgb(249 250 251)' @endif"
                onmouseout="@if(!$isActive) this.style.background='#fff' @endif"
            >
                {{ $cat['name'] }}
            </button>
        @endforeach
    </div>

    {{-- 插件列表 --}}
    @if(count($marketPlugins) === 0)
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;text-align:center;">
            <div style="width:4rem;height:4rem;background:rgb(243 244 246);border-radius:9999px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <x-filament::icon icon="heroicon-o-shopping-bag" style="width:2rem;height:2rem;color:rgb(156 163 175);" />
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:rgb(17 24 39);margin-bottom:0.25rem;">未找到插件</h3>
            <p style="font-size:0.875rem;color:rgb(107 114 128);">尝试更换关键词或分类</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
            @foreach($marketPlugins as $plugin)
                @php
                    $installed = $this->isInstalled($plugin['folder'] ?? '');
                    $rating = $plugin['rating'] ?? 0;
                    $fullStars = floor($rating);
                @endphp
                <div style="background:#fff;border-radius:0.75rem;border:1px solid rgb(229 231 235);overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .15s;"
                     onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'"
                     onmouseout="this.style.boxShadow='none'">

                    {{-- 封面 --}}
                    <div style="position:relative;height:140px;background:linear-gradient(135deg,rgb(238 242 255),rgb(224 231 255));overflow:hidden;">
                        @if(!empty($plugin['cover']))
                            <img src="{{ $plugin['cover'] }}" alt="{{ $plugin['name'] }}"
                                 style="width:100%;height:100%;object-fit:cover;"
                                 onerror="this.style.display='none'">
                        @else
                            <div style="display:flex;align-items:center;justify-content:center;height:100%;">
                                <x-filament::icon icon="heroicon-o-puzzle-piece" style="width:3rem;height:3rem;color:rgb(129 140 248);" />
                            </div>
                        @endif

                        {{-- 已安装徽章 --}}
                        @if($installed)
                            <div style="position:absolute;top:0.625rem;right:0.625rem;">
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(220 252 231);color:rgb(22 101 52);border:1px solid rgb(187 247 208);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    <span style="width:6px;height:6px;background:rgb(34 197 94);border-radius:9999px;display:inline-block;"></span>
                                    已安装
                                </span>
                            </div>
                        @endif

                        {{-- 分类标签 --}}
                        @if(!empty($plugin['category']))
                            <div style="position:absolute;top:0.625rem;left:0.625rem;">
                                <span style="background:rgba(0,0,0,0.45);color:#fff;border-radius:9999px;padding:0.15rem 0.5rem;font-size:0.7rem;">
                                    {{ collect($categories)->firstWhere('id', $plugin['category'])['name'] ?? $plugin['category'] }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- 插件信息 --}}
                    <div style="padding:1rem;flex:1;display:flex;flex-direction:column;gap:0.375rem;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">
                            <h4 style="font-size:0.9375rem;font-weight:600;color:rgb(17 24 39);line-height:1.3;">{{ $plugin['name'] }}</h4>
                            @if(!empty($plugin['version']))
                                <span style="flex-shrink:0;font-size:0.7rem;color:rgb(107 114 128);background:rgb(243 244 246);padding:0.125rem 0.5rem;border-radius:9999px;margin-top:2px;">v{{ $plugin['version'] }}</span>
                            @endif
                        </div>

                        @if(!empty($plugin['description']))
                            <p style="font-size:0.8125rem;color:rgb(107 114 128);line-height:1.5;flex:1;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ $plugin['description'] }}
                            </p>
                        @endif

                        {{-- 评分 + 下载量 --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.25rem;">
                            <div style="display:flex;align-items:center;gap:0.25rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <span style="font-size:0.75rem;color:{{ $i <= $fullStars ? 'rgb(251 191 36)' : 'rgb(209 213 219)' }};">★</span>
                                @endfor
                                @if($rating > 0)
                                    <span style="font-size:0.75rem;color:rgb(107 114 128);margin-left:0.125rem;">{{ number_format($rating, 1) }}</span>
                                @endif
                            </div>
                            @if(!empty($plugin['downloads']))
                                <span style="font-size:0.75rem;color:rgb(156 163 175);display:flex;align-items:center;gap:0.25rem;">
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:0.75rem;height:0.75rem;" />
                                    {{ number_format($plugin['downloads']) }}
                                </span>
                            @endif
                        </div>

                        {{-- 作者 --}}
                        @if(!empty($plugin['author']))
                            <div style="font-size:0.75rem;color:rgb(156 163 175);display:flex;align-items:center;gap:0.25rem;">
                                <x-filament::icon icon="heroicon-o-user" style="width:0.75rem;height:0.75rem;" />
                                <span>{{ is_array($plugin['author']) ? ($plugin['author']['name'] ?? '') : $plugin['author'] }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 操作按钮 --}}
                    <div style="padding:0.75rem 1rem;border-top:1px solid rgb(243 244 246);display:flex;gap:0.5rem;">
                        <button
                            wire:click="showDetail('{{ $plugin['id'] }}')"
                            style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:#fff;color:rgb(55 65 81);border:1px solid rgb(229 231 235);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                            onmouseover="this.style.background='rgb(249 250 251)'"
                            onmouseout="this.style.background='#fff'"
                        >
                            <x-filament::icon icon="heroicon-o-information-circle" style="width:1rem;height:1rem;" />
                            <span>详情</span>
                        </button>

                        @if($installed)
                            <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(240 253 244);color:rgb(22 101 52);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;">
                                <x-filament::icon icon="heroicon-o-check-circle" style="width:1rem;height:1rem;" />
                                <span>已安装</span>
                            </div>
                        @else
                            <button
                                wire:click="installPlugin('{{ $plugin['id'] }}')"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(99 102 241);color:#fff;border:1px solid rgb(99 102 241);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                                onmouseover="this.style.background='rgb(79 70 229)'"
                                onmouseout="this.style.background='rgb(99 102 241)'"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:1rem;height:1rem;" />
                                <span>安装</span>
                            </button>
                        @endif
                    </div>

                    {{-- 操作按钮 --}}
                    <div style="padding:0.75rem 1rem;border-top:1px solid rgb(243 244 246);display:flex;gap:0.5rem;">
                        {{-- 详情按钮（图标） --}}
                        <button
                            wire:click="showDetail('{{ $plugin['id'] }}')"
                            style="display:flex;align-items:center;justify-content:center;background:#fff;color:rgb(55 65 81);border:1px solid rgb(229 231 235);border-radius:0.5rem;padding:0.5rem 0.625rem;font-size:0.8125rem;cursor:pointer;flex-shrink:0;"
                            onmouseover="this.style.background='rgb(249 250 251)'"
                            onmouseout="this.style.background='#fff'"
                            title="查看详情"
                        >
                            <x-filament::icon icon="heroicon-o-information-circle" style="width:1rem;height:1rem;" />
                        </button>

                        @if($installed)
                            <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(240 253 244);color:rgb(22 101 52);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;">
                                <x-filament::icon icon="heroicon-o-check-circle" style="width:1rem;height:1rem;" />
                                <span>已安装</span>
                            </div>
                        @elseif($this->isFree($plugin))
                            <button
                                wire:click="installPlugin('{{ $plugin['id'] }}')"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(99 102 241);color:#fff;border:1px solid rgb(99 102 241);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                                onmouseover="this.style.background='rgb(79 70 229)'"
                                onmouseout="this.style.background='rgb(99 102 241)'"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:1rem;height:1rem;" />
                                <span>免费安装</span>
                            </button>
                        @elseif($this->isPurchased($plugin['id']))
                            <button
                                wire:click="installPlugin('{{ $plugin['id'] }}')"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(99 102 241);color:#fff;border:1px solid rgb(99 102 241);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                                onmouseover="this.style.background='rgb(79 70 229)'"
                                onmouseout="this.style.background='rgb(99 102 241)'"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" style="width:1rem;height:1rem;" />
                                <span>下载安装</span>
                            </button>
                        @else
                            <button
                                wire:click="buyPlugin('{{ $plugin['id'] }}')"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(234 88 12);color:#fff;border:1px solid rgb(234 88 12);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:600;cursor:pointer;"
                                onmouseover="this.style.background='rgb(194 65 12)'"
                                onmouseout="this.style.background='rgb(234 88 12)'"
                            >
                                <x-filament::icon icon="heroicon-o-shopping-cart" style="width:1rem;height:1rem;" />
                                <span>¥{{ number_format(($plugin['price'] ?? 0) / 100, 2) }}</span>
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
