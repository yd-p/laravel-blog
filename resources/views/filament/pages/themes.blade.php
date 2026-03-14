<x-filament-panels::page>

    @if(count($themes) < 1)
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 2rem;text-align:center;">
            <div style="width:4rem;height:4rem;background:rgb(243 244 246);border-radius:9999px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <x-filament::icon icon="heroicon-o-swatch" style="width:2rem;height:2rem;color:rgb(156 163 175);" />
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:rgb(17 24 39);margin-bottom:0.25rem;">暂无主题</h3>
            <p style="font-size:0.875rem;color:rgb(107 114 128);">将主题文件夹放入 <code style="background:rgb(243 244 246);padding:0.125rem 0.375rem;border-radius:0.25rem;font-size:0.8125rem;">themes/</code> 目录后刷新页面</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
            @foreach($themes as $theme)
                <div style="background:#fff;border-radius:0.75rem;border:1px solid rgb(229 231 235);overflow:hidden;display:flex;flex-direction:column;">

                    {{-- 封面图 --}}
                    <div style="position:relative;background:linear-gradient(135deg,rgb(249 250 251),rgb(243 244 246));height:160px;overflow:hidden;">
                        <img
                            src="{{ url('lh-core/theme/image') }}/{{ $theme->folder }}"
                            alt="{{ $theme->name }}"
                            style="width:100%;height:100%;object-fit:cover;"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                        >
                        <div style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;background:linear-gradient(135deg,rgb(240 253 244),rgb(220 252 231));">
                            <x-filament::icon icon="heroicon-o-swatch" style="width:3rem;height:3rem;color:rgb(74 222 128);" />
                        </div>

                        {{-- 状态徽章 --}}
                        <div style="position:absolute;top:0.625rem;right:0.625rem;">
                            @if($theme->active)
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(220 252 231);color:rgb(22 101 52);border:1px solid rgb(187 247 208);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    <span style="width:6px;height:6px;background:rgb(34 197 94);border-radius:9999px;display:inline-block;"></span>
                                    已激活
                                </span>
                            @elseif(isset($theme->default) && $theme->default)
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(239 246 255);color:rgb(29 78 216);border:1px solid rgb(191 219 254);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    默认
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(243 244 246);color:rgb(107 114 128);border:1px solid rgb(229 231 235);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    <span style="width:6px;height:6px;background:rgb(156 163 175);border-radius:9999px;display:inline-block;"></span>
                                    未激活
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 主题信息 --}}
                    <div style="padding:1rem;flex:1;display:flex;flex-direction:column;gap:0.375rem;">
                        <h4 style="font-size:0.9375rem;font-weight:600;color:rgb(17 24 39);">{{ $theme->name }}</h4>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
                            @if(isset($theme->version) && $theme->version)
                                <span style="font-size:0.75rem;color:rgb(107 114 128);background:rgb(243 244 246);padding:0.125rem 0.5rem;border-radius:9999px;">v{{ $theme->version }}</span>
                            @endif
                            @if(isset($theme->author) && $theme->author)
                                <span style="font-size:0.75rem;color:rgb(107 114 128);display:flex;align-items:center;gap:0.25rem;">
                                    <x-filament::icon icon="heroicon-o-user" style="width:0.75rem;height:0.75rem;" />
                                    {{ $theme->author }}
                                </span>
                            @endif
                        </div>
                        @if(isset($theme->description) && $theme->description)
                            <p style="font-size:0.8125rem;color:rgb(107 114 128);line-height:1.5;margin-top:0.25rem;">{{ $theme->description }}</p>
                        @endif
                    </div>

                    {{-- 操作按钮 --}}
                    <div style="padding:0.75rem 1rem;border-top:1px solid rgb(243 244 246);display:flex;gap:0.5rem;align-items:center;">
                        @if($theme->active)
                            <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(240 253 244);color:rgb(22 101 52);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;">
                                <x-filament::icon icon="heroicon-o-check-circle" style="width:1rem;height:1rem;" />
                                <span>当前主题</span>
                            </div>
                        @else
                            <button
                                wire:click="activate('{{ $theme->folder }}')"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(37 99 235);color:#fff;border:1px solid rgb(37 99 235);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                                onmouseover="this.style.background='rgb(29 78 216)'"
                                onmouseout="this.style.background='rgb(37 99 235)'"
                            >
                                <x-filament::icon icon="heroicon-o-bolt" style="width:1rem;height:1rem;" />
                                <span>激活</span>
                            </button>
                        @endif

                        <button
                            wire:click="mountAction('configureTheme', { theme_id: {{ $theme->id }} })"
                            style="display:flex;align-items:center;justify-content:center;gap:0.25rem;background:#fff;color:rgb(55 65 81);border:1px solid rgb(229 231 235);border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.8125rem;font-weight:500;cursor:pointer;"
                            onmouseover="this.style.background='rgb(249 250 251)'"
                            onmouseout="this.style.background='#fff'"
                            title="配置主题"
                        >
                            <x-filament::icon icon="heroicon-o-cog-6-tooth" style="width:1rem;height:1rem;" />
                        </button>

                        @if(!isset($theme->default) || !$theme->default)
                            <button
                                wire:click="deleteTheme('{{ $theme->folder }}')"
                                wire:confirm="确定要删除主题「{{ $theme->name }}」吗？"
                                style="display:flex;align-items:center;justify-content:center;background:#fff;color:rgb(239 68 68);border:1px solid rgb(254 202 202);border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.8125rem;cursor:pointer;"
                                onmouseover="this.style.background='rgb(254 242 242)'"
                                onmouseout="this.style.background='#fff'"
                                title="删除主题"
                            >
                                <x-filament::icon icon="heroicon-o-trash" style="width:1rem;height:1rem;" />
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
