<?php

/**
 * Weather behavior class
 * 
 * 
 *
 * 
 * @package Weather.Model.Behavior
 */
 
App::uses('HttpSocket', 'Network/Http');
 
class WeatherBehavior extends ModelBehavior
{
  private $__apiURL = 'http://api.worldweatheronline.com/free/v1/weather.ashx';
  
  public function checkCity( Model $Model, $value) 
  {
    $value = current( $value);
    
    if( empty( $value))
    {
      return true;
    }
        
    return $this->__check( $value);
  }
  
  private function __check( $city)
  {
    if( !$key = Configure::read( 'Weather.apiKey'))
    {
      throw new CakeException( __d( 'weather', "Value for Configure::read( 'Weather.apiKey') not found"));
    }
    
    $Http = new HttpSocket();
    
    $data = array(
        'key' => $key,
        'q' => $city,
        'format' => 'json',
        'num_of_days' => 5
    );
    
    $key = md5( serialize( $data));

    $response = $Http->get( $this->__apiURL, $data);
    $result = json_decode( $response, true);
    
    return !isset( $result ['data']['error']);
  }
}

?>