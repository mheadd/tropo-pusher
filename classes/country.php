<?php

/**
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

?>