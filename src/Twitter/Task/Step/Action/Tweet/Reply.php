<?php

namespace App\Twitter\Task\Step\Action\Tweet;

use App\Twitter\Task\ConfigurableInterface;
use App\Twitter\Task\Step\Action\ActionInterface;
use App\Twitter\Api\Client;

class Reply implements ActionInterface, ConfigurableInterface
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
        $this->client->updateStatus([
            'in_reply_to_status_id' => $tweet['id_str'],
            'status' => sprintf(
                '@%s %s',
                $tweet['user']['screen_name'],
                $this->getReply($tweet)
            )
        ]);

        return $tweet;
    }

    protected function getReply(array $tweet): string
    {
        if (!empty($this->config['reply'])) {
            return (string) $this->config['reply'];
        }

        if (!empty($this->config['replies']) && is_array($this->config['replies'])) {
            return (string) array_values($this->config['replies'])[mt_rand(0, count($this->config['replies']))];
        }

        if (!empty($tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]['reply'])) {
            return $tweet[ActionInterface::EXTRA_FIELDS_NAMESPACE]['reply'];
        }

        throw new \RuntimeException('Not reply found');
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
