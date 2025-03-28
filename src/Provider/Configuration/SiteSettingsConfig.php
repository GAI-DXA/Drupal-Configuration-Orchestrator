<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Provider\Configuration;

use WeAreGenuine\DrupalConfigOrchestrator\AbstractConfigurationProvider;

/**
 * Example configuration provider for site settings.
 */
class SiteSettingsConfig extends AbstractConfigurationProvider {

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct(
      ['dev', 'test', 'prod'],
      'system.site',
      'Site configuration settings'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationOverrides(string $environment): array {
    return [
      'system.site' => [
        'name' => $this->getSiteName($environment),
        'slogan' => 'A Drupal Website',
        'mail' => $this->getAdminEmail($environment),
      ],
      'system.performance' => [
        'css' => [
          'preprocess' => $environment === 'prod',
          'gzip' => $environment === 'prod',
        ],
        'js' => [
          'preprocess' => $environment === 'prod',
          'gzip' => $environment === 'prod',
        ],
        'cache' => [
          'page' => [
            'max_age' => $environment === 'prod' ? 3600 : 0,
          ],
        ],
      ],
    ];
  }

  /**
   * Gets the site name for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The site name.
   */
  private function getSiteName(string $environment): string {
    $name = 'My Site';

    switch ($environment) {
      case 'dev':
        return $name . ' (Development)';

      case 'test':
        return $name . ' (Staging)';

      default:
        return $name;
    }
  }

  /**
   * Gets the admin email for the given environment.
   *
   * @param string $environment
   *   The target environment.
   *
   * @return string
   *   The admin email.
   */
  private function getAdminEmail(string $environment): string {
    switch ($environment) {
      case 'dev':
        return 'dev-admin@example.com';

      case 'test':
        return 'test-admin@example.com';

      default:
        return 'admin@example.com';
    }
  }

}
