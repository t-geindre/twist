# Twist

Fully configurable Twitter bot

 * [How it works](#how-it-works)
 * [Requirements](#requirements)
   * [System requirements](#system-requirements)
   * [Installation](#installation)
 * [Configuration](#configuration)
   * [Tasks configuration](#tasks-configuration)
     * [Sources](#sources)
     * [Steps](#steps)
   * [Configuration parameters](#configuration-parameters)
   * [Configuration examples](#configuration-examples)
 * [Running the bot](#running-the-bot)
 * [Import existing friendships](#import-existing-friendships)
 * [Generate replies](#generate-replies)

## How it works

This bot is meant to be seen as a real human using twitter.
That's the reason why it makes Twitter API calls through a logged in headless Chromium browser.
It doesn't require app credentials to get access to the API, just the username and the password of a valid Twitter account.

By default, this bot doesn't do anything and you have to give it a configuration file where you define all tasks it will have to run.

Once you have configured your tasks, you can run the bot.

## Requirements

### System requirements

 * Chromium v65+
 * The bot will run the `chromium-browser` command, so it should be available in your PATH
 * PHP 7+
 * Composer => todo
 
### Installation

 * Clone the bot source code: `git clone https://github.com/t-geindre/twist.git`
 * In the newly created directory `twist`, install dependencies: `composer install`

Dependencies with composer (todo: check php requirements in composer.json)

## Twitter vocabulary

Status/Tweet

## Configuration

The default configuration file used is `~/.twist/config.yaml`. If needed, you can run the bot with a different configuration file, see [Running the bot](#running-the-bot).

The first time the bot is launched, if there is no existing configuration file, it will create a [default one](config/default.yaml). 

The root configuration file is made of the following elements:

 * `username` - *string* - **required**
    The account username which the bot will use to log in Twitter.
    
 * `password` - *string* - **required**
    The account password which the bot will use to log in Twitter.
    
 * `tasks` - *array(object)* - **required**
    The list of tasks the bot will execute, see [Task configuration](#tasks-configuration).
    
 * `parameters` - *array(mixed)* - **optional**
    In this section, you can define portion of configuration which you'll be able to use anywhere else, see [Configuration parameters](#configuration-parameters).
    
### Tasks configuration

The task configuration is made of the following elements:

 * `pause` - *integer* - **optional** - default `0`
    Define the duration the bot will wait before the task is executed once again.
    
 * `start_delay` - *integer* - **optional** - default `0`
    Define the duration the bot will wait before the first task execution.
    
 * `login_required` - *boolean* - **optional** - default `true`
    If the task has steps that will perform calls to the twitter API, then this entry must be set to `true`. Otherwise, it can be set to `false` to avoid useless Twitter login.
 
 * `source` - *object* - **required**
    This entry defines the source which will provide the data used during the steps execution.
    See [sources](#sources) for more information.
 
 * `steps` - *array(object)* - **required**
    This entry defines all the steps which will be applied to the data returned by the task source.
    See [steps](#steps) for more information.

#### Sources

A source is configured with the following fields:
  * `type` - *string* - **required**
     One of the available source type, see list below.
  
  * `config` - *array(mixed)* - **optional**
     Each source type has its own configuration schema, see list below.
  
Available sources:
 * Import data from a CSV file
   * `type`: `CsvImport`
   * `config`:
     * `file` - *string* - **required** - Path to CSV file to read
     * `headers` - *array* - **optional** - File headers name 
     * `use_file_headers` - *boolean* - **optional** - Use file headers
     * `delimiter` - *string* - **optional**
     * `enclosure` - *string* - **optional**
     * `escape_char` - *string* - **optional**
   
 * Get statuses from given list
   * `type`: `Tweet\ListStatuses`
   * `config`:
   
 * Get statuses from mention timeline
   * `type`: `Tweet\MentionsTimeline`
   * `config`:
   
 * Get statuses from the search API
   * `type`: `Tweet\Search`
   * `config`:
   
 * Get users from your local friendship database
   * `type`: `User\ExpiredFriendship`
   * `config`:
    

#### Steps

A step is configured with the following fields:
  * `type` - *string* - **required**
  * `config` - *array(mixed)* - **optional**
  * `conditions` - *array(object)* - **optional**

 * `Action\Tweet\Reply\AddPart` -
 * `Action\Tweet\Reply\AddSourceTags` -
 * `Action\Tweet\Reply\Create` -
 * `Action\Tweet\Display` -
 * `Action\Tweet\Favorite` -
 * `Action\Tweet\FriendshipMentioned` -
 * `Action\Tweet\FriendshipOwner` -
 * `Action\Tweet\GetInReplyStatus` -
 * `Action\Tweet\GetRetweetedStatus` -
 
 * `Action\Tweet\Reload` - Reload status data from Twitter API
 Reload a tweet based on its ID field.
 Some data sources, such as the Twitter search API, might return wrong information.
 For example, if you've already retweeted a status, the `retweeted` field will still be set to `false`.
 To get the real value, you have to reload the tweet.
 Be aware that this action might strongly increase the number of requests your bot will perform to the Twitter API.
 
 * `Action\Tweet\Reply` -
 * `Action\Tweet\Retweet` -
 * `Action\User\Display` -
 * `Action\User\Friendship` -
 * `Action\User\Unfriendship` -
 * `Action\CsvExport` -
 * `Condition\Scoring\ScoredCondition` -
 * `Condition\ConditionInterface` -
 * `Condition\FieldComparison` -
 * `Condition\FieldMatch` -
 * `Condition\IsUnique` -
 * `Condition\Limit` -
 * `Condition\Random` -
 * `Condition\Scoring` -
 
 - type (Action/Condition)
 - config
 - conditions

### Configuration parameters

### Configuration examples

 * Retweet statuses
 * Like your mentions
 * Reply randomly
 * Display tweets from a CSV file

## Running the bot

## Import existing friendships

## Generate replies
