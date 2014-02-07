<?php

Configure::load( 'weather');

Cache::config( 'weather', array(
	'engine' => 'File',
	'prefix' => 'weather_',
	'path' => CACHE . 'persistent' . DS,
	'serialize' => true,
	'duration' => '+1 hours',
));