<?php
require_once 'twitteroauth/twitteroauth.php';

define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');
define('ACCESS_TOKEN', '');
define('ACCESS_TOKEN_SECRET', '');

if(isset($_REQUEST['lng'])){
	$lng = $_REQUEST['lng']; 
	}
if(isset($_REQUEST['lat'])){
	$lat = $_REQUEST['lat'];
	}
if(isset($_REQUEST['rad'])){                                 
	$rad = $_REQUEST['rad'];
	}
if(isset($_REQUEST['hashtag'])){                                 
	$hashtag = $_REQUEST['hashtag'];
	}
	
function search(array $query){
	$toa = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
	return $toa->get('search/tweets', $query);
}//Fin serarch	

$max_id = "";
$contador = 0; 
$twuit = new Twuit();
$twuits = []; 
foreach (range(1, 1) as $i) { // up to 5 pages
	$query = array(
		"q" => "$hashtag",
		"geocode" => "$lat,$lng,$rad",
		"count" => 100,
		//"lang" => "es",
		//"result_type" => "popular",  //recent
		"max_id" => $max_id
	);
	
	$results = search($query);
	if($results->errors[0]->message == "Rate limit exceeded"){
		$error = array('error' => 'Rate limit exceeded');
		echo json_encode($error);
		exit(0);	
		}
	else{
		foreach ($results->statuses as $result) {
			$contador ++;
		
			$twuit = new Twuit();
			$twuit->user = $result->user->screen_name;
			$twuit->lng = $result->geo->coordinates[0];
			$twuit->lat = $result->geo->coordinates[1];
			$twuit->text = $result->text;
			$twuit->created_at = date("l M j \- g:ia",strtotime($result->created_at));
			$twuit->profile_image_url = $result->user->profile_image_url;
			$twuit->profile_background_image_url = $result->user->profile_background_image_url;
			$twuit->id_str = $result->id_str;
			
			$twuits[$contador] = $twuit;
			$max_id = $result->id_str; // Set max_id for the next search page
		}
	}	
}	
echo json_encode($twuits);
exit(0);	
	

class Twuit {
	public $user = "";
	public $lng  = "";
	public $lat = "";
	public $text = "";
	public $created_at = "";
	}

?>
