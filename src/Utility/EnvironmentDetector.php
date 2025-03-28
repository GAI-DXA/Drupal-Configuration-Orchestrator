<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Utility;

/**
 * Utility class for detecting the current environment.
 */
class EnvironmentDetector {

  /**
   * List of valid environments.
   *
   * @var array
   */
  private array $validEnvironments = ['dev', 'test', 'prod'];

  /**
   * Detects the current environment.
   *
   * Uses multiple sources in order of priority:
   * 1. DRUPAL_ENV environment variable
   * 2. Drupal settings array.
   *
   * @return string|null
   *   Detected environment or null if none detected.
   */
  public function detect(): ?string {
    $environment = $this->getFromEnvironmentVariable();
    if ($environment) {
      return $environment;
    }

    $environment = $this->getFromDrupalSettings();
    if ($environment) {
      return $environment;
    }

    return NULL;
  }

  /**
   * Gets environment from environment variable.
   *
   * @return string|null
   *   Environment from variable or null if not set.
   */
  private function getFromEnvironmentVariable(): ?string {
    $environment = getenv('DRUPAL_ENV');
    if ($environment && in_array($environment, $this->validEnvironments, TRUE)) {
      return $environment;
    }

    return NULL;
  }

  /**
   * Gets environment from Drupal settings.
   *
   * @return string|null
   *   Environment from settings or null if not set.
   */
  private function getFromDrupalSettings(): ?string {
    global $_drupal_config_orchestrator_settings;

    if (isset($_drupal_config_orchestrator_settings['environment']) &&
        in_array($_drupal_config_orchestrator_settings['environment'], $this->validEnvironments, TRUE)) {
      return $_drupal_config_orchestrator_settings['environment'];
    }

    return NULL;
  }

  /**
   * Gets list of valid environments.
   *
   * @return array
   *   List of valid environments.
   */
  public function getValidEnvironments(): array {
    return $this->validEnvironments;
  }

}
