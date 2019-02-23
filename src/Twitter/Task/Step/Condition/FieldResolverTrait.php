<?php

namespace Twist\Twitter\Task\Step\Condition;

trait FieldResolverTrait
{
    protected function resolveField(string $path, array $data)
    {
        foreach (explode('.', $path) as $item) {
            $data = $data[$item];
        }

        return $data;
    }
}
