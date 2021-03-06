<?php
/*
 #####                                            
#     # #####   ####  ##### # ###### #      #   # 
#       #    # #    #   #   # #      #       # #  
 #####  #    # #    #   #   # #####  #        #   
      # #####  #    #   #   # #      #        #   
#     # #      #    #   #   # #      #        #   
 #####  #       ####    #   # #      ######   # 

Under MIT licence
*/

/**********************
** Koray KIRCAOGLU ****
** koraym@gmail.com ***
***********************
* www.ohshiftlabs.com *
************2014*******
* Spotify PHP Class ***
** Free Distribution **
---- v1.3 -------------
**********************/


class spotifly {

//	protected $currenturl = "http://".$_SERVER["SERVER_NAME"]."/callback";
	protected $settings;
	static $version = "v1";
	static $baseurl = "https://api.spotify.com/";

	function __construct($config){

		$currURL = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$preconfig = array(
			"callbackurl"	=> urlencode($currURL),
			"scope"			=> "",
			"show_dialog"	=>	false
		);

    	if(!is_array($config) || !array_key_exists("clientid", $config) || !array_key_exists("clientsecret", $config)){
    		die("Wrong configuration values!");
    	}

		if (!session_id()) {
    	  session_start();//Start a session if not initialized.
    	}

		$this->settings = array_merge($preconfig,$config);

		//is user authenticated?
		if(isset($_GET["code"])){
			$_SESSION["spotify"] = (!isset($_SESSION["spotify"]))? array() : $_SESSION["spotify"];
			$_SESSION["spotify"]["login"] = true;
			$_SESSION["spotify"]["auth"] = false;
			$_SESSION["spotify"]["code"] = $_GET["code"];
		}

		//is error returned?
		if(isset($_GET["error"])){
			
		}
	}


	function login($state = NULL){

		$query = array(
			"client_id"		=>	$this->settings["clientid"],
			"response_type"	=>	$this->settings["clientsecret"],
			"redirect_uri"	=>	$this->settings["callbackurl"],
			"scope"			=>	$this->settings["scope"],
			"state"			=>	$state,
			"response_type"	=>	"code",
		);

		$loginurl = "https://accounts.spotify.com/authorize";
		$query = http_build_query($query);
		
		return $loginurl."?".$query;

	}

	function auth(){
		$authurl = "https://accounts.spotify.com/api/token";
		$query = array(
			"grant_type"		=>	"authorization_code",
			"code"				=>	$_SESSION["spotify"]["code"],
			"redirect_uri"		=>	$this->settings["callbackurl"],
			"client_id"			=>	$this->settings["clientid"],
			"client_secret"		=>	$this->settings["clientsecret"]
		);

		$auth = $this->postData($authurl,$query);

		if(is_array($auth) && !isset($auth["error"])){
			$_SESSION["spotify"]["auth"] = true;
			$_SESSION["spotify"]["access_token"]	= $auth["access_token"];
			$_SESSION["spotify"]["expires_in"]		= $auth["expires_in"];
			$_SESSION["spotify"]["refresh_token"]	= $auth["refresh_token"];


		}elseif( isset($auth["error"]) ){
			echo "Error: " . $auth["error"]."\nTry to get a new code."; //destroy session and start again.
		}

		return $auth;
	}

	function refreshToken($refreshToken = NULL){
		$refreshToken = ($refreshToken == NULL && isset($_SESSION["spotify"]["refresh_token"]))? $_SESSION["spotify"]["refresh_token"]: NULL;

		if($refreshToken==NULL){
			$refresh = array("error"=>"There is no refresh token");
		}else{
			$tokenurl = "https://accounts.spotify.com/api/token";
			$query = array(
				"grant_type"	=>	"refresh_token",
				"refresh_token"	=>	$refreshToken
			);
			$header = array("Authorization: Basic ". base64_encode($this->settings["clientid"].":".$this->settings["clientsecret"]));
			$refresh = $this->postData($tokenurl, $query, $header);

			if(isset($refresh["access_token"])){
				$_SESSION["spotify"]["access_token"] = $refresh["access_token"];
			}

		}

		return $refresh;
	}



	function getUser($token=NULL){
		$usertoken = (isset($token)) ? $token : $_SESSION["spotify"]["access_token"];
		$url = self::$baseurl . self::$version . "/me";
		$header = array("Authorization: Bearer ".$usertoken);

		$user = $this->postData($url,NULL,$header);
		return $user;
	}

	function getTrack($id){
		return $this->endPointData("tracks/".$id);
	}

	function getAlbum($id){
		return $this->endPointData("albums/".$id);
	}

	function createPlaylist($userid,$name,$isPublic=true){
		//EDIT // /users/{user_id}/playlists/{playlist_id}/tracks
		$endpoint = "/users/".$userid."/playlists";
		$params = array("name" => $name, "public" => $isPublic);
		$this->endPointData($endpoint,$params);
	}


	function search($item,$type,$limit=50){
		$params = array(
			"q"		=>$item,
			"type"	=>$type, //Values are: album, artist, playlist, track
			"limit"	=>$limit
		);

		return $this->endPointData("search", $params);
	}


	function endPointData($endpoint, $params=NULL, $token=NULL){
		if($this->isOAuthRequired($endpoint)){
			$usertoken = (isset($token)) ? $token : is($_SESSION["spotify"]["access_token"]);
			$header = array("Authorization: Bearer ".$usertoken);
		}else{
			$header = NULL;			
		}

		$querystring = "?";
		if(isset($params)){
			$querystring.= is_array($params)? http_build_query($params):$params;
		}

		$url = self::$baseurl . self::$version . "/".$endpoint.$querystring;
		$endpoint = $this->postData($url, NULL, $header);

		return $endpoint;
	}

	function isOAuthRequired($endpoint){

		$OAuthRequiredList = array("browse","me","users",);
		if(strpos($endpoint, "/") !== false){//PHP 5.3
			$endpoint = explode("/", $endpoint);
			$endpoint = $endpoint[0];
		}

//		$endpoint = (strpos($endpoint, "/") === false)? $endpoint : explode("/", $endpoint)[0]; //PHP 5.5

		return in_array($endpoint, $OAuthRequiredList);

	}



	function postData($url, $query=NULL, $headers=NULL){

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $url);

		if(is_array($headers)){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		if(is_array($query)){
			$querybuilded = http_build_query($query);
			curl_setopt($curl, CURLOPT_POST, count($query));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $querybuilded);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	
		if ($result = curl_exec($curl)){
			return json_decode($result,true);
		}else{
			return array("error"=>'Curl error: ' . curl_error($curl));
		}
	}


	private function uniqueId(){
		return uniqid(microtime());
	}
	
}

?>