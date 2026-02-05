<?php

namespace App\Hooks\Attributes;

use Attribute;

/**
 * 钩子中间件属性注解
 * 
 * 用于为钩子指定中间件
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Middleware
{
    public function __construct(
        public string $class,
        public array $parameters = []
    ) {}
}