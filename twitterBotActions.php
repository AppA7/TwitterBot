<?php

require 'autoload.php';
require 'TwitterBotRunner.php';
//use Abraham\TwitterOAuth\TwitterOAuth;
 
define('CONSUMER_KEY', 'yjZoZ1ARvwUGJPxknz5FKuCeq');
define('CONSUMER_SECRET', 'KkJLEcys4CZQYX40cldPr5etD65GmX9Wp6Xq7VUiTEKe4SSgc9');
define('ACCESS_TOKEN', '784013569230827520-kWrbMN8OZv2RA9aLdEFJXAzZmeIjb1n');
define('ACCESS_TOKEN_SECRET', 'eJxq9NX2sMMwetRa7M5jXH4shYLWyG2z6zyUW7hPp7HrB');
define('BITLY_LOGIN','o_7am8np1erp');
define('BITLY_KEY','R_5a1b657f438b4b97b6ee7360676587f1');

// Get information from initial form
$login = $_POST['login'];
$password = $_POST['pwd'];
$hashtag = $_POST['hashtag'];
$website = $_POST['url'];
$responseList = $_POST['responsesList'];

// Create new instance of twitter bot
$twitterInstance = new TwitterBotRunner(CONSUMER_KEY, CONSUMER_SECRET);

// Shorten url with bit.ly
$shortUrl = $twitterInstance->shortenUrl($website, BITLY_LOGIN, BITLY_KEY);

// Setup twetter token
$twitterInstance->setToken(ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$hashtag = $twitterInstance->styleHashtag($hashtag);

$searchResults = $twitterInstance->searchKeywordTweets($hashtag);

$reply = $responseList . ' ' . $shortUrl;

foreach ($searchResults->statuses as $tweet) {
	$twitterInstance->AddRepliesToSearch($tweet, $reply);	
}

?>