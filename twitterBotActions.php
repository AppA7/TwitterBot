<?php

require 'autoload.php';
 
use Abraham\TwitterOAuth\TwitterOAuth;
 
define('CONSUMER_KEY', '0YttKZbMCgzcdovlt0sNeqPvM');
define('CONSUMER_SECRET', '1Idwhf0QKChCeGEZsz8thTJz6UC9tTEXyhn2XWbzDyEVUabAta');
define('ACCESS_TOKEN', '784013569230827520-kWrbMN8OZv2RA9aLdEFJXAzZmeIjb1n');
define('ACCESS_TOKEN_SECRET', 'Jxq9NX2sMMwetRa7M5jXH4shYLWyG2z6zyUW7hPp7HrB');
 
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$theSearch = array('q' => '#Paris', 'lang' => 'fr', 'count' => 5);
 
$results = $connection->get('search/tweets', $theSearch);

// Action à effectuer pour chaque Tweet
foreach($results->statuses as $status) {
 $connection->post('favorites/create', array('id' => $status->id_str));
 $connection->post('favorites/create', array('id' => $status->id_str));
}
 
?>