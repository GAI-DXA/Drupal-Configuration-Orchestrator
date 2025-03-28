<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator;

/**
 * Abstract base class for configuration providers.
 *
 * Provides common functionality for configuration providers, including:
 * - Environment management
 * - Configuration naming
 * - Description handling
 * - Standard configuration group creation.
 */
abstract class AbstractConfigurationProvider implements ConfigurationProviderInterface {

  /**
   * List of applicable environments.
   *
   * @var array
   */
  protected array $environments;

  /**
   * Configuration name.
   *
   * @var string
   */
  protected string $configName;

  /**
   * Configuration description.
   *
   * @var string
   */
  protected string $description;

  /**
   * Constructor.
   *
   * @param array $environments
   *   List of applicable environments.
   * @param string $configName
   *   Configuration name.
   * @param string $description
   *   Configuration description.
   */
  public function __construct(array $environments, string $configName, string $description) {
    $this->environments = $environments;
    $this->configName = $configName;
    $this->description = $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableEnvironments(): array {
    return $this->environments;
  }

  /**
   * Creates a configuration group with standard structure.
   *
   * @param array $settings
   *   Configuration settings.
   *
   * @return array
   *   Structured configuration group.
   */
  protected function createConfigGroup(array $settings): array {
    return [
      'name' => $this->configName,
      'settings' => $settings,
      'description' => $this->description,
    ];
  }

}
