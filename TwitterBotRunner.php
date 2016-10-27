<?php
/**
 * Created by IntelliJ IDEA.
 * User: Anthony
 * Date: 13/10/2016
 * Time: 11:38
 */
require 'twitteroauth/TwitterOAuth.php';

class TwitterBotRunner{

    protected $url_update = '/statuses/update.json';
    protected $url_search = '/search/tweets.json';
    protected $url_verify = '/account/verify_credentials.json';
    protected $url_token = 'https://twitter.com/oauth/request_token';
    protected $url_token_access = 'https://twitter.com/oauth/access_token';
    protected $url_auth = 'http://twitter.com/oauth/authorize';

    private $replies;
    private $oauth;
    private $screenName;

    public function __construct($key, $secret){
        $this->oauth = new TwitterOAuth($key, $secret);
    }

    public function setToken($token, $secret){
        $this->oauth->setOauthToken($token, $secret);
    }

    public function addReply($terms,$regex,$type){
        $this->replies = array('terms' => $terms,'regex' => $regex,'type' => $type);
    }

    public function getSinceId($file='since_id'){
        $since_id = @file_get_contents($file);
        if(!$since_id){
            $since_id = 0;
        }
        printf("Since_ID = %d </br>", $since_id);
        return $since_id;
    }

    public function setSinceId($max_id=null,$file='since_id'){
        file_put_contents($file, $max_id);
    }

    private function verifyAccountWorks(){
        try{
            $this->oauth->oauth($this->url_verify, array(), 'GET');
            $response = json_decode($this->oauth->getLastResponse());
            $this->screenName = $response->screen_name;
            return true;
        }catch(Exception $ex){
            return false;
        }
    }

    public function writeTweet($status){
        $array = array(
            'status' => $status // Status to write on twitter account
        );
        // OAUTH_HTTP_METHOD_POST : use POST method for this request
        $response = $this->oauth->oauth($this->url_update, $array);

        print_r($response);
    }

    public function searchKeywordTweets($keyword)
    {
        printf("## Starting search with keyword : %s </br>", $keyword);

        $since_id = $this->getSinceId();
        $max_id = $since_id;

        if ($this->verifyAccountWorks()) {
            /* find every tweet since last ID, or the maximum lasts tweets if no since_id */
            $request = sprintf($this->url_search, urlencode($keyword), $since_id);

            $query = array(
                "q" => $keyword,
                "count" => 50,
                "result_type" => "recent",
            	"exclude" => "retweets",
                "lang" => "fr",
                "exclude" => "retweets",
                "since_id" => $since_id,
            );
            $search = $this->oauth->get("search/tweets", $query);

            if ($search) {
                if ($search->search_metadata->max_id_str > $max_id) {
                    $max_id = $search->search_metadata->max_id_str;
                }

                $i = 0;
                foreach ($search->statuses as $tweet) {
                    echo '<b><a href="https://twitter.com/' . $tweet->user->screen_name . '" target="_blank" style="color:red">@' . $tweet->user->screen_name . '</a> :</b> <a href="https://twitter.com/' . $tweet->user->screen_name . '/status/' . $tweet->id . '" target="_blank" style="color:black;text-decoration:none">' . $tweet->text . '</a></b><br>';
                }
                //echo 'Terms #'.$key.' : '.$i.' valid(s)'."\n<br>";
            }

            /* setting new max id */
            $this->setSinceId($max_id);

            return $search;
        }
    }

