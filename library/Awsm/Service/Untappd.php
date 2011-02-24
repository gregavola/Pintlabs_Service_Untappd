<?php
/**
 * Provides a service into the Untappd public API.
 * 
 * @see    http://untappd.com/api/dashboard
 * @author Jason Austin - http://jasonawesome.com - @jason_austin
 *
 */
class Awsm_Service_Untappd
{
    /**
     * Base URI for the Untappd service
     * 
     * @var string
     */
    const URI_BASE = 'http://api.untappd.com/v3';
    
    /**
     * Username and password hash for the request signing
     * 
     * @var string
     */
    protected $_upHash = '';
    
    /**
     * API key
     * 
     * @var string
     */
    protected $_apiKey = '';
    
    /**
     * Stores the last parsed response from the server
     * 
     * @var stdClass
     */
    protected $_lastParsedResponse = null;
    
    /**
     * Stores the last raw response from the server
     * 
     * @var string
     */
    protected $_lastRawResponse = null;

    /**
     * Stores the last requested URI
     * 
     * @var string
     */
    protected $_lastRequestUri = null;
    
    /**
     * Constructor
     * 
     * @param string $apiKey Untappd-provided API key
     * @param string *optional* $username Untappd username
     * @param string *optional* $password Untappd password
     */
    public function __construct($apiKey, $username = '', $password = '')
    {
        $this->_apiKey = (string) $apiKey;
        
        $this->setAuthenticatedUser($username, $password);
    }
    
    /**
     * Sets the authenticated user for untappd.  If username and
     * password vars are set to empty string, will null out the
     * password hash needed for authenticated methods.
     * 
     * @param string $username Untappd username
     * @param string $password Untappd password
     */
    public function setAuthenticatedUser($username, $password)
    {
        if ($username != '' && $password != '') {
            $this->_upHash = (string) $username . ':' . md5((string) $password);
        } else {
            $this->_upHash = null;
        }
        
        return $this;
    }
    
    /**
     * Returns the authenticated user's friend feed
     * 
     * @param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function myFriendFeed($since = '', $offset = '')
    {
        $args = array(
            'since'  => $since, 
            'offset' => $offset
        );
        
        return $this->_request('feed', $args, true);
    }
        
    /**
     * Gets a user's info
     * 
     * @param string *optional* $username Untappd username
     */
    public function userInfo($username = '')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
        
        $args = array(
            'user' => $username
        );
        
