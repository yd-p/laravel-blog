<x-filament-panels::page>

    @if(count($plugins) < 1)
        {{-- 空状态 --}}
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 2rem;text-align:center;">
            <div style="width:4rem;height:4rem;background:rgb(243 244 246);border-radius:9999px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                <x-filament::icon icon="heroicon-o-puzzle-piece" style="width:2rem;height:2rem;color:rgb(156 163 175);" />
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:rgb(17 24 39);margin-bottom:0.25rem;">暂无插件</h3>
            <p style="font-size:0.875rem;color:rgb(107 114 128);">将插件文件夹放入 <code style="background:rgb(243 244 246);padding:0.125rem 0.375rem;border-radius:0.25rem;font-size:0.8125rem;">plugins/</code> 目录后刷新页面</p>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
            @foreach($plugins as $pluginFolder => $plugin)
                @php $active = $plugin['active'] ?? false; @endphp
                <div style="background:#fff;border-radius:0.75rem;border:1px solid rgb(229 231 235);overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .15s;">

                    {{-- 封面图 --}}
                    <div style="position:relative;background:linear-gradient(135deg,rgb(249 250 251),rgb(243 244 246));height:140px;overflow:hidden;">
                        <img
                            src="{{ url('lh-core/plugin/image') }}/{{ $pluginFolder }}"
                            alt="{{ $plugin['name'] }}"
                            style="width:100%;height:100%;object-fit:cover;"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                        >
                        <div style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;background:linear-gradient(135deg,rgb(238 242 255),rgb(224 231 255));">
                            <x-filament::icon icon="heroicon-o-puzzle-piece" style="width:3rem;height:3rem;color:rgb(129 140 248);" />
                        </div>

                        {{-- 状态徽章 --}}
                        <div style="position:absolute;top:0.625rem;right:0.625rem;">
                            @if($active)
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(220 252 231);color:rgb(22 101 52);border:1px solid rgb(187 247 208);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    <span style="width:6px;height:6px;background:rgb(34 197 94);border-radius:9999px;display:inline-block;"></span>
                                    已启用
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:0.25rem;background:rgb(243 244 246);color:rgb(107 114 128);border:1px solid rgb(229 231 235);border-radius:9999px;padding:0.2rem 0.625rem;font-size:0.75rem;font-weight:600;">
                                    <span style="width:6px;height:6px;background:rgb(156 163 175);border-radius:9999px;display:inline-block;"></span>
                                    已禁用
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 插件信息 --}}
                    <div style="padding:1rem;flex:1;display:flex;flex-direction:column;gap:0.5rem;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">
                            <div style="flex:1;min-width:0;">
                                <h4 style="font-size:0.9375rem;font-weight:600;color:rgb(17 24 39);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $plugin['name'] }}
                                </h4>
                                @if(!empty($plugin['version']))
                                    <span style="font-size:0.75rem;color:rgb(107 114 128);">v{{ $plugin['version'] }}</span>
                                @endif
                            </div>
                            {{-- 删除按钮 --}}
                            <div style="flex-shrink:0;">
                                {{ ($this->deletePluginAction)(['folder' => $pluginFolder, 'name' => $plugin['name']]) }}
                            </div>
                        </div>

                        @if(!empty($plugin['description']))
                            <p style="font-size:0.8125rem;color:rgb(107 114 128);line-height:1.5;flex:1;">
                                {{ $plugin['description'] }}
                            </p>
                        @endif

                        @if(!empty($plugin['author']))
                            <div style="display:flex;align-items:center;gap:0.375rem;font-size:0.75rem;color:rgb(156 163 175);">
                                <x-filament::icon icon="heroicon-o-user" style="width:0.875rem;height:0.875rem;" />
                                <span>{{ is_array($plugin['author']) ? ($plugin['author']['name'] ?? '') : $plugin['author'] }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 操作按钮 --}}
                    <div style="padding:0.75rem 1rem;border-top:1px solid rgb(243 244 246);display:flex;gap:0.5rem;">
                        @if($active)
                            <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(239 246 255);color:rgb(37 99 235);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;">
                                <x-filament::icon icon="heroicon-o-check-circle" style="width:1rem;height:1rem;" />
                                <span>运行中</span>
                            </div>
                            @if(!empty($plugin['settings_page']))
                                <a
                                    href="{{ filament()->getPanel('admin')->getUrl() }}/{{ $plugin['settings_page'] }}"
                                    style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:#fff;color:rgb(79 70 229);border:1px solid rgb(199 210 254);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;text-decoration:none;transition:background .15s;"
                                    onmouseover="this.style.background='rgb(238 242 255)'"
                                    onmouseout="this.style.background='#fff'"
                                >
                                    <x-filament::icon icon="heroicon-o-cog-6-tooth" style="width:1rem;height:1rem;" />
                                    <span>配置</span>
                                </a>
                            @endif
                            <button
                                wire:click="mountAction('disablePluginAction', {{ json_encode(['folder' => $pluginFolder, 'name' => $plugin['name']]) }})"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:#fff;color:rgb(239 68 68);border:1px solid rgb(254 202 202);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='rgb(254 242 242)'"
                                onmouseout="this.style.background='#fff'"
                            >
                                <x-filament::icon icon="heroicon-o-x-circle" style="width:1rem;height:1rem;" />
                                <span>禁用</span>
                            </button>
                        @else
                            <button
                                wire:click="mountAction('activePluginAction', {{ json_encode(['folder' => $pluginFolder, 'name' => $plugin['name']]) }})"
                                style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.375rem;background:rgb(37 99 235);color:#fff;border:1px solid rgb(37 99 235);border-radius:0.5rem;padding:0.5rem;font-size:0.8125rem;font-weight:500;cursor:pointer;transition:background .15s;"
                                onmouseover="this.style.background='rgb(29 78 216)'"
                                onmouseout="this.style.background='rgb(37 99 235)'"
                            >
                                <x-filament::icon icon="heroicon-o-bolt" style="width:1rem;height:1rem;" />
                                <span>启用</span>
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
