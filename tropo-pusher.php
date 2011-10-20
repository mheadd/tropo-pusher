<?php

// Include requires classes.
require 'classes/tropo/tropo.class.php';
require 'classes/limonade/limonade.php';
require 'classes/pusher/Pusher.php';

// User defined constants for interacting with Pusher platform.
define("PUSHERAPP_AUTHKEY", "");
define("PUSHERAPP_SECRET", "");
define("PUSHERAPP_APPID", "");
define("PUSHER_CHANNEL", "tropo-color");
define("PUSHER_EVENT", "change");

/**
 * 
 * Object used to hold country-specific settings.
 *
 */
class CountryObject {
	public $name;
	public $recognizer;
	public $voice;
	public $choices;
	public $welcome_prompt;
	public $color_prompt;
	public $error_prompt;

	public function __construct($name, $recognizer, $voice, $choices, $welcome_prompt, $color_prompt, $error_prompt) {
		$this->name = $name;
		$this->recognizer = $recognizer;
		$this->voice = $voice;
		$this->choices = $choices;
		$this->welcome_prompt = $welcome_prompt;
		$this->color_prompt = $color_prompt;
		$this->error_prompt = $error_prompt;		
	}
}

function getCountryName($calledID) {
	switch(substr($calledID, 0, 1)) {
		case '4':
			return 'UK'; 
			break;
			
		case '3':
			return 'ESP';
			break;
			
		default:
			return 'USA';
			break;
	}
}


/**
 *
 * Utility method for setting country-specific settings based on country name.
 * @param string $calledID
 */
function getCountrySettings($country) {
	switch ($country) {
		case 'UK':
			return new CountryObject(
							'UK',
							'en-gb', 
							'Elizabeth', 
							'red,blue,green,yellow,white,stop', 
							'Welcome to the Tropo Pusher demo.', 
							'Say the name of the color you want to see. When done, say stop.', 
							'Sorry mate, there seems to be a problem.'	
			);
			break;
		
		case 'ESP':
			return new CountryObject(
							'Spain',
							'es-es', 
							'Jorge', 
							'rojo,azul,verde,amarillo,blanco,parada', 
							'Bienvenido a la demostraci—n Pusher Tropo.', 
							'Diga el nombre del color que quieres ver. Cuando haya terminado, dejar de decir.', 
							'Lo sentimos, no parece ser un problema.'
			);
			break;
			
		default:
			return new CountryObject(	
							'USA',
							'en-us', 
							'Victor', 
							'red,blue,green,yellow,white,stop', 
							'Welcome to the Tropo Pusher demo.', 
							'Say the name of the color you want to see. When done, say stop.', 
							'Sorry, there seems to be a problem.'	
			);
			break;
}
}

/**
 * Starting point in Tropo call flow.
 */
dispatch_post('/', 'displayCall');
function displayCall() {

	// Get the calledID from the Trop Session at beginning of call.
	$session = new Session();
	$to_info = $session->getTo();
	$calledID = $to_info['id'];
	$country = (strlen($calledID) == 10) ? 'USA' : getCountryName($calledID);
	$country_settings = getCountrySettings($country);

	// Format payload for pushing to browser instances.
	$payload = json_encode(array("type" => "call_info", "called_id" => $calledID, "country" => $country_settings->name));

	// Create a new instance of Pusher object.
	$pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true);

	// Push CalledID and country name for display on web page.
	$pusher->trigger(PUSHER_CHANNEL, PUSHER_EVENT, $payload);

	// Send JSON to Tropo to play welcome message to caller.
	$tropo = new Tropo();
	$tropo->say($country_settings->welcome_prompt, array("voice" => $country_settings->voice));
	$tropo->on(array("event" => "continue", "next" => "tropo-pusher.php?uri=prompt&country=" . $country));
	$tropo->renderJSON();

}

/**
 * Hanlder and method for promoting caller for color selection.
 */
dispatch_post('/prompt', 'promptCaller');
function promptCaller() {
	
	$country = $_REQUEST['country'];
	$country_settings = getCountrySettings($country);	
	
	// Send prompt and question to caller.
	$tropo = new Tropo();
	$tropo->ask($country_settings->color_prompt, array("attempts" => 3, "choices" => $country_settings->choices, "voice" => $country_settings->voice, "recognizer" => $country_settings->recognizer));
	$tropo->on(array("event" => "continue", "next" => "tropo-pusher.php?uri=color&country=" . $country));
	$tropo->on(array("event" => "error", "next" => "tropo-pusher.php?uri=error&country=" . $country));
	$tropo->on(array("event" => "incomplete", "next" => "tropo-pusher.php?uri=error&country=" . $country));
	$tropo->renderJSON();

}

/**
 * Handler and method for accepting caller input and pushing to borwser.
 */
dispatch_post('/color', 'changeColor');
function changeColor() {
	
	$country = $_REQUEST['country'];
	$country_settings = getCountrySettings($country);
	
	// Get the value of the caller utterance from Tropo Result object.
	$result = new Result();
	$value = $result->getValue();

	$tropo = new Tropo();

	// If user said stop, disconnect.
	if($value == "stop" || $value == "parada") {
		$tropo->hangup();
	}

	// Otherwise, send color selection to browser.
	else {
		$payload = json_encode(array("type" => "color", "color" => $value));

		// Create a new instance of Pusher object.
		$pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, true);

		// Push color selection to browser instance for display.
		$pusher->trigger(PUSHER_CHANNEL, PUSHER_EVENT, $payload);

		// Redirect caller back to color selection prompt.
		$tropo->on(array("event" => "continue", "next" => "tropo-pusher.php?uri=prompt&country=" . $country));
	}

	$tropo->renderJSON();

}

/**
 * Error hander and method.
 */
dispatch_post('/error', 'handleError');
function handleError() {
	
	$country = $_REQUEST['country'];
	$country_settings = getCountrySettings($country);

	// Render error prompt and disconnect.
	$tropo = new Tropo();
	$tropo->say($country_settings->error_prompt, array("voice" => $country_settings->voice));
	$tropo->hangup();
	$tropo->renderJSON();

}

run();

?>