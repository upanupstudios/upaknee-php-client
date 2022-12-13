<?php

namespace Upanupstudios\Upaknee\Php\Client;

class Subscribers extends AbstractApi
{
  /**
   * Add a new subscriber (simple).
   */
  public function create(array $data)
  {
    $options['body'] = $this->client->prepareData('subscriber', $data);

    $response = $this->client->request('POST', 'subscribers', $options);

    return $response;
  }
}