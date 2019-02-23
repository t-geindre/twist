<?php

namespace Twist\Twitter\Task\Step\Condition;

use Twist\Twitter\Task\ConfigurableInterface;

class FieldComparison implements ConditionInterface, ConfigurableInterface
{
    use FieldResolverTrait;

    const TYPE_SUPPORTED_OPERATORS = [
        'string' => ['eq', 'neq'],
        'bool' => ['eq', 'neq'],
        'int' => ['eq', 'neq', 'gte', 'gt', 'lt', 'lte'],
        'date' => ['eq', 'neq', 'gte', 'gt', 'lt', 'lte']
    ];

    /** @var array */
    private $config = [];

    /** @var string */
    protected $comparisonField;

    public function configure(array $config): void
    {
        $this->config = $config;
    }

    public function satisfy(array $subject): bool
    {
        return $this->compare(
            $this->config['operator'],
            $this->config['type'] ?? 'string',
            $this->resolveField($this->config['field'], $subject),
            $this->config['value']
        );
    }

    protected function compare(string $operator, string $type, $a, $b): bool
    {
        $operator = strtolower($operator);
        $comparisonClosures = $this->getComparisonClosures();

        if (!array_key_exists($operator, $comparisonClosures)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported comparison operator "%s", supported types are: %s',
                $operator,
                implode(', ', array_keys($comparisonClosures))
            ));
        }

        $supported = self::TYPE_SUPPORTED_OPERATORS[$type] ?? null;

        if (!is_array($supported)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported comparison data type "%s", supported types are: %s',
                $type,
                implode(', ', array_keys(self::TYPE_SUPPORTED_OPERATORS))
            ));
        }

        if (!in_array($operator, $supported)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported operator "%s" for data type "%s", supported operators are: %s',
                $operator,
                $type,
                implode(', ', $supported)
            ));
        }

        $conversionClosures = $this->getConversionClosures();

        return $comparisonClosures[$operator](
            $conversionClosures[$type]($a),
            $conversionClosures[$type]($b)
        );
    }

    protected function getComparisonClosures(): array
    {
        return [
            'eq' => function ($a, $b) {
                return $a == $b;
            },
            'neq' => function ($a, $b) {
                return $a != $b;
            },
            'gte' => function ($a, $b) {
                return $a >= $b;
            },
            'gt' => function ($a, $b) {
                return $a > $b;
            },
            'lt' => function ($a, $b) {
                return $a < $b;
            },
            'lte' => function ($a, $b) {
                return $a <= $b;
            },
        ];
    }

    protected function getConversionClosures()
    {
        return [
            'string' => function ($value) {
                return (string) $value;
            },
            'int' => function ($value) {
                return (int) $value;
            },
            'bool' => function ($value) {
                return (bool) $value;
            },
            'date' => function ($value) {
                try {
                    $date = new \DateTime($value);
                } catch (\Throwable $e) {
                    throw new \InvalidArgumentException(sprintf('Invalid date format "%s"', $value));
                }

                return $date;
            },
        ];
    }
}

