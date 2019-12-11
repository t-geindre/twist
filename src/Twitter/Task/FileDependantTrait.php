<?php

namespace Twist\Twitter\Task;

trait FileDependantTrait
{
    protected function getFile(array $from, string $field, bool $readable = false, bool $writable = false): string
    {
        if (!is_string($file = $from[$field] ?? null)) {
            throw new \InvalidArgumentException(sprintf('Missing mandatory "%s" parameter', $field));
        }

        if ($writable) {
            if (!is_file($file) && false === touch($file)) {
                throw new \InvalidArgumentException(sprintf('Unable to create "%s" file', $file));
            }

            if (!is_writeable($file)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not writable', $file));
            }
        }

        if ($readable) {
            if (!is_file($file) || !is_readable($file)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not readable', $file));
            }
        }

        return $file;
    }
}
