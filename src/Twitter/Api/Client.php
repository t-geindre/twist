<?php

namespace Twist\Twitter\Api;

use Twist\Twitter\Browser\Client as BrowserClient;

class Client
{
    const API_URL = 'https://api.twitter.com/1.1/';

    /** @var BrowserClient */
    private $browserClient;

    public function __construct(BrowserClient $browserClient)
    {
        $this->browserClient = $browserClient;
    }

    public function request(string $method, string $url, array $data = []): ?array
    {
        return $this->browserClient->request([
            'method' => $method,
            'url' => self::API_URL.$url,
            'data' => $data
        ]);
    }

    public function login(string $username, string $password): bool
    {
        return $this->browserClient->login($username, $password);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/search/api-reference/get-search-tweets.html
     */
    public function searchTweets(array $data): ?array
    {
        return $this->request('GET', 'search/tweets.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/post-and-engage/api-reference/post-statuses-update#post-statusesupdate
     */
    public function updateStatus(array $data): ?array
    {
        return $this->request('POST', 'statuses/update.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/post-and-engage/api-reference/get-statuses-show-id
     */
    public function getStatus(array $data): ?array
    {
        return $this->request('GET', 'statuses/show.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-mentions_timeline.html
     */
    public function getMentionsTimeline(array $data)
    {
        return $this->request('GET', 'statuses/mentions_timeline.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/post-and-engage/api-reference/post-statuses-retweet-id
     */
    public function retweetStatus(array $data): ?array
    {
        return $this->request('POST', 'statuses/retweet.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/accounts-and-users/follow-search-get-users/api-reference/post-friendships-create
     */
    public function createFriendship(array $data): ?array
    {
        return $this->request('POST', 'friendships/create.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/tweets/post-and-engage/api-reference/post-favorites-create
     */
    public function createFavorite(array $data)
    {
        return $this->request('POST', 'favorites/create.json', $data);
    }

    /**
     * See https://developer.twitter.com/en/docs/accounts-and-users/follow-search-get-users/api-reference/get-friends-list.html
     */
    public function getFriendsList(array $data)
    {
        return $this->request('GET', 'friends/list.json', $data);
    }
}
