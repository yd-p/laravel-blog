<?php

namespace App\Hooks\Attributes;

use Attribute;

/**
 * 钩子条件属性注解
 * 
 * 用于设置钩子执行条件
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Condition
{
    public function __construct(
        public string $type,
        public mixed $value = null,
        public string $operator = '='
    ) {}
}