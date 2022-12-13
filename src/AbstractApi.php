<?php

namespace Upanupstudios\Upaknee\Php\Client;

abstract class AbstractApi
{
  /**
   * @var Upaknee
   */
  protected $client;

  public function __construct(Upaknee $client)
  {
      $this->client = $client;
  }
}