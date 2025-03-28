<?php

namespace WeAreGenuine\DrupalConfigOrchestrator\Configuration;

use Composer\Json\JsonFile;

/**
 * Manages configuration settings for the Drupal Configuration Orchestrator.
 *
 * This class handles loading and providing access to configuration settings
 * from various sources (composer.json, environment variables) with a defined
 * precedence order.
 */
class Settings {
  /**
   * The namespace for configuration providers.
   *
   * @var string
   */
  private string $providerNamespace;

  /**
   * The directory path for configuration providers.
   *
   * @var string
   */
  private string $providerPath;

  public function __construct() {
    $this->loadSettings();
  }

  /**
   * Loads settings from various sources with defined precedence.
   *
   * Priority order:
   * 1. Environment variables.
   * 2. composer.json configuration.
   * 3. Default values.
   */
  private function loadSettings(): void {
    // Default values.
    $this->providerNamespace = 'YourNamespace\\Config\\Provider\\Configuration';
    $this->providerPath = 'config/providers';

    // Try to load from composer.json.
    $composerFile = $this->findComposerJson();
    if ($composerFile) {
      $jsonFile = new JsonFile($composerFile);
      $config = $jsonFile->read();

      if (isset($config['extra']['drupal-config-orchestrator'])) {
        $settings = $config['extra']['drupal-config-orchestrator'];
        $this->providerNamespace = $settings['provider-namespace'] ?? $this->providerNamespace;
        $this->providerPath = $settings['provider-path'] ?? $this->providerPath;
      }
    }

    // Environment variables can override composer.json settings.
    $this->providerNamespace = getenv('DRUPAL_CONFIG_PROVIDER_NAMESPACE') ?: $this->providerNamespace;
    $this->providerPath = getenv('DRUPAL_CONFIG_PROVIDER_PATH') ?: $this->providerPath;
  }

  /**
   * Recursively searches for composer.json starting from current directory.
   *
   * @return string|null
   *   The full path to composer.json if found, NULL otherwise.
   */
  private function findComposerJson(): ?string {
    $dir = getcwd();
    while ($dir !== '/') {
      $composerFile = $dir . '/composer.json';
      if (file_exists($composerFile)) {
        return $composerFile;
      }
      $dir = dirname($dir);
    }
    return NULL;
  }

  /**
   * Gets the configured provider namespace.
   *
   * @return string
   *   The fully qualified namespace for configuration providers.
   */
  public function getProviderNamespace(): string {
    return $this->providerNamespace;
  }

  /**
   * Gets the configured provider directory path.
   *
   * @return string
   *   The directory path where configuration providers are located.
   */
  public function getProviderPath(): string {
    return $this->providerPath;
  }

}
