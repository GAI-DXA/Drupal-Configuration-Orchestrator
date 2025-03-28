<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Configuration manager class.
 *
 * Central class responsible for managing and applying configuration overrides.
 * This class:
 * - Manages a collection of configuration providers
 * - Applies configurations for specific environments
 * - Tracks success/failure statistics
 * - Provides detailed logging and output.
 */
class ConfigurationManager {

  /**
   * List of configuration providers.
   *
   * @var \WeAreGenuine\DrupalConfigOrchestrator\ConfigurationProviderInterface[]
   */
  private array $providers = [];

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Adds a configuration provider.
   *
   * @param \WeAreGenuine\DrupalConfigOrchestrator\ConfigurationProviderInterface $provider
   *   Configuration provider to add.
   */
  public function addProvider(ConfigurationProviderInterface $provider): void {
    $this->providers[] = $provider;
  }

  /**
   * Gets all registered providers.
   *
   * @return \WeAreGenuine\DrupalConfigOrchestrator\ConfigurationProviderInterface[]
   *   List of registered providers.
   */
  public function getProviders(): array {
    return $this->providers;
  }

  /**
   * Applies configurations for a specific environment.
   *
   * @param string $environment
   *   Target environment.
   * @param bool $dryRun
   *   Whether to perform a dry run.
   *
   * @return array
   *   Array containing success and total counts.
   */
  public function applyConfigurations(string $environment, bool $dryRun = FALSE): array {
    $total_success = 0;
    $total_updates = 0;
    $results = [];

    foreach ($this->providers as $provider) {
      if (!in_array($environment, $provider->getApplicableEnvironments(), TRUE)) {
        continue;
      }

      $config_group = $provider->getConfigurationOverrides($environment);
      $result = $this->updateConfigurationGroup($config_group, $dryRun);
      $total_success += $result['success'];
      $total_updates += $result['total'];
      $results[] = $result;
    }

    return [
      'success' => $total_success,
      'total' => $total_updates,
      'details' => $results,
    ];
  }

  /**
   * Updates a configuration group.
   *
   * @param array $config_group
   *   Configuration group to update.
   * @param bool $dryRun
   *   Whether to perform a dry run.
   *
   * @return array
   *   Array containing success and total counts.
   */
  private function updateConfigurationGroup(array $config_group, bool $dryRun = FALSE): array {
    $success_count = 0;
    $total_count = 0;
    $details = [];

    foreach ($config_group as $config_name => $settings) {
      $config = $this->configFactory->getEditable($config_name);
      if (!$config) {
        $details[] = [
          'message' => "Could not load configuration: {$config_name}",
          'success' => FALSE,
        ];
        continue;
      }

      $total_count++;
      if ($dryRun) {
        $success_count++;
        $details[] = [
          'message' => "Would update configuration: {$config_name}",
          'success' => TRUE,
          'changes' => $settings,
        ];
        continue;
      }

      try {
        foreach ($settings as $key => $value) {
          $config->set($key, $value);
        }
        $config->save();
        $success_count++;
        $details[] = [
          'message' => "Updated configuration: {$config_name}",
          'success' => TRUE,
        ];
      }
      catch (\Exception $e) {
        $details[] = [
          'message' => "Failed to update configuration {$config_name}: {$e->getMessage()}",
          'success' => FALSE,
        ];
      }
    }
    return [
      'success' => $success_count,
      'total' => $total_count,
      'details' => $details,
    ];
  }

}
