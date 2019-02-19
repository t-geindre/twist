<?php

namespace App\Twitter\Task\Step\Action\Tweet\Reply;

use App\Twitter\Task\Step\Action\ActionInterface;

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
