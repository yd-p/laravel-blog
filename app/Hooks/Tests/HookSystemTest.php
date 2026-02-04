<?php

namespace App\Hooks\Tests;

use App\Hooks\HookManager;
use App\Hooks\Facades\Hook;
use Illuminate\Foundation\Testing\TestCase;

/**
 * 钩子系统测试
 * 
 * 运行测试: php artisan test app/Hooks/Tests/HookSystemTest.php
 */
class HookSystemTest extends TestCase
{
    protected HookManager $hookManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hookManager = app(HookManager::class);
        $this->hookManager->clearCache(); // 清除缓存确保测试环境干净
    }

    /** @test */
    public function it_can_register_and_execute_simple_hook()
    {
        // 注册钩子
        $hookId = Hook::register('test.simple', function ($message) {
            return "处理消息: {$message}";
        });

        $this->assertNotEmpty($hookId);

        // 执行钩子
        $result = Hook::execute('test.simple', 'Hello World');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(1, $result->getExecutedCount());
        $this->assertEquals('处理消息: Hello World', $result->getFirstResult());
    }

    /** @test */
    public function it_can_register_multiple_hooks_with_priority()
    {
        // 注册多个钩子，不同优先级
        Hook::register('test.priority', function () {
            return 'second';
        }, 20);

        Hook::register('test.priority', function () {
            return 'first';
        }, 10);

        Hook::register('test.priority', function () {
            return 'third';
        }, 30);

        // 执行钩子
        $result = Hook::execute('test.priority');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(3, $result->getExecutedCount());

        $results = array_values($result->getResults());
        $this->assertEquals('first', $results[0]);
        $this->assertEquals('second', $results[1]);
        $this->assertEquals('third', $results[2]);
    }

    /** @test */
    public function it_can_batch_register_hooks()
    {
        $hooks = [
            'test.batch1' => function () { return 'batch1'; },
            'test.batch2' => [
                'callback' => function () { return 'batch2'; },
                'priority' => 5,
                'group' => 'test'
            ]
        ];

        $hookIds = Hook::registerBatch($hooks);

        $this->assertCount(2, $hookIds);

        // 测试执行
        $result1 = Hook::execute('test.batch1');
        $result2 = Hook::execute('test.batch2');

        $this->assertEquals('batch1', $result1->getFirstResult());
        $this->assertEquals('batch2', $result2->getFirstResult());
    }

    /** @test */
    public function it_can_toggle_hook_enabled_state()
    {
        // 注册钩子
        $hookId = Hook::register('test.toggle', function () {
            return 'enabled';
        });

        // 初始状态应该是启用的
        $result = Hook::execute('test.toggle');
        $this->assertEquals(1, $result->getExecutedCount());

        // 禁用钩子
        $this->assertTrue(Hook::toggle('test.toggle', $hookId, false));

        // 再次执行应该没有结果
        $result = Hook::execute('test.toggle');
        $this->assertEquals(0, $result->getExecutedCount());

        // 重新启用
        $this->assertTrue(Hook::toggle('test.toggle', $hookId, true));

        // 应该能正常执行
        $result = Hook::execute('test.toggle');
        $this->assertEquals(1, $result->getExecutedCount());
    }

    /** @test */
    public function it_can_remove_hooks()
    {
        // 注册多个钩子
        $hookId1 = Hook::register('test.remove', function () { return 'hook1'; });
        $hookId2 = Hook::register('test.remove', function () { return 'hook2'; });

        // 执行应该有2个结果
        $result = Hook::execute('test.remove');
        $this->assertEquals(2, $result->getExecutedCount());

        // 移除一个钩子
        $this->assertTrue(Hook::remove('test.remove', $hookId1));

        // 执行应该只有1个结果
        $result = Hook::execute('test.remove');
        $this->assertEquals(1, $result->getExecutedCount());

        // 移除所有钩子
        $this->assertTrue(Hook::remove('test.remove'));

        // 执行应该没有结果
        $result = Hook::execute('test.remove');
        $this->assertEquals(0, $result->getExecutedCount());
    }

    /** @test */
    public function it_can_remove_hooks_by_group()
    {
        // 注册不同分组的钩子
        Hook::register('test.group1', function () { return 'group1'; }, 10, 'group_a');
        Hook::register('test.group2', function () { return 'group2'; }, 10, 'group_a');
        Hook::register('test.group3', function () { return 'group3'; }, 10, 'group_b');

        // 移除group_a分组的钩子
        $removedCount = Hook::removeByGroup('group_a');
        $this->assertEquals(2, $removedCount);

        // group_a的钩子应该被移除
        $result1 = Hook::execute('test.group1');
        $result2 = Hook::execute('test.group2');
        $this->assertEquals(0, $result1->getExecutedCount());
        $this->assertEquals(0, $result2->getExecutedCount());

        // group_b的钩子应该还在
        $result3 = Hook::execute('test.group3');
        $this->assertEquals(1, $result3->getExecutedCount());
    }

    /** @test */
    public function it_can_get_hook_statistics()
    {
        // 注册一些钩子
        Hook::register('test.stats1', function () { return 'stats1'; }, 10, 'stats');
        Hook::register('test.stats2', function () { return 'stats2'; }, 10, 'stats');
        Hook::register('test.stats3', function () { return 'stats3'; }, 10, 'other');

        // 执行一些钩子
        Hook::execute('test.stats1');
        Hook::execute('test.stats1');
        Hook::execute('test.stats2');

        $stats = Hook::getStats();

        $this->assertGreaterThanOrEqual(3, $stats['total_hooks']);
        $this->assertGreaterThanOrEqual(3, $stats['enabled_hooks']);
        $this->assertGreaterThanOrEqual(3, $stats['total_calls']);
        $this->assertArrayHasKey('stats', $stats['groups']);
        $this->assertArrayHasKey('other', $stats['groups']);
    }

    /** @test */
    public function it_handles_hook_execution_errors_gracefully()
    {
        // 注册一个会抛出异常的钩子
        Hook::register('test.error', function () {
            throw new \Exception('测试异常');
        });

        // 注册一个正常的钩子
        Hook::register('test.error', function () {
            return 'success';
        });

        // 执行钩子
        $result = Hook::execute('test.error');

        // 应该有一个成功，一个失败
        $this->assertEquals(1, $result->getExecutedCount());
        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->getErrors());
        $this->assertEquals('success', $result->getFirstResult());
    }

    /** @test */
    public function it_can_add_and_execute_middleware()
    {
        $middlewareExecuted = false;

        // 添加中间件
        Hook::addMiddleware('test.middleware', function ($hookName, $hookId, $args) use (&$middlewareExecuted) {
            $middlewareExecuted = true;
            return true; // 允许执行
        });

        // 注册钩子
        Hook::register('test.middleware', function () {
            return 'middleware_test';
        });

        // 执行钩子
        $result = Hook::execute('test.middleware');

        $this->assertTrue($middlewareExecuted);
        $this->assertTrue($result->isSuccessful());
    }

    /** @test */
    public function middleware_can_block_hook_execution()
    {
        // 添加阻止执行的中间件
        Hook::addMiddleware('test.blocked', function ($hookName, $hookId, $args) {
            return false; // 阻止执行
        });

        // 注册钩子
        Hook::register('test.blocked', function () {
            return 'should_not_execute';
        });

        // 执行钩子
        $result = Hook::execute('test.blocked');

        // 应该没有执行任何钩子
        $this->assertEquals(0, $result->getExecutedCount());
        $this->assertEmpty($result->getResults());
    }

    /** @test */
    public function it_can_get_hooks_by_name_and_group()
    {
        // 注册钩子
        Hook::register('test.filter', function () { return 'test1'; }, 10, 'group1');
        Hook::register('test.filter', function () { return 'test2'; }, 10, 'group2');
        Hook::register('other.hook', function () { return 'other'; }, 10, 'group1');

        // 按名称获取
        $hooksByName = Hook::getHooks('test.filter');
        $this->assertCount(2, $hooksByName);

        // 按分组获取
        $hooksByGroup = Hook::getHooks(null, 'group1');
        $this->assertGreaterThanOrEqual(2, count($hooksByGroup));
    }

    protected function tearDown(): void
    {
        // 清理测试数据
        $this->hookManager->clearCache();
        parent::tearDown();
    }
}