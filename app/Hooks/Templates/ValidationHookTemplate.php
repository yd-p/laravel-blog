<?php

namespace App\Hooks\Templates;

use App\Hooks\AbstractHook;
use Illuminate\Support\Facades\Validator;

/**
 * 验证钩子模板
 * 
 * 专门用于数据验证的钩子模板
 * 
 * @hook validation.hook
 * @priority 5
 * @group validation
 */
class ValidationHookTemplate extends AbstractHook
{
    protected string $description = '数据验证钩子模板';
    protected int $priority = 5; // 验证通常需要较高优先级

    // 验证配置
    protected array $validationConfig = [
        'strict_mode' => false,      // 严格模式
        'stop_on_first_failure' => false, // 遇到第一个错误就停止
        'custom_messages' => [],     // 自定义错误消息
        'custom_attributes' => [],   // 自定义属性名称
    ];

    // 预定义验证规则
    protected array $predefinedRules = [
        'user_data' => [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'age' => 'nullable|integer|min:0|max:150',
        ],
        'order_data' => [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
        ],
        'file_data' => [
            'file' => 'required|file|max:10240', // 10MB
            'type' => 'required|in:image,document,video',
        ],
    ];

    /**
     * 处理验证钩子逻辑
     * 
     * @param mixed ...$args 钩子参数
     * @return mixed 处理结果
     */
    public function handle(...$args)
    {
        [$data, $rules, $options] = $this->extractArgs($args);

        // 应用验证配置
        $this->applyValidationConfig($options);

        // 获取验证规则
        $validationRules = $this->getValidationRules($rules);

        // 执行验证
        $validationResult = $this->performValidation($data, $validationRules, $options);

        // 处理验证结果
        return $this->processValidationResult($validationResult, $data, $options);
    }

    /**
     * 提取参数
     */
    protected function extractArgs(array $args): array
    {
        $data = $args[0] ?? [];
        $rules = $args[1] ?? 'default';
        $options = $args[2] ?? [];

        return [$data, $rules, $options];
    }

    /**
     * 应用验证配置
     */
    protected function applyValidationConfig(array $options): void
    {
        if (isset($options['validation_config'])) {
            $this->validationConfig = array_merge($this->validationConfig, $options['validation_config']);
        }
    }

    /**
     * 获取验证规则
     */
    protected function getValidationRules($rules): array
    {
        if (is_string($rules)) {
            // 使用预定义规则
            return $this->predefinedRules[$rules] ?? [];
        }

        if (is_array($rules)) {
            // 直接使用提供的规则
            return $rules;
        }

        return [];
    }

