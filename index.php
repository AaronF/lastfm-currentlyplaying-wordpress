<?php
include_once("config.php");
include_once("includes/class.comms.php");

$comms = new comms();

$body_string = "method=user.getrecenttracks&user=aaronpfisher&api_key=" . $api_key . "&format=json";

$recent_tracks = $comms->get($base_url, $body_string);
$recent_tracks = json_decode($recent_tracks, true);

if(count($recent_tracks) > 0){
	$last_track = $recent_tracks["recenttracks"]["track"][0];
	if(!empty($last_track)){
		echo $last_track["name"] . " by " . $last_track["artist"]["#text"];

		echo getElapsedTime((int)$last_track["date"]["uts"]);

	}

	echo "<pre>";
	print_r($last_track);
	echo "</pre>";
	
}

function getElapsedTime($fixed_time){
	if(!is_numeric($time))
		$time = strtotime($time);

	$periods = array("second", "minute", "hour", "day", "week", "month", "year", "age");
	$lengths = array("60","60","24","7","4.35","12","100");

	$now = time();

	$difference = $now - $time;
	if ($difference <= 10 && $difference >= 0)
		return $tense = 'just now';
	elseif($difference > 0)
		$tense = 'ago';
	elseif($difference < 0)
		$tense = 'later';

	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		$difference /= $lengths[$j];
	}

	$difference = round($difference);

	$period =  $periods[$j] . ($difference >1 ? 's' :'');
	return "{$difference} {$period} {$tense} ";
}

?>