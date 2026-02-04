# 钩子模板库

这里提供了多种钩子模板，涵盖不同的使用场景。选择合适的模板作为起点，然后根据你的业务需求进行定制。

## 📚 可用模板

### 1. 基础模板

#### HookTemplate.php - 完整功能模板
- **适用场景**: 需要完整功能的复杂钩子
- **特性**: 完整的生命周期方法、错误处理、参数验证
- **复杂度**: ⭐⭐⭐⭐
- **推荐用于**: 复杂业务逻辑、需要完整控制的场景

#### SimpleHookTemplate.php - 简单模板
- **适用场景**: 简单的钩子实现
- **特性**: 最小化实现、直接实现接口
- **复杂度**: ⭐
- **推荐用于**: 简单的数据处理、快速原型

#### ClosureHookTemplate.php - 闭包模板
- **适用场景**: 动态注册钩子
- **特性**: 闭包示例、动态注册
- **复杂度**: ⭐⭐
- **推荐用于**: 临时钩子、测试、快速实现

### 2. 专业模板

#### AsyncHookTemplate.php - 异步处理模板
- **适用场景**: 需要异步处理的耗时操作
- **特性**: 
  - 同步/异步模式切换
  - 队列任务分发
  - 性能监控
- **复杂度**: ⭐⭐⭐⭐
- **推荐用于**: 
  - 大数据处理
  - 外部API调用
  - 文件处理
  - 复杂计算

```php
// 使用示例
Hook::execute('async.process', $largeDataset, ['async' => true]);
```

#### ConditionalHookTemplate.php - 条件处理模板
- **适用场景**: 根据不同条件执行不同逻辑
- **特性**:
  - 条件评估引擎
  - 多处理器支持
  - 动态处理器选择
- **复杂度**: ⭐⭐⭐
- **推荐用于**:
  - 业务规则引擎
  - 多分支处理
  - 优先级处理
  - 特殊情况处理

```php
// 使用示例
Hook::execute('conditional.process', $order, ['priority' => 'high']);
```

#### BatchProcessingHookTemplate.php - 批量处理模板
- **适用场景**: 批量处理大量数据
- **特性**:
  - 自动分批
  - 重试机制
  - 错误恢复
  - 进度跟踪
- **复杂度**: ⭐⭐⭐⭐
- **推荐用于**:
  - 数据导入/导出
  - 批量更新
  - 大规模数据处理
  - ETL操作

```php
// 使用示例
Hook::execute('batch.process', $items, [
    'batch_size' => 50,
    'max_retries' => 3
]);
```

#### EventDrivenHookTemplate.php - 事件驱动模板
- **适用场景**: 需要触发和监听Laravel事件
- **特性**:
  - 事件触发
  - 事件监听
  - 事件链
  - 动作处理
- **复杂度**: ⭐⭐⭐
- **推荐用于**:
  - 事件驱动架构
  - 解耦系统
  - 通知系统
  - 工作流引擎

```php
// 使用示例
Hook::execute('event.process', 'create', $userData);
```

#### CacheAwareHookTemplate.php - 缓存感知模板
- **适用场景**: 需要缓存处理结果的钩子
- **特性**:
  - 智能缓存
  - 缓存失效
  - 缓存预热
  - 性能优化
- **复杂度**: ⭐⭐⭐
- **推荐用于**:
  - 计算密集型操作
  - 外部数据获取
  - 频繁查询
  - 性能敏感场景

```php
// 使用示例
Hook::execute('cache.calculate', 'complex_formula', $parameters);
```

#### ValidationHookTemplate.php - 验证模板
- **适用场景**: 数据验证和业务规则检查
- **特性**:
  - Laravel验证器集成
  - 自定义验证规则
  - 预定义规则集
  - 严格模式
- **复杂度**: ⭐⭐⭐
- **推荐用于**:
  - 数据验证
  - 业务规则检查
  - 输入过滤
  - 数据完整性检查

```php
// 使用示例
Hook::execute('validate.user', $userData, 'user_data');
```

#### ViewHookTemplate.php - 视图处理模板
- **适用场景**: 视图渲染前后的处理
- **特性**:
  - 视图生命周期管理
  - 数据注入和预处理
  - 模板路径修改
  - 主题和布局切换
- **复杂度**: ⭐⭐⭐⭐
- **推荐用于**:
  - 视图数据预处理
  - 动态模板切换
  - 主题系统
  - 视图性能优化

```php
// 使用示例
Hook::execute('view.before_render', 'admin.dashboard', $data);
```

#### ViewComposerHookTemplate.php - 视图组合器模板
- **适用场景**: 视图组合器和数据共享
- **特性**:
  - 自动视图组合器注册
  - 数据缓存和验证
  - 延迟加载支持
  - 用户相关数据注入
