<?php

namespace App\Twitter\Conditions;

class FieldComparison implements ConditionInterface
{
    /** @var array */
    private $config = [];

    /** @var string */
    protected $comparisonField;

    public function configure(?array $config): void
    {
        $this->config = $config;
    }

    public function satisfy(array $subject): bool
    {
        return $this->compare(
            $this->config['operator'],
            $subject[$this->config['field']],
            $this->config['value']
        );
    }

    protected function compare(string $operator, $a, $b): bool
    {
        $operator = strtolower($operator);

        if ($operator === 'eq') {
            return $a == $b;
        }

        if ($operator === 'neq') {
            return $a != $b;
        }

        $closures =  [
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

        if (!isset($closures[$operator])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid comparison operator, supported operators are "%s", "%s given',
                implode(', ', array_keys($closures)),
                $operator
            ));
        }

        foreach ([$a, $b] as &$value) {
            if (is_int($value)) {
                continue;
            }

            if (is_numeric($value)) {
                $value = (int) $value;
                continue;
            }

            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid value format given');
            }
        }

        if (gettype($a) !== gettype($b)) {
            throw new \InvalidArgumentException('Cannot compare values of different types');
        }

        return $closures[$operator]($a, $b);
    }
}
