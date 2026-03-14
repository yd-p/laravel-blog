<?php

namespace App\Filament\Pages;

use App\Models\PluginOrder;
use App\Services\PaymentService;
use App\Services\PluginMarketService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class PluginMarket extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = '插件市场';
    protected static string|\UnitEnum|null $navigationGroup = '系统设置';
    protected static ?int $navigationSort = 29;

    public string $search = '';
    public string $activeCategory = 'all';
    public array $marketPlugins = [];
    public array $categories = [];
    public array $installedFolders = [];
    public array $purchasedPluginIds = [];
    public ?array $detailPlugin = null;
    // 当前待支付订单（用于展示二维码轮询）
    public ?string $pendingOrderNo = null;
    public string $pendingPayMethod = '';
    public string $wechatQrcodeUrl = '';

    public function getView(): string
    {
        return 'filament.pages.plugin-market';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function mount(): void
    {
        $this->loadCategories();
        $this->loadPlugins();
        $this->loadInstalledFolders();
        $this->loadPurchasedPlugins();
    }

    // -------------------------------------------------------------------------
    // 数据加载
    // -------------------------------------------------------------------------

    private function loadCategories(): void
    {
        $this->categories = app(PluginMarketService::class)->getCategories();
    }

    public function loadPlugins(): void
    {
        $this->marketPlugins = app(PluginMarketService::class)
            ->getMarketPlugins($this->activeCategory === 'all' ? '' : $this->activeCategory, $this->search);
        $this->loadInstalledFolders();
    }

    private function loadInstalledFolders(): void
    {
        $pluginRoot = base_path('plugins');
        $this->installedFolders = [];
        if (!is_dir($pluginRoot)) return;
        foreach (new \DirectoryIterator($pluginRoot) as $item) {
            if ($item->isDot() || !$item->isDir()) continue;
            $this->installedFolders[] = $item->getFilename();
        }
    }

    private function loadPurchasedPlugins(): void
    {
        $this->purchasedPluginIds = PluginOrder::where('user_id', auth()->id())
            ->where('status', PluginOrder::STATUS_PAID)
            ->pluck('plugin_id')
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // 筛选 / 搜索
    // -------------------------------------------------------------------------

    public function filterByCategory(string $category): void
    {
        $this->activeCategory = $category;
        $this->loadPlugins();
    }

    public function updatedSearch(): void
    {
        $this->loadPlugins();
    }

    public function refreshCache(): void
    {
        app(PluginMarketService::class)->clearCache();
        $this->loadCategories();
        $this->loadPlugins();
        Notification::make()->title('缓存已刷新')->success()->send();
    }

    // -------------------------------------------------------------------------
    // 状态判断
    // -------------------------------------------------------------------------

    public function isInstalled(string $folder): bool
    {
        return in_array($folder, $this->installedFolders, true);
    }

    public function isPurchased(string $pluginId): bool
    {
        return in_array($pluginId, $this->purchasedPluginIds, true);
    }

    public function isFree(array $plugin): bool
    {
        return empty($plugin['price']) || (int)$plugin['price'] === 0;
    }

    // -------------------------------------------------------------------------
    // 详情 Modal
    // -------------------------------------------------------------------------

    public function showDetail(string $pluginId): void
    {
        $this->detailPlugin = app(PluginMarketService::class)->getPluginDetail($pluginId);
        $this->mountAction('pluginDetailAction');
    }

    public function pluginDetailAction(): Action
    {
        return Action::make('pluginDetailAction')
            ->label('插件详情')
            ->modalHeading(fn () => $this->detailPlugin['name'] ?? '插件详情')
            ->modalCancelActionLabel('关闭')
            ->modalSubmitActionLabel(function () {
                if (!$this->detailPlugin) return '关闭';
                $plugin = $this->detailPlugin;
                if ($this->isInstalled($plugin['folder'] ?? '')) return '已安装';
                if ($this->isFree($plugin)) return '免费安装';
                if ($this->isPurchased($plugin['id'])) return '下载安装';
                return '立即购买 ¥' . number_format(($plugin['price'] ?? 0) / 100, 2);
            })
            ->modalWidth('2xl')
            ->action(function () {
                if (!$this->detailPlugin) return;
                $plugin = $this->detailPlugin;
                if ($this->isInstalled($plugin['folder'] ?? '')) return;

                if ($this->isFree($plugin) || $this->isPurchased($plugin['id'])) {
                    $this->doInstall($plugin);
                } else {
                    // 跳转到购买流程
                    $this->mountAction('purchaseAction');
                }
            })
            ->modalContent(fn () => view('filament.modals.plugin-detail', [
                'plugin'    => $this->detailPlugin ?? [],
                'installed' => $this->isInstalled($this->detailPlugin['folder'] ?? ''),
                'purchased' => $this->isPurchased($this->detailPlugin['id'] ?? ''),
                'free'      => $this->isFree($this->detailPlugin ?? []),
            ]));
    }

    // -------------------------------------------------------------------------
    // 购买 Action
    // -------------------------------------------------------------------------

    public function buyPlugin(string $pluginId): void
    {
        $plugin = app(PluginMarketService::class)->getPluginDetail($pluginId);
        if (!$plugin) {
            Notification::make()->title('插件信息获取失败')->danger()->send();
            return;
        }
        $this->detailPlugin = $plugin;
        $this->mountAction('purchaseAction');
    }

    public function purchaseAction(): Action
    {
        return Action::make('purchaseAction')
            ->label('购买插件')
            ->modalHeading(fn () => '购买「' . ($this->detailPlugin['name'] ?? '') . '」')
            ->modalWidth('lg')
            ->modalSubmitActionLabel('确认支付')
            ->modalCancelActionLabel('取消')
            ->form([
                Radio::make('payment_method')
                    ->label('选择支付方式')
                    ->options([
                        'alipay' => '支付宝',
                        'wechat' => '微信支付',
                    ])
                    ->default('alipay')
                    ->required(),
            ])
            ->modalContent(fn () => view('filament.modals.purchase-confirm', [
                'plugin' => $this->detailPlugin ?? [],
            ]))
            ->action(function (array $data) {
                $plugin = $this->detailPlugin;
                if (!$plugin) return;

                // 检查是否已有待支付订单
                $existing = PluginOrder::where('user_id', auth()->id())
                    ->where('plugin_id', $plugin['id'])
                    ->where('status', PluginOrder::STATUS_PENDING)
                    ->first();

                $order = $existing ?? PluginOrder::create([
                    'order_no'       => PluginOrder::generateOrderNo(),
                    'user_id'        => auth()->id(),
                    'plugin_id'      => $plugin['id'],
                    'plugin_name'    => $plugin['name'],
                    'plugin_folder'  => $plugin['folder'] ?? '',
                    'plugin_version' => $plugin['version'] ?? '',
                    'amount'         => (int)($plugin['price'] ?? 0),
                    'download_url'   => $plugin['download_url'] ?? '',
                ]);

                $method = $data['payment_method'];

                if ($method === 'alipay') {
                    // 支付宝：跳转到支付页
                    $this->redirect(route('payment.alipay.pay', $order->order_no));
                } else {
                    // 微信：获取二维码 URL，展示扫码 Modal
                    try {
                        $codeUrl = app(PaymentService::class)->wechatNative($order);
                        $this->pendingOrderNo  = $order->order_no;
                        $this->pendingPayMethod = 'wechat';
                        $this->wechatQrcodeUrl  = $codeUrl;
                        $this->mountAction('wechatQrcodeAction');
                    } catch (\Throwable $e) {
                        Notification::make()->title('微信支付初始化失败')->body($e->getMessage())->danger()->send();
                    }
                }
            });
    }

    // -------------------------------------------------------------------------
    // 微信扫码 Modal
    // -------------------------------------------------------------------------

    public function wechatQrcodeAction(): Action
    {
        return Action::make('wechatQrcodeAction')
            ->label('微信扫码支付')
            ->modalHeading('微信扫码支付')
            ->modalCancelActionLabel('我已完成支付')
            ->modalSubmitAction(false)
            ->modalWidth('sm')
            ->modalContent(fn () => view('filament.modals.wechat-qrcode', [
                'orderNo'  => $this->pendingOrderNo,
                'codeUrl'  => $this->wechatQrcodeUrl,
                'amount'   => $this->detailPlugin ? number_format(($this->detailPlugin['price'] ?? 0) / 100, 2) : '0.00',
            ]))
            ->action(function () {
                // 用户点"我已完成支付"时轮询一次
                $this->checkWechatPayment();
            });
    }

    public function checkWechatPayment(): void
    {
        if (!$this->pendingOrderNo) return;

        $order = PluginOrder::where('order_no', $this->pendingOrderNo)
            ->where('user_id', auth()->id())
            ->first();

        if ($order && $order->isPaid()) {
            $this->loadPurchasedPlugins();
            Notification::make()->title('支付成功')->body('插件已解锁，可立即安装')->success()->send();
            $this->pendingOrderNo = null;
        } else {
            Notification::make()->title('尚未检测到支付')->body('请完成扫码支付后再点击确认')->warning()->send();
        }
    }

    // -------------------------------------------------------------------------
    // 安装逻辑
    // -------------------------------------------------------------------------

    public function installPlugin(string $pluginId): void
    {
        $plugin = app(PluginMarketService::class)->getPluginDetail($pluginId);
        if (!$plugin) {
            Notification::make()->title('插件信息获取失败')->danger()->send();
            return;
        }

        // 付费插件需先购买
        if (!$this->isFree($plugin) && !$this->isPurchased($pluginId)) {
            $this->detailPlugin = $plugin;
            $this->mountAction('purchaseAction');
            return;
        }

        $this->doInstall($plugin);
    }

    protected function doInstall(array $plugin): void
    {
        $downloadUrl = $plugin['download_url'] ?? '';

        if (empty($downloadUrl)) {
            Notification::make()->title('暂无下载地址')->body('该插件尚未提供下载包')->warning()->send();
            return;
        }

        $result = app(PluginMarketService::class)->installFromUrl($downloadUrl, $plugin['folder'] ?? '');

        if ($result['success']) {
            $this->loadInstalledFolders();
            Notification::make()->title('安装成功')->body($result['message'])->success()->send();
        } else {
            Notification::make()->title('安装失败')->body($result['message'])->danger()->send();
        }
    }

    // -------------------------------------------------------------------------
    // 上传 / URL 安装
    // -------------------------------------------------------------------------

    public function uploadInstallAction(): Action
    {
        return Action::make('uploadInstallAction')
            ->label('上传安装')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('上传插件 ZIP 包')
            ->modalDescription('根目录需包含 plugin.json 文件。')
            ->modalSubmitActionLabel('立即安装')
            ->modalCancelActionLabel('取消')
            ->form([
                FileUpload::make('zip_file')
                    ->label('插件压缩包')
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                    ->maxSize(51200)
                    ->required()
                    ->disk('local')
                    ->directory('plugin-uploads'),
            ])
            ->action(function (array $data) {
                $path = storage_path('app/' . $data['zip_file']);
                $result = app(PluginMarketService::class)->installFromZip($path);
                if (file_exists($path)) @unlink($path);

                if ($result['success']) {
                    $this->loadInstalledFolders();
                    Notification::make()->title('安装成功')->body($result['message'])->success()->send();
                } else {
                    Notification::make()->title('安装失败')->body($result['message'])->danger()->send();
                }
            });
    }

    public function urlInstallAction(): Action
    {
        return Action::make('urlInstallAction')
            ->label('URL 安装')
            ->icon('heroicon-o-link')
            ->color('gray')
            ->modalHeading('通过 URL 安装插件')
            ->modalSubmitActionLabel('下载并安装')
            ->modalCancelActionLabel('取消')
            ->form([
                TextInput::make('download_url')
                    ->label('ZIP 下载地址')
                    ->url()
                    ->required()
                    ->placeholder('https://example.com/plugin.zip'),
                TextInput::make('plugin_folder')
                    ->label('插件目录名（可选）')
                    ->placeholder('留空则自动从 plugin.json 读取'),
            ])
            ->action(function (array $data) {
                $result = app(PluginMarketService::class)->installFromUrl(
                    $data['download_url'],
                    $data['plugin_folder'] ?? ''
                );
                if ($result['success']) {
                    $this->loadInstalledFolders();
                    Notification::make()->title('安装成功')->body($result['message'])->success()->send();
                } else {
                    Notification::make()->title('安装失败')->body($result['message'])->danger()->send();
                }
            });
    }
}
