<?php

namespace App\Twitter\Task\Actions\Tweet;

use App\Twitter\Task\Actions\ActionInterface;
use App\Twitter\Api\Client;
use App\Twitter\Task\Configurable\NotConfigurableTrait;

class Reply implements ActionInterface
{
    use NotConfigurableTrait;

    const REPLY_PARTS = [
        [
            'Je tente ma chance,',
            'On croise les doigts !',
            'Merci pour le concours !',
            'Sympa le concours !',
            'J\'adore les concours,',
            'Bonne chance à tous !',
            'Merci !',
            'J\'espère que vais gagner !',
            'Je le sens bien ce concours,',
            'Je participe,',
            'Super le concours !',
            'Merci d\'organiser ce concours !',
            'Si je gagne, je partage pas !',
            'Allez hop je participe !',
            'Celui là je le gagne !',
            'Allez on y crois !',
        ],
        [
            'Merci pour le concours !',
            'Merci d\'organiser ça !',
            'Merci !',
            'Je kiff !',
            'Cette fois-ci je le gagne !',
            'Vous êtes les meilleurs !',
            'Bonne chance à tous !',
            'Je suis à fond !',
            'Je croise les doigts !',
        ]
    ];

    const MENTION_PARTS = [
        [
            'Hey @dwogsi,',
            'Yo @dwogsi',
            '@dwogsi,',
            'Tiens @dwogsi,',
        ],
        [
            'je sais que tu aimes les concours !',
            'tu peux le tenter aussi !',
            'c\'est un concours pour toi ça, non ?',
            'tu me disais que tu cherchais des concours non ?',
            'je t\'ais trouvé un concours !',
            'tentes ta chance !',
            'c\'est pour toi ça !',
        ]
    ];

    const HASHTAG_FORBIDDEN_WORDS = [
        'concours', 'rt', 'follow', 'cadeau', 'lot', 'giveaway', 'gagner', 'jeu', 'like',
    ];

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(array $tweet): array
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
        $reply = $this->generateReply(self::REPLY_PARTS);

        if (preg_match('/mention|tag|ami/', $tweet['full_text'])) {
            $reply .= $this->generateReply(self::MENTION_PARTS, true);
        }

        $reply .= $this->getHashtags($tweet, true);

        return $reply;
    }

    protected function getHashtags(array $tweet, bool $withStartingSpace = false): string
    {
        $hashtags = array_unique(
            array_filter(
                array_column($tweet['entities']['hashtags'] ?? [], 'text'),
                function(string $tag) {
                    foreach (self::HASHTAG_FORBIDDEN_WORDS as $word) {
                        if (strpos(strtolower($tag), strtolower($word)) !== false) {
                            return false;
                        }
                    }
                    return true;
                }
            )
        );

        if ($count = count($hashtags)) {
            return ($withStartingSpace ? ' ' : '').'#'.array_values($hashtags)[mt_rand(0, $count - 1)];
        }

        return '';
    }

    protected function generateReply(array $replyParts, bool $withStartingSpace = false): string
    {
        $reply = '';
        $usedWords = [];

        foreach ($replyParts as $parts) {
            for(;;) {
                $part = $parts[mt_rand(0, count($parts)-1)];
                $partWords = $this->getWords($part);

                if (count($usedWords) > 0) {
                    foreach ($partWords as $word) {
                        if (in_array($word, $usedWords)) {
                            continue 2;
                        }
                    }
                }

                break;
            };

            $usedWords = array_merge($usedWords, $partWords);

            if (substr($reply, -1, 1) === ',') {
                $part = lcfirst($part);
            }

            $reply .= (!empty($reply) || $withStartingSpace ? ' ' : '').$part;
        }

        return $reply;
    }

    protected function getWords(string $sentence): array
    {
        return array_map(
            'strtolower',
            array_filter(
                preg_split('/ |\'|-|,/', trim($sentence)),
                function (string $word) {
                    return mb_strlen($word) > 3;
                }
            )
        );
    }
}