- **复杂度**: ⭐⭐⭐
- **推荐用于**:
  - 全局视图数据
  - 导航菜单
  - 用户信息共享
  - 系统配置注入

```php
// 使用示例
Hook::execute('view.composer', 'layouts.*', $composerData);
```

## 🚀 快速开始

### 1. 选择模板
根据你的需求选择合适的模板：

```bash
# 复制基础模板
cp app/Hooks/Templates/HookTemplate.php app/Hooks/Custom/MyHook.php

# 或复制专业模板
cp app/Hooks/Templates/AsyncHookTemplate.php app/Hooks/Custom/MyAsyncHook.php
```

### 2. 自定义模板
编辑复制的文件：

1. 修改命名空间为 `App\Hooks\Custom`
2. 修改类名
3. 更新注解中的钩子名称、优先级、分组
4. 实现具体的业务逻辑
5. 根据需要调整配置和方法

### 3. 注册钩子
```bash
php artisan hook discover
```

### 4. 使用钩子
```php
use App\Hooks\Facades\Hook;

$result = Hook::execute('your.hook.name', $data);
```

## 📋 模板选择指南

### 按复杂度选择

| 复杂度 | 模板 | 适用场景 |
|--------|------|----------|
| ⭐ | SimpleHookTemplate | 简单处理、快速实现 |
| ⭐⭐ | ClosureHookTemplate | 动态钩子、测试 |
| ⭐⭐⭐ | ConditionalHookTemplate<br>CacheAwareHookTemplate<br>EventDrivenHookTemplate<br>ValidationHookTemplate<br>ViewComposerHookTemplate | 中等复杂度业务逻辑 |
| ⭐⭐⭐⭐ | HookTemplate<br>AsyncHookTemplate<br>BatchProcessingHookTemplate<br>ViewHookTemplate | 复杂业务逻辑、高性能要求 |

### 按场景选择

| 场景 | 推荐模板 | 说明 |
|------|----------|------|
| 数据验证 | ValidationHookTemplate | 内置Laravel验证器支持 |
| 异步处理 | AsyncHookTemplate | 支持队列和同步模式 |
| 批量操作 | BatchProcessingHookTemplate | 自动分批和错误恢复 |
| 条件处理 | ConditionalHookTemplate | 多分支业务逻辑 |
| 性能优化 | CacheAwareHookTemplate | 智能缓存管理 |
| 事件驱动 | EventDrivenHookTemplate | Laravel事件集成 |
| 视图处理 | ViewHookTemplate | 视图生命周期管理 |
| 视图组合 | ViewComposerHookTemplate | 视图数据共享 |
| 通用场景 | HookTemplate | 完整功能支持 |
| 简单场景 | SimpleHookTemplate | 最小化实现 |

## 🛠️ 自定义模板

你也可以基于现有模板创建自己的模板：

1. **组合模板**: 将多个模板的特性组合到一个钩子中
2. **扩展模板**: 在现有模板基础上添加新功能
3. **专业模板**: 为特定业务场景创建专门的模板

### 创建自定义模板示例

```php
<?php

namespace App\Hooks\Custom;

use App\Hooks\Templates\CacheAwareHookTemplate;
use App\Hooks\Templates\ValidationHookTemplate;

/**
 * 组合模板示例：缓存 + 验证
 * 
 * @hook custom.cache.validation
 * @priority 10
 * @group custom
 */
class CacheValidationHook extends CacheAwareHookTemplate
{
    use ValidationTrait; // 可以创建trait来复用验证功能
    
    public function handle(...$args)
    {
        // 先验证数据
        $validationResult = $this->validateData($args[0]);
        
        if (!$validationResult['valid']) {
            return $validationResult;
        }
        
        // 然后使用缓存处理
        return parent::handle(...$args);
    }
}
```

## 📖 最佳实践

1. **选择合适的模板**: 根据实际需求选择，避免过度设计
2. **保持简单**: 从简单模板开始，逐步增加复杂性
3. **复用代码**: 使用trait或继承来复用通用功能
4. **文档化**: 为你的钩子添加清晰的文档和注释
5. **测试**: 为每个钩子编写测试用例
6. **性能考虑**: 根据性能需求选择合适的模板

## 🔗 相关资源

- [钩子系统文档](../README.md)
- [快速开始指南](../QUICKSTART.md)
- [用户实现指南](../USER_GUIDE.md)
- [视图钩子指南](../VIEW_HOOKS_GUIDE.md)
- [测试用例](../Tests/)

---

选择合适的模板，开始构建你的钩子系统吧！🚀