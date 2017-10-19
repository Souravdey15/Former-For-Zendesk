<?php
include("config.php");

/* Notes
 *
 * This script expects there to be a config.php file in the same directory as this file.
 * There is an included config.example which you need to edit with your own values.
 * Remember: ZDURL needs to be of the form https://subdomain.zendesk.com/api/v2 with no trailing slash
 *
 * Instructions: Much like before where you would create fields that had z_, in this setup,
 * for custom fields, you'll create text boxes that have c_ but are followed by the custom field ID number.
 * In addition, note the changed config.example.php where we now have a new setting (Custom) which needs to be true
 * in order to process custom fields. 
 *
 */

function curlWrap($url, $json)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
	curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	curl_close($ch);
	$decoded = json_decode($output);
	return $decoded;
}

$arr = array();

foreach($_POST as $key => $value){
	if(preg_match('/^z_/i',$key)){
		$arr[strip_tags($key)] = strip_tags($value);
	}
}
$ticket = array('ticket' => array('subject' => $arr['z_subject'], 'comment' => array("value"=>$arr['z_description'] ), 'requester' => array('name' => $arr['z_name'], 'email' => $arr['z_requester'])));
if(CUSTOM){
	foreach($_POST as $key => $value){
		if(preg_match('/^c_/i',$key)){
			$id = str_replace('c_', '', strip_tags($key));
			$value = strip_tags($value);
			$cfield=array('id'=>$id, 'value'=>$value);
			$ticket['ticket']['custom_fields'][]=$cfield;
		}
	}
}
$ticket = json_encode($ticket);
$return = curlWrap("/api/v2/tickets.json", $ticket);
?>
