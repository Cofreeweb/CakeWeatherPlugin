<?php
/**
 * Utiliza el api de worldweatheronline.com para mostrar el tiempo de las localidades solicitadas
 *
 * Es necesario setear en Config/weather.php $config ['Weather']['apiKey'] = 'el_api';
 * 
 * El uso básico es solicitar una ciudad con
 * $this->Weather->setCity( 'Paris')
 *
 * Y después hacer uso de los métodos públicos para recibir la información de cada key
 * 
 * @link http://developer.worldweatheronline.com/io-docs
 * @package weather.view.helper
 * @author Alfonso Etxeberria
 */

App::uses('HttpSocket', 'Network/Http');

class WeatherHelper extends AppHelper 
{
  public $helpers = array('Html', 'Form');
  
/**
 * La URL del servidor de API
 *
 * @access private
 */
  private $__apiURL = 'http://api.worldweatheronline.com/free/v1/weather.ashx';

/**
 * El api key, que es seteado en el constructor tomando el valor de Configure::read( 'Weather.apiKey')
 *
 * @access private
 */
  private $__apiKey = null;
    
/**
 * La ciudad de la petición, seteada desde la vista con $this->Weather->setCity( 'Ciudad')
 *
 * @access private
 */
  private $__city = null;
  
/**
 * La instancia de HttpSocket
 *
 * @access private
 */
  private $Http = null;
  
/**
 * La petición actual, usada desde los distintos métodos para tomar la información
 *
 * @access private
 */
  private $__current = null;
  

  public function __construct( View $View, $settings = array()) 
  {
    parent::__construct( $View, $settings);
    
    if( !$key = Configure::read( 'Weather.apiKey'))
    {
      throw new CakeException( __d( 'weather', "Value for Configure::read( 'Weather.apiKey') not found"));
    }
    
    $this->__apiKey = $key;    
    
    $this->Http = new HttpSocket();
	}
	
/**
 * Setea la ciudad actual y realiza una petición al API
 *
 * @param string $city 
 * @return void
 */
	public function setCity( $city)
	{
    $this->__city = $city;
    $this->__request();
	}
  
/**
 * Realiza una petición al API y guarda la información en $this->__current
 *
 * @return void
 */
  private function __request()
  {
    $response = $this->Http->get( $this->__apiURL, array(
        'key' => $this->__apiKey,
        'q' => $this->__city,
        'format' => 'json',
        'num_of_days' => 5
    ));
    
    $data = json_decode( $response, true);
    $this->__current = $data ['data'];
  }
  
/**
 * Retorna un valor determinado de la petición actual seteada en WeatherHelper::setCity()
 * El valor de $key es la clave del valor solicitado
 * $day puede ser o 'current' (para la información actual) o 0, 1, 2, 3... para los días, siendo 0 hoy y 1 mañana
 *
 * @param string $key 
 * @param string $day 
 * @return string
 */
  public function getValue( $key, $day = 'current')
  {
    if( $day === 'current')
    {
      $data = $this->__current ['current_condition'];
      $day = 0;
    }
    else
    {
      $data = $this->__current ['weather'];
    }
    return $data [$day][$key];
  }
  
  
/**
 * Devuelve el nombre de la class CSS para la situación del cielo
 *
 * @param string $day 
 * @return string
 */
  public function sky( $day)
  {
    $value = $this->getValue( 'weatherDesc', $day);
    return 'weather-' . strtolower( $value [0]['value']);
  }
  
/**
 * Devuelve la temperatura mínima
 *
 * @param string $day 
 * @return void
 */
  public function tempMin( $day)
  {
    $value = $this->getValue( 'tempMinC', $day);
    return $value ."º";
  }
 
/**
 * Devuelve la temperatura máxima
 *
 * @param string $day 
 * @return void
 */
  public function tempMax( $day)
  {
    $value = $this->getValue( 'tempMaxC', $day);
    return $value ."º";
  }
  
/**
 * Devuelve el día de la semana
 *
 * @param string $day 
 * @return void
 */
  public function day( $day)
  {
    $value = $this->getValue( 'date', $day);
    return strftime( "%A", strtotime( $value));
  }
}