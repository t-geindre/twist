<?php

namespace App\Console\Command;

use App\Configuration\Configuration;
use App\Twitter\Api\Client;
use App\Twitter\Browser\Client as Browser;
use App\Twitter\Task\Step\Action\Tweet\Reply;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LegacyCommand extends Command
{
    const COMMAND_NAME = 'legacy';

    protected static $defaultName = self::COMMAND_NAME;

    /** @var Client */
    private $client;

    /** @var Reply */
    private $reply;

    /** @var Configuration */
    private $config;

    /** @var SymfonyStyle */
    private $io;
    /** @var Browser */
    private $browser;

    public function __construct(Client $client, Reply $reply, Configuration $config, SymfonyStyle $io, Browser $browser)
    {
        parent::__construct();

        $this->client = $client;
        $this->reply = $reply;
        $this->config = $config;
        $this->io = $io;
        $this->browser = $browser;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        [$username, $password] = $this->getCredentials();

        $this->io->comment('Login');
        $this->browser->login($username, $password);

        while (true) {
            $this->io->comment('Searching for tweets');
            $tweets = $this->client->searchTweets([
                'q' => 'RT follow gagner',
                'lang' => 'fr',
                'result_type' => 'recent',
                'include_entities' => 'false',
                'count' => 100,
            ])['statuses'];

            // Avoid retweeted status
            foreach ($tweets as &$tweet) {
                if (!empty($tweet['retweeted_status'])) {
                    $tweet = $tweet['retweeted_status'];
                }
            }

            // Remove replies
            foreach ($tweets as $key => $tweet) {
                if (!empty($tweet['in_reply_to_status_id'])) {
                    unset($tweets[$key]);
                }
            }

            // Avoid duplicates
            $duplicatedTweets = $tweets;
            $tweets = [];
            foreach ($duplicatedTweets as $duplicatedTweet) {
                $tweets[$duplicatedTweet['id']] = $duplicatedTweet;
            }
            $tweets = array_values($tweets);

            // Fully load tweets contents
            foreach ($tweets as &$tweet) {
                // Load tweet
                $tweet = $this->client->getStatus([
                    'id' => $tweet['id_str'],
                    'include_my_retweet' => 'true',
                    'include_entities' => 'true',
                    'tweet_mode' => 'extended'
                ]);

                // Avoid already retweeted
                if ($tweet['retweeted'] !== false) {
                    continue;
                }

                // Avoid too few followers
                if ($tweet['user']['followers_count'] < 200) {
                    continue;
                }

                // Display
                $this->io->block(
                    sprintf(
                        '%s @%s - %s followers',
                        $tweet['user']['name'],
                        $tweet['user']['screen_name'],
                        $tweet['user']['followers_count']
                    ),
                    null,
                    'bg=green;fg=white;options=bold'
                );
                $this->io->block($tweet['full_text']);
                $this->io->block(
                    sprintf(
                        '%s retweets - %s favorites',
                        $tweet['retweet_count'],
                        $tweet['favorite_count']
                    ),
                    null,
                    'bg=blue;fg=white'
                );

                // Retweet
                $this->client->retweetStatus(['id' => $tweet['id_str']]);

                // Favorite
                $this->client->createFavorite(['id' => $tweet['id_str']]);

                // Reply
                $this->reply->execute($tweet);

                // Follow owner and mentions
                $this->client->createFriendship(['user_id' => $tweet['user']['id_str']]);
                foreach ($tweet['entities']['user_mentions'] as $mention) {
                    $this->client->createFriendship(['user_id' => $mention['id_str']]);
                }

                // Update tweet
                $tweet['retweeted'] = true;
            }


            $pauseDuration = 600;
            $this->io->comment(sprintf('Pause before next search (%d seconds)', $pauseDuration));
            $progress = $this->io->createProgressBar();
            $progress->setMaxSteps($pauseDuration);
            $progress->setMessage('Pause before next search');
            for ($i = 0; $i < $pauseDuration; $i++) {
                sleep(1);
                $progress->advance();
            }
            $progress->clear();
        }
    }

    protected function getCredentials(): array
    {
        do {
            $configUserName = $this->config->get('username');
            $username = $this->io->ask('Username', $configUserName);
        } while (empty(trim($username)));

        if ($configUserName !== $username) {
            $this->config->set('username', $username);
            try {
                $this->config->persist();
            } catch(\Throwable $e) {
                $this->io->warning($e->getMessage());
            }
        }

        $password = $this->config->get('password');
        while (empty($password)) {
            $password = $this->io->askHidden('Password (hidden, never stored)');
        }

        return [$username, $password];
    }
}
