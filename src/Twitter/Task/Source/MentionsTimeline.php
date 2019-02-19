<?php

namespace Twist\Twitter\Task\Source;

use Twist\Twitter\Api\Client;
use Twist\Twitter\Task\ConfigurableInterface;

class MentionsTimeline implements SourceInterface, ConfigurableInterface
{
    /** @var Client */
    private $client;

    /** @var array */
    private $config;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(): array
    {
        return $this->client->getMentionsTimeline($this->config);
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