    /**
     * 执行验证
     */
    protected function performValidation(array $data, array $rules, array $options): array
    {
        if (empty($rules)) {
            return [
                'valid' => true,
                'errors' => [],
                'message' => '没有验证规则'
            ];
        }

        try {
            // 创建验证器
            $validator = Validator::make(
                $data,
                $rules,
                $this->validationConfig['custom_messages'],
                $this->validationConfig['custom_attributes']
            );

            // 添加自定义验证规则
            $this->addCustomValidationRules($validator);

            // 执行验证
            if ($validator->fails()) {
                return [
                    'valid' => false,
                    'errors' => $validator->errors()->toArray(),
                    'message' => '验证失败',
                    'failed_rules' => $validator->failed()
                ];
            }

            // 验证通过
            return [
                'valid' => true,
                'errors' => [],
                'message' => '验证通过',
                'validated_data' => $validator->validated()
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['validation_error' => [$e->getMessage()]],
                'message' => '验证过程出错',
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * 添加自定义验证规则
     */
    protected function addCustomValidationRules($validator): void
    {
        // TODO: 添加你的自定义验证规则
        
        // 示例：验证手机号
        $validator->addExtension('mobile', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^1[3-9]\d{9}$/', $value);
        });

        // 示例：验证身份证号
        $validator->addExtension('id_card', function ($attribute, $value, $parameters, $validator) {
            return $this->validateIdCard($value);
        });

        // 示例：验证业务规则
        $validator->addExtension('business_rule', function ($attribute, $value, $parameters, $validator) {
            return $this->validateBusinessRule($attribute, $value, $parameters);
        });
    }

    /**
     * 验证身份证号
     */
    protected function validateIdCard(string $idCard): bool
    {
        // TODO: 实现身份证号验证逻辑
        
        // 简单的长度和格式检查
        return preg_match('/^\d{17}[\dXx]$/', $idCard);
    }

    /**
     * 验证业务规则
     */
    protected function validateBusinessRule(string $attribute, $value, array $parameters): bool
    {
        // TODO: 实现业务规则验证逻辑
        
        // 示例：根据参数执行不同的业务规则验证
        $rule = $parameters[0] ?? 'default';
        
        switch ($rule) {
            case 'unique_in_context':
                return $this->validateUniqueInContext($attribute, $value, $parameters);
                
            case 'valid_date_range':
                return $this->validateDateRange($attribute, $value, $parameters);
                
            case 'sufficient_balance':
                return $this->validateSufficientBalance($attribute, $value, $parameters);
                
            default:
                return true;
        }
    }

    /**
     * 验证上下文唯一性
     */
    protected function validateUniqueInContext(string $attribute, $value, array $parameters): bool
    {
        // TODO: 实现上下文唯一性验证
        return true;
    }

    /**
     * 验证日期范围
     */
    protected function validateDateRange(string $attribute, $value, array $parameters): bool
    {
        // TODO: 实现日期范围验证
        return true;
    }

    /**
     * 验证余额充足
     */
    protected function validateSufficientBalance(string $attribute, $value, array $parameters): bool
    {
        // TODO: 实现余额验证
        return true;
    }

    /**
     * 处理验证结果
     */
    protected function processValidationResult(array $validationResult, array $data, array $options): array
    {
        $result = [
            'status' => $validationResult['valid'] ? 'success' : 'validation_failed',
            'valid' => $validationResult['valid'],
            'message' => $validationResult['message'],
            'timestamp' => now()
        ];

        if (!$validationResult['valid']) {
            $result['errors'] = $validationResult['errors'];
            
            if (isset($validationResult['failed_rules'])) {
                $result['failed_rules'] = $validationResult['failed_rules'];
            }

            // 严格模式下抛出异常
            if ($this->validationConfig['strict_mode']) {
                throw new \InvalidArgumentException('数据验证失败: ' . $validationResult['message']);
            }
        } else {
            if (isset($validationResult['validated_data'])) {
                $result['validated_data'] = $validationResult['validated_data'];
            }

            // 执行验证通过后的处理
            $result['post_validation'] = $this->handleValidationSuccess($data, $options);
        }

        return $result;
    }

    /**
     * 处理验证成功后的逻辑
     */
    protected function handleValidationSuccess(array $data, array $options): array
    {
        // TODO: 实现验证成功后的处理逻辑
        
        // 示例：
        // - 数据清理
        // - 数据转换
        // - 触发后续流程
        // - 记录审计日志
        
        return [
            'processed' => true,
            'processed_at' => now()
        ];
    }

    // 规则管理方法

    /**
     * 添加预定义规则
     */
    public function addPredefinedRules(string $name, array $rules): self
    {
        $this->predefinedRules[$name] = $rules;
        return $this;
    }

    /**
     * 获取预定义规则
     */
    public function getPredefinedRules(string $name = null): array
    {
        if ($name) {
            return $this->predefinedRules[$name] ?? [];
        }
        
        return $this->predefinedRules;
    }

    /**
     * 移除预定义规则
     */
    public function removePredefinedRules(string $name): self
    {
        unset($this->predefinedRules[$name]);
        return $this;
    }

    /**
     * 设置验证配置
     */
    public function setValidationConfig(array $config): self
    {
        $this->validationConfig = array_merge($this->validationConfig, $config);
        return $this;
    }

    /**
     * 获取验证配置
     */
    public function getValidationConfig(): array
    {
        return $this->validationConfig;
    }

    /**
     * 设置严格模式
     */
    public function setStrictMode(bool $strict): self
    {
        $this->validationConfig['strict_mode'] = $strict;
        return $this;
    }

    /**
     * 设置自定义错误消息
     */
    public function setCustomMessages(array $messages): self
    {
        $this->validationConfig['custom_messages'] = $messages;
        return $this;
    }

    /**
     * 设置自定义属性名称
     */
    public function setCustomAttributes(array $attributes): self
    {
        $this->validationConfig['custom_attributes'] = $attributes;
        return $this;
    }

    /**
     * 参数验证
     */
    protected function validateArgs(...$args): bool
    {
        return count($args) >= 1 && is_array($args[0]);
    }
}