<?php

namespace Twist\Twitter\Task\Step\Action\Tweet\Reply;

use Twist\Twitter\Task\ConfigurableInterface;
use Twist\Twitter\Task\Step\Action\ActionInterface;

class AddPart implements ActionInterface, ConfigurableInterface
{
    /** @var array */
    private $config;

    public function execute(array $tweet): ?array
    {
        $reply = &$tweet[self::EXTRA_FIELDS_NAMESPACE]['reply'];
        $parts = $this->config['parts'];

        if ($this->config['repetition']['avoid'] ?? false) {
            $minLength = $this->config['repetition']['min_length'] ?? 3;
            $usedWords = $this->getWords($reply, $minLength);

            $parts = array_values(array_filter(
                $parts,
                    function(string $part) use ($usedWords, $minLength) {
                    foreach ($this->getWords($part, $minLength) as $word) {
                        if (in_array($word, $usedWords)) {
                            return false;
                        }
                    }

                    return true;
                }
            ));
        }

        $part = ucfirst($parts[mt_rand(0, count($parts) - 1)]);

        if (substr($reply, -1, 1) === ' ') {
            $part = lcfirst($part);
        }

        $reply = trim($reply).(!empty($reply) ? ' ' : '').$part;

        return $tweet;
    }

    public function configure(array $config): void
    {
        if (empty($config['parts'])) {
            throw new \InvalidArgumentException('At least on reply part is required');
        }

        // Make sure parts keys are numeric and continue
        $config['parts'] = array_values($config['parts']);

        $this->config = $config;
    }

    protected function getWords(string $sentence, int $minLength): array
    {
        return array_map(
            'strtolower',
            array_filter(
                preg_split('/ |\'|-|,/', trim($sentence)),
                function (string $word) use ($minLength) {
                    return mb_strlen($word) >= $minLength;
                }
            )
        );
    }
}
