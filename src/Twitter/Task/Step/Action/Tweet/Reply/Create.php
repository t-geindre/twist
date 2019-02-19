<?php

namespace Twist\Twitter\Task\Step\Action\Tweet\Reply;

use Twist\Twitter\Task\Step\Action\ActionInterface;

class Create implements ActionInterface
{
    public function execute(array $tweet): ?array
    {
        $tweet[self::EXTRA_FIELDS_NAMESPACE] = array_merge(
            $tweet[self::EXTRA_FIELDS_NAMESPACE] ?? [],
            ['reply' => '']
        );

        return $tweet;
    }
}