        return $this->_request('user', $args);
    }
    
    /**
     * Gets a user's checkins
     * 
     * @param string *optional* $username Untappd username
     * @param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function userFeed($username = '', $since = '', $offset = '')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
                
        $args = array(
            'user'   => $username, 
            'since'  => $since, 
            'offset' => $offset
        );
        
        return $this->_request('user_feed', $args);
    }
    
    /**
     * Gets a user's distinct beer list
     * 
     * @param string *optional* $username Untappd username
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function userDistinctBeers($username = '', $offset = '')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
                
        $args = array(
            'user'   => $username, 
            'offset' => $offset
        );
        
        return $this->_request('user_distinct', $args);
    }
    
    /**
     * Gets a list of a user's friends
     * 
     * @param string *optional* $username Untappd username
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function userFriends($username = '', $offset = '')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
                
        $args = array(
            'user'   => $username, 
            'offset' => $offset
        );
        
        return $this->_request('friends', $args);
    }
    
    /**
     * Gets a user's wish list
     * 
     * @param string *optional* $username Untappd username
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function userWishlist($username = '', $offset = '')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
                
        $args = array(
            'user'   => $username, 
            'offset' => $offset
        );
        
        return $this->_request('wish_list', $args);
    }

    
    /**
     * Gets a list of a user's badges they have won
     * 
     * @param string *optional* $username Untappd username
     * @param (all|beer|venue|special) *optional* $sort order to sort the badges in
     */
    public function userBadge($username = '', $sort = 'all')
    {
        if ($username == '' && is_null($this->_upHash)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('username parameter or Untappd authentication parameters must be set.');
        }
                
        $validSorts = array('all', 'beer', 'venue', 'special');
        if (!in_array($sort, $validSorts)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Sort parameter must be one of the following: ' . implode(', ', $validSorts));            
        }
        
        $args = array(
            'user' => $username, 
            'sort' => $sort
        );
        
        return $this->_request('user_badge', $args);
    }
    
    /**
     * Gets a beer's critical info
     * 
     * @param int $beerId Untappd beer ID
     */
    public function beerInfo($beerId)
    {
        if (empty($beerId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('beerId parameter must be set and not empty');            
        }
                
        $args = array(
            'bid' => $beerId
        );
        
        return $this->_request('beer_info', $args);
    }
    
    /**
     * Searches Untappd's database to find beers matching the query string
     * 
     * @param string $searchString query string to search
     */
    public function beerSearch($searchString)
    {
        if (empty($searchString)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('searchString parameter must be set and not empty');            
        }
                
        $args = array(
            'q' => $searchString
        );
        
        return $this->_request('beer_search', $args);
    }
    
    /**
     * Gets all checkins for a specified beer
     * 
     * @param int $beerId Untappd ID of the beer to search for
     * @param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function beerFeed($beerId, $since = '', $offset = '')
    {
        if (empty($beerId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('beerId parameter must be set and not empty');            
        }
        
        $args = array(
            'bid'    => $beerId,
            'since'  => $since,
            'offset' => $offset,
        );
        
        return $this->_request('beer_checkins', $args);          
    }
    
    /**
     * Gets information about a given venue
     * 
     * @param int $venueId Untappd ID of the venue
     */
    public function venueInfo($venueId)
    {
        if (empty($venueId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('venueId parameter must be set and not empty');            
        }
                
        $args = array(
            'venue_id' => $venueId,
        );
        
        return $this->_request('venue_info', $args);          
    }

    /**
     * Gets all checkins at a given venue
     * 
     * @param int $venueId Untappd ID of the venue
     * @param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function venueFeed($venueId, $since = '', $offset = '')
    {
        if (empty($venueId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('venueId parameter must be set and not empty');            
        }
                
        $args = array(
            'venue_id' => $venueId,
            'since'    => $since,
            'offset'   => $offset,
        );
        
        return $this->_request('venue_checkins', $args);          
    }
    
    /**
     * Gets all for beers of a certain brewery
     * 
     * @param int $breweryId Untappd ID of the brewery
     * @param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     */
    public function breweryFeed($breweryId, $since = '', $offset = '')
    {
        if (empty($breweryId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('breweryId parameter must be set and not empty');            
        }
        
        $args = array(
            'brewery_id' => $breweryId,
            'since'      => $since,
            'offset'     => $offset,
        );
        
        return $this->_request('brewery_checkins', $args);          
    }
    
    /**
     * Gets the public feed of checkings, also known as "the pub"
     * 
     *@ param int *optional* $since numeric ID of the latest checkin
     * @param int *optional* $offset offset within the dataset to move to
     * @param float *optional* $longitude longitude to filter public feed
     * @param float *optional* $latitude latitude to filter public feed
     */
    public function publicFeed($since = '', $offset = '', $longitude = '', $latitude = '')
    {
        $args = array(
            'since'  => $since, 
            'offset' => $offset, 
            'geolng' => $longitude,
            'geolat' => $latitude
        );
        
        return $this->_request('thepub', $args);
    }
    
    /**
     * Gets the trending list of beers based on location
     * 
     * @param (all|macro|micro|local) *optional* $type Type of beers to search for
     * @param int *optional* $limit Number of results to return
     * @param (daily|weekly|monthly) *optional* $age Age of checkins to consider
     * @param float *optional* $latitude Numeric latitude to filter the feed
     * @param float *optional* $longitude Numeric longitude to filter the feed
     */
    public function publicTrending($type = 'all', $limit = 10, $age = 'daily', $latitude = '', $longitude = '')
    {
        $validTypes = array('all', 'macro', 'micro', 'local');
        if (!in_array($type, $validTypes)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Type parameter must be one of the following: ' . implode(', ', $validTypes));
        }
        
        $validAges = array('daily', 'weekly', 'monthly');
        if (!in_array($age, $validAges)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Age parameter must be one of the following: ' . implode(', ', $validAges));
        }
        
        // Set limit to default if it is outside of the available params
        if ($limit > 10 || $limit < 1) {
            $limit = 10;
        }
        
        $args = array(
            'type'   => $type,
            'limit'  => $limit,
            'age'    => $age,
            'geolat' => $latitude,
            'geolng' => $longitude,
        );
        
        return $this->_request('trending', $args);        
    }
    
    /**
     * Gets the details of a specific checkin
     * 
     * @param int $checkinId Untappd checkin ID
     */
    public function checkinInfo($checkinId)
    {
        if (empty($checkinId)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('checkinId parameter must be set and not empty');            
        }
                
        $args = array(
            'id' => $checkinId
        );
        
        return $this->_request('details', $args);
    }
    
    // Waiting for validation from untappd on arguments and details for write API
    /*
    public function checkin()
    {
        $args = array(
            
        );
        
        return $this->_request('', $args);          
    }
    
    public function checkinComment()
    {
        $args = array(
            
        );
        
        return $this->_request('', $args);          
    }
    
    public function checkinRemoveComment()
    {
        $args = array(
            
        );
        
        return $this->_request('', $args);          
    }
    
    public function checkinToast()
    {
        $args = array(
            
        );
        
        return $this->_request('', $args);          
    }
    
    public function checkinRemoveToast()
    {
        $args = array(
            
        );
        
        return $this->_request('', $args);          
    }
    */
    
    /**
     * Sends a request using curl to the required URI
     * 
     * @param string $method Untappd method to call
     * @param array $args key value array or arguments
     * 
     * @throws Awsm_Service_Untappd_Exception
     * 
     * @return stdClass object
     */
    protected function _request($method, $args, $requireAuth = false)
    {
        $this->_lastRequestUri = null;
        $this->_lastRawResponse = null;
        $this->_lastParsedResponse = null;
        
        if (is_null($this->_upHash) && $requireAuth) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Method requires Untappd user authentication which is not set.');
        }
        
        // Append the API key to the args passed in the query string
        $args['key'] = $this->_apiKey;

        // remove any unnecessary args from the query string
        foreach ($args as $key => $a) {
            if ($a == '') {
                unset($args[$key]);
            }
        }
        
        $this->_lastRequestUri = self::URI_BASE . '/' . $method . '?' . http_build_query($args);
        
        // Set curl options and execute the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_lastRequestUri);
        
        if (!is_null($this->_upHash)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->_upHash);
        }
          
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $this->_lastRawResponse = curl_exec($ch);
        
        if ($this->_lastRawResponse === false) {
            
            $this->_lastRawResponse = curl_error($ch);
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('CURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Response comes back as JSON, so we decode it into a stdClass object
        $this->_lastParsedResponse = json_decode($this->_lastRawResponse);
        
        // If the http_code var is not found, the response from the server was unparsable
        if (!isset($this->_lastParsedResponse->http_code)) {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Error parsing response from server.');
        }
        
        // Server provides error messages in http_code and error vars.  If not 200, we have an error.
        if ($this->_lastParsedResponse->http_code != '200') {
            require_once 'Awsm/Service/Untappd/Exception.php';
            throw new Awsm_Service_Untappd_Exception('Untappd Service Error ' .  
                $this->_lastParsedResponse->http_code . ': ' .  $this->_lastParsedResponse->error);
        }
        
        return $this->getLastParsedResponse();
    }    
    
    /**
     * Gets the last parsed response from the service
     * 
     * @return null|stdClass object
     */
    public function getLastParsedResponse()
    {
        return $this->_lastParsedResponse;
    }
    
    /**
     * Gets the last raw response from the service
     * 
     * @return null|json string
     */
    public function getLastRawResponse()
    {
        return $this->_lastRawResponse;
    }
    
    /**
     * Gets the last request URI sent to the service
     * 
     * @return null|string
     */
    public function getLastRequestUri()
    {
        return $this->_lastRequestUri;
    }
}