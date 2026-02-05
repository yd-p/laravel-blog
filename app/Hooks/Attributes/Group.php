<?php

namespace App\Hooks\Attributes;

use Attribute;

/**
 * 钩子分组属性注解
 * 
 * 用于设置钩子分组
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Group
{
    public function __construct(
        public string $name
    ) {}
}