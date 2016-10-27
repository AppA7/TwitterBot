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
        if($since_id){
            printf("Since_ID = %d </br>", $since_id);
        } else {
        	$since_id = 0;
        }
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

                    echo '<b>Username:</b>'. $tweet->user->screen_name;
                }
            }

            /* setting new max id */
            $this->setSinceId($max_id);

            return $search;
        }
    }

    public function AddRepliesToSearch($tweet, $reply) {
	    try{
			$this->oauth->post('statuses/update', array('status' => '@' . $tweet->user->screen_name . ' ' . $reply,'in_reply_to_status_id' => $tweet->id));
	    }catch(OAuthException $ex){
	        echo 'ERROR: '.$ex->lastResponse;
	    }
    }

    public function shortenUrl($url, $bitly_login, $bitly_key)
    {
        // Check is url contains http:// header
        if((strpos($url, 'http://') === false && strpos($url, 'https://') === false))
        {
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

    public function styleHashtag($hashtag)
    {
        // Check is hashtag contains # sympbol or not
        if (strpos($hashtag, '#') === false) {
            $hashtag = '#'.$hashtag;
        }

        return $hashtag;
    }
}