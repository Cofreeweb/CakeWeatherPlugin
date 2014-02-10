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
 * @link http://www.worldweatheronline.com/feed/wwoConditionCodes.txt
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
  
  
/**
 * El nombre de la familia de iconos situados en Plugin/Weather/webroot/img/icons
 *
 * @access private
 */
  private $__iconFamily = 'black';
  
  

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
 * Setea la familia de iconos
 * Los ficheros se encuentran en Plugin/Weather/webroot/img/icons
 *
 * @param string $family 
 * @return void
 */
	public function setIconFamily( $family)
	{
	  $this->$__iconFamily = $family;
	}
  
/**
 * Realiza una petición al API y guarda la información en $this->__current
 *
 * @return void
 */
  private function __request()
  {
    $data = array(
        'key' => $this->__apiKey,
        'q' => $this->__city,
        'format' => 'json',
        'num_of_days' => 5
    );
    
    $key = md5( serialize( $data));
    $result = Cache::read( $key, 'weather');

    if( !$result)
    {
      $response = $this->Http->get( $this->__apiURL, $data);
      $result = json_decode( $response, true);
      Cache::write( $key, $result, 'weather');
    }
    
    $this->__current = $result ['data'];
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
 * @param integer $day El número de día (0 es hoy) 
 * @param array $attributes Atributos para la etiqueta <img> 
 * @param string $size El tamaño del icono (64, 128, 256, 512)
 * @return void
 */
  public function icon( $day, $attributes = array(), $size = '64')
  {
    $value = $this->getValue( 'weatherIconUrl', $day);
    if( !empty( $value [0]['value']))
    {
      $value = $value [0]['value'];
      $file = substr( $value, strrpos( $value, '/') + 1);
      $sky = $this->getValue( 'weatherDesc', $day);
      $attributes ['alt'] = $sky [0]['value'];
      return $this->Html->image( '/Weather/img/icons/'. $this->__iconFamily . '/'. $size .'/'. $file, $attributes);
    }
    
    return '';
  }
  
/**
 * Devuelve la temperatura actual (solo para la condición metereológica actual)
 *
 * @param string $day 
 * @return void
 */
  public function temp( $day)
  {
    $value = $this->getValue( 'temp_C', $day);
    return $value ."º";
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
    return utf8_encode( strftime( "%A", strtotime( $value)));
  }
}