<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Example\Provider;

use WeAreGenuine\DrupalConfigOrchestrator\AbstractConfigurationProvider;

/**
 * Example configuration provider for database settings.
 */
class DatabaseConfig extends AbstractConfigurationProvider {

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct(
      ['dev', 'test', 'prod'],
      'databases',
      'Database configuration settings'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationOverrides(string $environment): array {
    return [
      'databases' => [
        'default' => [
          'default' => [
            'database' => $this->getDatabaseName($environment),
            'username' => $this->getDatabaseUser($environment),
            'password' => $this->getDatabasePassword($environment),
            'host' => $this->getDatabaseHost($environment),
            'port' => '3306',
            'driver' => 'mysql',
            'prefix' => '',
          ],
        ],
      ],
    ];
  }

  /**
   * Gets the database name for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The database name.
   */
  private function getDatabaseName(string $environment): string {
    $prefix = 'mysite';

    switch ($environment) {
      case 'dev':
        return $prefix . '_dev';

      case 'test':
        return $prefix . '_test';

      default:
        return $prefix . '_prod';
    }
  }

  /**
   * Gets the database user for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The database user.
   */
  private function getDatabaseUser(string $environment): string {
    switch ($environment) {
      case 'dev':
        return 'dev_user';

      case 'test':
        return 'test_user';

      default:
        return 'prod_user';
    }
  }

  /**
   * Gets the database password for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The database password.
   */
  private function getDatabasePassword(string $environment): string {
    // In a real implementation, these are fetched from environment variables
    // or a secure secret management system.
    return 'REPLACE_WITH_SECURE_PASSWORD';
  }

  /**
   * Gets the database host for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The database host.
   */
  private function getDatabaseHost(string $environment): string {
    switch ($environment) {
      case 'dev':
        return 'localhost';

      case 'test':
        return 'test-db.example.com';

      default:
        return 'prod-db.example.com';
    }
  }

}
