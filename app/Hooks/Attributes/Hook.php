<?php

namespace App\Hooks\Attributes;

use Attribute;

/**
 * 钩子属性注解
 * 
 * 用于标记钩子类的基本信息
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Hook
{
    public function __construct(
        public string $name,
        public int $priority = 10,
        public ?string $group = null,
        public ?string $description = null,
        public bool $enabled = true
    ) {}
}