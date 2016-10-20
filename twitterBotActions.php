<?php

require 'autoload.php';
require 'twitteroauth/TwitterOAuth.php';
require 'TwitterBotRunner.php';
//use Abraham\TwitterOAuth\TwitterOAuth;
 
define('CONSUMER_KEY', 'yjZoZ1ARvwUGJPxknz5FKuCeq');
define('CONSUMER_SECRET', 'KkJLEcys4CZQYX40cldPr5etD65GmX9Wp6Xq7VUiTEKe4SSgc9');
define('ACCESS_TOKEN', '784013569230827520-kWrbMN8OZv2RA9aLdEFJXAzZmeIjb1n');
define('ACCESS_TOKEN_SECRET', 'eJxq9NX2sMMwetRa7M5jXH4shYLWyG2z6zyUW7hPp7HrB');

// Create new instance of twitter bot
$twitterInstance = new TwitterBotRunner(CONSUMER_KEY, CONSUMER_SECRET);

// Setup token
$twitterInstance->setToken(ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$twitterInstance->searchKeywordTweets("#guyane");
 
?>