    public function AddRepliesToSearch($findedTweet, $messageToTweet)
    {
        foreach ($findedTweet->statuses as $tweet){
            /* If you are the author of the tweet, we ignore it */
            if ($tweet->user->screen_name == $this->screenName){
                continue;
            }
            /* if tweet is a quote (like a RT), we ignore it */
            if($tweet->is_quote_status){
                continue;
            }

            $pass = false;

            switch($t['type']){
                case('dogbot'):
                    //echo '<b><a href="https://twitter.com/'.$tweet->user->screen_name.'" target="_blank" style="color:red">@'.$tweet->user->screen_name.'</a> :</b> <a href="https://twitter.com/'.$tweet->user->screen_name.'/status/'.$tweet->id.'" target="_blank" style="color:black;text-decoration:none">'.$tweet->text.'</a></b><br>';

                    $t['word'] = null; /* initialisation variable mot additionnel */

                    /* if the regex specified found something, we try to get the content */
                    if(preg_match($t['regex'], $tweet->text, $content)){
                        /* get the longest word after keyword */
                        $words = explode(' ',$content[1]);
                        $maxword = null;$maxlength = 0;
                        foreach($words as $w){
                            $wlength = strlen($w);
                            if($wlength >= $maxlength){
                                $maxword = $w;
                                $maxlength = $wlength;
                            }
                        }
                        if($maxword){
                            $t['word'] = $maxword;
                        }
                    }

                    $pass = true;
                    $i++;
                    break;
                default:
                    echo 'ERROR: NO TYPE DEFINED';
                    break;
            }

            if($pass){
                $this->sendReply($tweet, $t);
                /* wait 100ms */
                usleep(100000);
            }
        }
        $length = strlen($messageToTweet);

        // We should trim down the title if it's too long
        // So that our tweets are 120 characters or less
        if (strlen($title) > 120-$length)
            $shorttitle = substr($title, 0, 117-$length) . "...";
        else
            $shorttitle = $title;

        // Add the title to the message
        $message = $shorttitle.$message;

        // Post the message to Twitter
        $oauth->OAuthRequest('https://twitter.com/statuses/update.xml',
            array('status' => $message), 'POST');
        // Wait a couple of mintes before the next tweet
        // Don't try and flood Twitter
        // Only 150 API calls per hour, use them wisely.
        sleep(rand(60,120));
    }

    public function shortenUrl($url, $bitly_login, $bitly_key)
    {
        // Check is url contains http:// header
        if (strpos($url, 'http://') !== true || strpos($url, 'https://') !== true ) {
            $url = 'http://'.$url;
        }

        $url_escaped = urlencode($url);
        $bitly_url = "http://api.bit.ly/shorten?version=2.0.1";
        $bitly_url .= "&longUrl=$url_escaped";
        $bitly_url .= "&login=$bitly_login&apiKey=$bitly_key";

        $shortened_url = json_decode(file_get_contents($bitly_url));

        if ($shortened_url->errorCode == 0) {
            // Retrieve the shortened url from the json object
            foreach ($shortened_url->results as $key => $value)
                $shorturl = $value->shortUrl;
        }
            return $shorturl;
    }
}











/*
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

$theSearch = array('q' => '#Paris', 'lang' => 'fr', 'count' => 5);


// Store the feed settings into $feed
$feed = $feeds[$feed_name];

// Fetch the feed and store the prefix
$rss = fetch_rss($feed[
}"url"]);
$postfix = $feed["postfix"];

// Loop through the feed items
foreach ($rss->items as $item)
{
    // All simple enough here
    $title = trim($item["title"]);
    $url = $item["link"];

    // Let's make sure our feeds are in English, allow spaces and punctuation
    if (ereg('^[[:alnum:][:blank:][:punct:]]+$', $title))
    {
        // Escape the URL for bit.ly shortening and then shorten the link
        // This is the place where you have to use your bit.ly login
        // And the API key
        $url_escaped = urlencode($url);
        $bitly_url = "http://api.bit.ly/shorten?version=2.0.1";
        $bitly_url .= "&longUrl=$url_escaped";
        $bitly_url .= "&login=$bitly_login&apiKey=$bitly_key";

        $shortened_url = json_decode(file_get_contents($bitly_url));

        // If everything went okay, go on
        if ($shortened_url->errorCode == 0)
        {
            // Retrieve the shortened url from the json object
            foreach ($shortened_url->results as $key => $value)
                $shorturl = $value->shortUrl;

            // Form a new message from the short URL and the postfix
            $message = " $shorturl $postfix";
            $length = strlen($message);

            // We should trim down the title if it's too long
            // So that our tweets are 120 characters or less
            if (strlen($title) > 120-$length)
                $shorttitle = substr($title, 0, 117-$length) . "...";
            else
                $shorttitle = $title;

            // Add the title to the message
            $message = $shorttitle.$message;

            // Post the message to Twitter
            $oauth->OAuthRequest('https://twitter.com/statuses/update.xml',
                array('status' => $message), 'POST');

            // Wait a couple of mintes before the next tweet
            // Don't try and flood Twitter, remember, you have
            // Only 150 API calls per hour, use them wisely.
            sleep(rand(60,120));
        }
    }
}
*/