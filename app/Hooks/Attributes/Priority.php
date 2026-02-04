<?php

namespace App\Hooks\Attributes;

use Attribute;

/**
 * 钩子优先级属性注解
 * 
 * 用于单独设置钩子优先级
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Priority
{
    public function __construct(
        public int $value
    ) {}
}