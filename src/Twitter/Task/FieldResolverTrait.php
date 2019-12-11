<?php

namespace Twist\Twitter\Task;

trait FieldResolverTrait
{
    protected function resolveField(string $path, array $data)
    {
        foreach (explode('.', $path) as $item) {
            $data = $data[$item];
        }

        return $data;
    }

    protected function reverseField(string $path, $value, array $data)
    {
        $returnedData = &$data;
        foreach (explode('.', $path) as $item) {
            if (!array_key_exists($item, $data)) {
                $data[$item] = [];
            }
            $data = &$data[$item];
        }

        $data = $value;
        return $returnedData;
    }
}
