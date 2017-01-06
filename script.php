<?php

require 'vendor/autoload.php';
require 'sendgrid-php/sendgrid-php.php';
require 'sendgrid-php/vendor/autoload.php';

$host_name = getenv('HOST_NAME');
$user_name = getenv('USER_NAME');
$pass_word = getenv('PASS_WORD');
$database_name = getenv('DATABASE_NAME');
$port = getenv('PORT');

date_default_timezone_set('America/New_York');

$BLACK_LIST = array('jficca', 'rtursky', 'mjeffers');

$SENDGRID_USERNAME = getenv('SENDGRID_USERNAME');

$SENDGRID_PASSWORD = getenv('SENDGRID_PASSWORD');

			
$short_connect = new mysqli($host_name, $user_name, $pass_word, $database_name, $port);

$sqlUser = 'SELECT DISTINCT email, name FROM mrbs_users WHERE name = ANY('
	        . ' SELECT DISTINCT create_by FROM mrbs_entry'
	        . ' WHERE EXTRACT(YEAR FROM FROM_UNIXTIME(start_time))= EXTRACT(YEAR FROM NOW())'
	        . ' AND EXTRACT(MONTH FROM FROM_UNIXTIME(start_time))= EXTRACT(MONTH FROM NOW())'
	        . ' AND EXTRACT(DAY FROM FROM_UNIXTIME(start_time))= EXTRACT(DAY FROM NOW())'
	        . ' )';
			

$sqlEntry = 'SELECT * FROM mrbs_entry'
        . ' WHERE EXTRACT(YEAR FROM FROM_UNIXTIME(start_time))= EXTRACT(YEAR FROM NOW())'
        . ' AND EXTRACT(MONTH FROM FROM_UNIXTIME(start_time))= EXTRACT(MONTH FROM NOW())'
        . ' AND EXTRACT(DAY FROM FROM_UNIXTIME(start_time))= EXTRACT(DAY FROM NOW())'; 

$sqlRooms = 'SELECT DISTINCT room_name, id FROM mrbs_room '; 

$entryQuery = $short_connect->query($sqlEntry);
$roomQuery = $short_connect->query($sqlRooms);
$userQuery = $short_connect->query($sqlUser);

$rooms= array();

if (($roomQuery) && ($roomQuery->num_rows > 0)){
	$results = array();
	while ($entryRow = $roomQuery->fetch_assoc()){
		$results[] = $entryRow;
		foreach ($results as $res){
			$room_id = $res['id'];
			$room_name = $res['room_name'];
			if(!(array_key_exists($room_id, $rooms))){
				$rooms[$room_id] = $room_name;
			}
		}
	}
	$roomQuery->free();
}

$users = array();

if (($userQuery) && ($userQuery->num_rows > 0)){
	$results = array();
	while ($entryRow = $userQuery->fetch_assoc()){
		$results[] = $entryRow;
		foreach ($results as $res){
			$name = $res['name'];
			$email = $res['email'];
			if(!(array_key_exists($name, $users))){
				$users[$name] = $email;
			}
		}
	}
	$userQuery->free();
}

$master = array();
$checkUniqueVals = array();

if (($entryQuery) && ($entryQuery->num_rows > 0)){
	$results = array();
	$emails = '';
	while ($entryRow = $entryQuery->fetch_assoc()){
		$results[] = $entryRow;
		$results = array_unique($results, SORT_REGULAR);
	
		foreach ($results as $res){
			$start = $res['start_time'];
			$author = $res['create_by'];
			$email = $users[$author];
			$room_id = $res['room_id'];
			$room_name = $rooms[$room_id];
			$time = date("Y-m-d g:i:s A", $start);
			$type = $res['type'];
			if($type!='B'){
				if(!array_key_exists($author, $master)){
					$master[$author] = array();
					$master[$author]['entries'] = array();
					$master[$author]['name'] = $author;
					$master[$author]['email'] = $email;
				}
				$val = $author.$room_name.$time;
				if(!(in_array($val, $checkUniqueVals))){
					$entry = array();
					$entry['equipment'] = $room_name;
					$entry['time'] = $time;
					array_push($master[$author]['entries'], $entry);
					array_push($checkUniqueVals, $val);
				}
			}
		}
	}
	$entryQuery->free();
}

$short_connect->close();

print_r($master);




$toFinal = getenv('EMAIL');
$finalMessage = "EMAIL_LOG:<br>";

$subject = "[DFAB] Reservation Reminder";

$message = "<b>Dfab Reservation Daily Reminder</b><br>";
$message .="Hello user,<br>Our records indicate you have at least one appointment today.<br>";
$message .="Please review reservations listed below.<br>";
$message .="Please show up on time.<br>";
$message .="If you think you might not be able to arrive on time, please cancel the reservation so others may utilize the equipment.<br>";
$message .="Thank you for your consideration of others.<br>";
$message .="Fondly, <br> -The_Reservation_System<br>";

$sendgrid = new SendGrid($SENDGRID_USERNAME, $SENDGRID_USERNAME);


foreach($master as $list){
	$entryList = $list['entries'];
	$to = $list['email'];
	$name = $list['name'];

	$appendMessage = "<br><hr><br>Reservations for ". $name." today:<br>";

	
	foreach($entryList as $entry){
		$equipment = $entry['equipment'];
		$startTime = $entry['time'];
		$appendMessage.="<br>". $startTime . " on: " . $equipment;
	}
	$appendMessage = $message . $appendMessage;
	$finalMessage.="<br><br><hr><br><br>" . $appendMessage;
	if(in_array($name, $BLACK_LIST)){
		$finalMessage.="<br>[[User on DO-NOT-MAIL list; will not receive this email]]<br>";
	}
	
	if(!is_null($to)){
		if(!in_array($name, $BLACK_LIST)){
			$email = new SendGrid\Email();
			$email->addTo($to)
			    ->setFrom('dfab_reservation@andrew.cmu.edu')
			    ->setSubject($subject)
			    ->setHtml($appendMessage);
			$sendgrid->send($email);
		}
	} 
}

$email = new SendGrid\Email();
$email->addTo($toFinal)
    ->setFrom('dfab_reservation@andrew.cmu.edu')
    ->setSubject($subject)
    ->setHtml($finalMessage);

$sendgrid->send($email);


?>
