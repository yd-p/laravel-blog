<x-filament-panels::page>
    <x-filament::section class="w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Themes</h3>
        </div>
        <x-filament-actions::modals />
        <div class="relative w-full">
            @if(count($themes) < 1)
                暂无主题
            @endif
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3 md:grid-cols-2">
                @foreach($themes as $theme)
                    <div class="overflow-hidden border rounded-md border-neutral-200 dark:border-neutral-700">
                        <img class="w-full h-[200px] object-cover"
                             src="{{ url('lh-core/theme/image') }}/{{ $theme->folder }}"
                             alt="{{ $theme->name }}"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjMwMCIgeT0iMjgwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMjAiIGZpbGw9IiM5Njk2OTYiPlRoZW1lIFByZXZpZXc8L3RleHQ+PC9zdmc+'">

                        <div class="flex items-center justify-between flex-shrink-0 w-full p-4 border-t border-neutral-200 dark:border-neutral-700">
                            <div class="relative flex flex-col">
                                <h4 class="font-semibold">{{ $theme->name }}</h4>
                                <div class="text-xs text-zinc-500 space-y-1 mt-1">
                                    @if(isset($theme->version))
                                        <div>版本: {{ $theme->version }}</div>
                                    @endif
                                    @if(isset($theme->author))
                                        <div>作者: {{ $theme->author }}</div>
                                    @endif
                                    @if(isset($theme->description))
                                        <div class="truncate max-w-xs" title="{{ $theme->description }}">
                                            描述: {{ $theme->description }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="relative flex items-center space-x-1">
                                @if(isset($theme->link) && $theme->link)
                                    <a href="{{ $theme->link }}" target="_blank"
                                       class="flex items-center justify-center w-8 h-8 border rounded-md border-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800 hover:bg-zinc-200"
                                       title="访问主题链接">
                                    </a>
                                @endif
                                <x-filament::button
                                    color="gray"
                                    size="sm"
                                    icon="heroicon-o-cog-6-tooth"
                                    wire:click="mountAction('configureTheme', { theme_id: {{ $theme->id }} })"
                                >
                                    设置
                                </x-filament::button>
                                <button wire:click="deleteTheme('{{ $theme->folder }}')"
                                        wire:confirm="你确定要删除模板{{ $theme->name }}吗？ "
                                        class="flex items-center justify-center w-8 h-8 border rounded-md border-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800 hover:bg-zinc-200"
                                        title="删除主题">
                                </button>
                            </div>
                        </div>

                        <div class="w-full p-4 pt-0">
                            @if($theme->active)
                                <div class="flex justify-center items-center px-2 py-1.5 space-x-1.5 w-full text-sm text-center text-white bg-blue-500 rounded">
                                    <span>已激活</span>
                                </div>
                            @else
                                <button wire:click="activate('{{ $theme->folder }}')"
                                        class="flex justify-center items-center px-2 py-1.5 space-x-1.5 w-full text-sm text-blue-500 rounded border border-neutral-200 dark:border-neutral-700 hover:text-white hover:bg-blue-500 hover:border-blue-600">
                                    <span>激活主题</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
