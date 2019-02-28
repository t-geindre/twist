<?php

namespace Twist\Twitter\Task\Step\Action\Tweet;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;

class GetInReplyStatus implements ActionInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var array */
    private $config;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): ?array
    {
        if (!empty($replyId = $tweet['in_reply_to_status_id_str'])) {
            return $this->client->getStatus(array_merge(
                ['id' => $tweet['id_str']],
                $this->config
            ));
        }

        return $tweet;
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
