<?php

namespace Upanupstudios\Upaknee\Php\Client;

final class Config
{
  private $apiToken;
  private $apiPassword;

  public function __construct(string $apiToken, string $apiPassword)
  {
    $this->apiToken = $apiToken;
    $this->apiPassword = $apiPassword;
  }

  /**
   * Get API token.
   */
  public function getApiToken(): string
  {
    return $this->apiToken;
  }

  /**
   * Get API password.
   */
  public function getApiPassword(): string
  {
    return $this->apiPassword;
  }
}