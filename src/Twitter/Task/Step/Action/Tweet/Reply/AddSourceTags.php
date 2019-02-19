<?php

namespace Twist\Twitter\Task\Step\Action\Tweet\Reply;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;

class AddSourceTags implements ActionInterface, ConfigurableInterface
{
    /** @var array */
    private $config;

    public function execute(array $tweet): ?array
    {
        $hashtags = array_column($tweet['entities']['hashtags'] ?? [], 'text');
        $hashtags = array_unique($hashtags);

        if (empty($hashtags)) {
            return $tweet;
        }

        if ($remove = ($this->config['remove'] ?? false)) {
            $list = $remove['list'] ?? [];
            $mode = $remove['mode'] ?? 'contains';

            $hashtags = array_filter($hashtags, function(string $tag) use ($list, $mode) {
                $tag = strtolower($tag);

                foreach ($list as $word) {
                    $word = strtolower($word);

                    if ($mode == 'contains' && strpos($tag, $word) !== false) {
                        return false;
                    }

                    if ($mode == 'equal' && $tag == $word) {
                        return false;
                    }
                }

                return true;
            });
        }

        if ($limit = ($this->config['limit'] ?? false)) {
            $count = $limit['count'] ?? 1;
            $mode = $limit['mode'] ?? 'random';

            if (count($hashtags) > $count) {
                if ($mode === 'random') {
                    shuffle($hashtags);
                }
                $hashtags = array_slice($hashtags, 0, $count);
            }
        }

        if (empty($hashtags)) {
            return $tweet;
        }

        $reply = &$tweet[self::EXTRA_FIELDS_NAMESPACE]['reply'];
        $reply = trim($reply).(!empty($reply) ? ' ' : '').'#'.implode(' #', $hashtags);

        return $tweet;
    }

    public function configure(array $config): void
    {
        $this->config = $config;
    }
}
