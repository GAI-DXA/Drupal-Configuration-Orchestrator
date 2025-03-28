<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator;

/**
 * Interface for configuration providers.
 *
 * Interface defines the contract that all configuration providers must follow.
 * Configuration providers are responsible for supplying environment-specific
 * configuration overrides and specifying which environments they apply to.
 */
interface ConfigurationProviderInterface {

  /**
   * Get the configuration overrides for a specific environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return array
   *   Array of configuration overrides.
   */
  public function getConfigurationOverrides(string $environment): array;

  /**
   * Get the environments this configuration applies to.
   *
   * @return array
   *   Array of environment names.
   */
  public function getApplicableEnvironments(): array;

}
