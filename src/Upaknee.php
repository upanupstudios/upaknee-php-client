<?php

namespace Upanupstudios\Upaknee\Php\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class Upaknee
{
  /**
   * The REST API URL.
   *
   * @var string $api_url
   */
  private $api_url = 'https://rest.upaknee.com';

  private $config;
  private $httpClient;

  public function __construct(Config $config, ClientInterface $httpClient)
  {
    $this->config = $config;
    $this->httpClient = $httpClient;
  }

  public function getApiUrl()
  {
    return $this->api_url;
  }

  public function getConfig(): Config
  {
    return $this->config;
  }

  public function request(string $method, string $uri, array $options = [])
  {
    try {
      $apiToken = $this->config->getApiToken();
      $apiPassword = $this->config->getApiPassword();

      $credentials = base64_encode($apiToken.':'.$apiPassword);

      $defaultOptions = [
        'headers' => [
          'Accept' => 'application/xml',
          'Content-Type' => 'application/xml',
          'Authorization' => 'Bearer '.$credentials
        ]
      ];

      if(!empty($options)) {
        //TODO: This might not be a deep merge...
        $options = array_merge($defaultOptions, $options);
      } else {
        $options = $defaultOptions;
      }

      $request = $this->httpClient->request($method, $this->api_url.'/'.$uri, $options);

      // Return as array
      $response = $this->prepareResponse($request);

    } catch (\JsonException $exeption) {
      $response = $exeption->getMessage();
    } catch (RequestException $exception) {
      $response = $exception->getMessage();
    }

    return $response;
  }

  public function version()
  {
    $response = $this->request('GET', 'version');

    return $response;
  }

  protected function prepareResponse($request) {
    $body = $request->getBody();
    $response = $body->__toString();

    $xml = new \SimpleXMLElement($response);
    $json = json_encode($xml);
    $response = json_decode($json, TRUE);

    return $response;
  }

  public function prepareData($uri, $data) {
    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><' . $uri . '></' . $uri . '>');
    $this->_array_to_xml($data, $xml);
    return $xml->asXML();
  }

  /**
   * Convert array into XML format.
   */
  private function _array_to_xml($data, &$xml) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        if (is_numeric($key)) {
          $this->_array_to_xml($value, $xml);
        } else {
          $subnode = $xml->addChild($key);

          if (array_keys($value) === range(0, count($value) - 1)) {
            $subnode->addAttribute('type', 'array');
          }

          $this->_array_to_xml($value, $subnode);
        }
      } else {
        if (in_array($key, array('body'))) {
          $subnode = $xml->addChild($key, NULL);
          $dom = dom_import_simplexml($subnode);
          $dom->appendChild($dom->ownerDocument->createCDATASection($value));
        } else {
          $subnode = $xml->addChild($key, htmlspecialchars($value));

          if (is_bool($value)) {
            $subnode->addAttribute('type', 'boolean');
          }
        }
      }
    }
  }

  /**
   * @return object
   *
   * @throws \InvalidArgumentException
   *  If $class does not exist.
   */
  public function api(string $class)
  {
    switch ($class) {
      case 'subscribers':
      $api = new Subscribers($this);
      break;

      default:
      throw new \InvalidArgumentException("Undefined api instance called: '$class'.");
    }

    return $api;
  }

  public function __call(string $name, array $args): object
  {
    try {
      return $this->api($name);
    } catch (\InvalidArgumentException $e) {
      throw new \BadMethodCallException("Undefined method called: '$name'.");
    }
  }
}