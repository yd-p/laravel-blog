<x-filament-panels::page>
    <x-filament::section class="w-full">
        <div class="relative w-full">
            @if(count($plugins) < 1)
                暂无插件
            @endif

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3 md:grid-cols-2">
                @foreach($plugins as $pluginFolder => $plugin)
                    <div class="overflow-hidden border rounded-md border-neutral-200 dark:border-neutral-700">
                        <img class="relative" src="{{ url('lh-core/plugin/image' ) }}/{{ $pluginFolder }}">
                        <div
                            class="flex items-center justify-between flex-shrink-0 w-full p-4 border-b border-neutral-200 dark:border-neutral-700">
                            <div class="relative flex flex-col pr-3">
                                <h4 class="font-semibold">{{ $plugin['name'] }}</h4>
                                <p class="text-xs text-zinc-500">{{ $plugin['description'] }}</p>
                                <p class="text-xs text-zinc-500">{{ 'Version ' . ($plugin['version'] ?? '') }}</p>
                            </div>
                            <div class="relative flex items-center space-x-1">
                                <div class="flex items-center justify-center w-8 h-8 border rounded-md border-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800 hover:bg-zinc-200 transition-all duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)] transform hover:scale-[1.02]">
                                    {{ ($this->deletePluginAction)($plugin) }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center w-full p-4 space-x-2">
                            @if($plugin['active'])
                                <div
                                    class="flex justify-center items-center px-2 py-1.5 space-x-1.5 w-full text-sm text-center text-white bg-blue-500 rounded">
                                    <span>插件已启用</span>
                                </div>
                                <button wire:click="mountAction('disablePluginAction',{{json_encode($plugin)}})" class="flex justify-center items-center px-2 py-1.5 space-x-1.5 w-full text-sm text-red-500 hover:text-white bg-transparent hover:bg-red-500 rounded border border-neutral-200 dark:border-neutral-700 hover:border-red-600 transition-all duration-300 ease-out transform hover:scale-[1.02] active:scale-95 shadow-sm hover:shadow-red-200/40 dark:hover:shadow-red-800/30">
                                    <span>禁用插件</span>
                                </button>
                            @else
                                <button wire:click="mountAction('activePluginAction', {{json_encode($plugin)}})" class="flex justify-center items-center px-2 py-1.5 space-x-1.5 w-full text-sm text-blue-500 hover:text-white bg-transparent hover:bg-blue-500 rounded border border-neutral-200 dark:border-neutral-700 hover:border-blue-600 transition-all duration-300 ease-[cubic-bezier(0.25,0.1,0.25,1)] transform hover:scale-[1.02] active:scale-95 shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-800/30">
                                    <span>启用插件</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
