# Drupal Configuration Orchestrator

A flexible tool for managing environment-specific configuration overrides in Drupal 10+ websites. This package allows you to define configuration providers that can apply different settings based on the current environment, making it easy to manage configuration across development, staging, and production environments.

> **Note**: This is a helper package, not a Drupal module. It works alongside your Drupal installation to manage environment-specific configurations.

## Requirements

- PHP 8.1 or higher
- Drupal 10.0 or higher
- Composer 2.x

## Overview

The configuration system consists of three main components:

1. **Configuration Manager** (`src/ConfigurationManager.php`)
   - Core functionality for managing configuration overrides
   - Handles provider registration and configuration application
   - Manages configuration export and import

1. **Configuration Providers** (`src/Provider/Configuration/`)
   - Individual configuration providers for different features
   - Each provider can specify which environments it applies to
   - Supports environment-specific configuration values

1. **Utility Classes** (`src/Utility/`)
   - Helper classes for common operations
   - Environment detection and management
   - Shared functionality

## Implementation in Your Drupal Project

### 1. Installation

Add the package to your Drupal project:

```bash
composer require wearegenuine/drupal-config-orchestrator
```

### 2. Project Structure Setup

1. Create a directory for your configuration providers:

   ```bash
   mkdir -p config/providers
   ```

2. Configure your project's composer.json to autoload your providers:

   ```json
   {
     "autoload": {
       "psr-4": {
         "YourNamespace\\Config\\Provider\\Configuration\\": "config/providers/"
       }
     }
   }
   ```

3. Run composer dump-autoload:

   ```bash
   composer dump-autoload
   ```

### 3. Drupal Integration

1. Add the following to your `settings.php` or environment-specific settings file (e.g., `settings.local.php`):

   ```php
   use WeAreGenuine\DrupalEnvConfig\ConfigurationManager;
   use YourNamespace\Config\ConfigurationService;

   // Initialize the configuration manager
   $configManager = new ConfigurationManager();

   // Register your providers
   ConfigurationService::registerProviders($configManager);

   // Apply configuration overrides
   $configManager->applyConfiguration();
   ```

2. Ensure your `settings.php` has the environment setting:

   ```php
   $settings['environment'] = getenv('DRUPAL_ENV') ?: 'dev';
   ```

### 4. Configuration Script Setup

Add the configuration script to your deployment process:

```bash
# For local development
./vendor/bin/drupal-config-orchestrator

# For CI/CD pipelines
./vendor/bin/drupal-config-orchestrator --env=prod
```

## Usage

## Creating Your Configuration Providers

### Basic Provider Implementation

Create configuration providers in your `config/providers` directory. Each provider should handle a specific configuration concern (e.g., database, cache, mail settings).

   ```php
   namespace YourNamespace\Config\Provider;

   use WeAreGenuine\DrupalEnvConfig\AbstractConfigurationProvider;

   class DatabaseConfig extends AbstractConfigurationProvider {
       public function __construct() {
           parent::__construct(
               ['dev', 'test', 'prod'],  // Supported environments
               'databases',               // Config key
               'Database configuration'   // Description
           );
       }

       public function getConfigurationOverrides(string $environment): array {
           return [
               'default' => [
                   'default' => [
                       'host' => $environment === 'prod'
                           ? 'prod-db.example.com'
                           : 'localhost',
                   ],
               ],
           ];
       }
   }
   ```

1. Register your providers:

   ```php
   // In config/providers/ConfigurationService.php
   namespace YourNamespace\Config;

   use WeAreGenuine\DrupalEnvConfig\ConfigurationManager;
   use YourNamespace\Config\Provider\DatabaseConfig;

   class ConfigurationService {
       public static function registerProviders(ConfigurationManager $manager): void {
           $manager->addProvider(new DatabaseConfig());
           // Add more providers here
       }
   }
   ```

## Environment Management

### Environment Detection

The package determines the current environment in the following order of precedence:

1. Command line argument: `--env=prod`
1. Environment variable: `DRUPAL_ENV`
1. Drupal's `SETTINGS` array: `$settings['environment']`

### Environment-Specific Configuration

1. Define environment-specific values in your providers:

   ```php
   public function getConfigurationOverrides(string $environment): array
   {
       return match ($environment) {
           'prod' => ['key' => 'production-value'],
           'test' => ['key' => 'staging-value'],
           default => ['key' => 'development-value'],
       };
   }
   ```

1. Use environment variables for sensitive data:

   ```php
   public function getDatabasePassword(string $environment): string
   {
       return match ($environment) {
           'prod' => getenv('DRUPAL_DB_PASSWORD'),
           default => 'local-password',
       };
   }
   ```

### CI/CD Integration

Add the configuration script to your deployment process:

1. Acquia Cloud:

   ```bash
   # In hooks/post-code-deploy/apply-config.sh
   #!/bin/bash
   cd /var/www/html/${site}.${target_env}/docroot
   ../vendor/bin/drupal-config-orchestrator --env=${target_env}
   ```

1. Platform.sh:

   ```yaml
   # In .platform.app.yaml
   hooks:
     deploy: |
       ./vendor/bin/drupal-config-orchestrator --env=$PLATFORM_BRANCH
   ```

### Available Commands

```bash
# Apply configuration for current environment
./vendor/bin/drupal-config-orchestrator

# Apply configuration for specific environment
./vendor/bin/drupal-config-orchestrator --env=prod

# List all registered providers
./vendor/bin/drupal-config-orchestrator --list-providers

# Show configuration changes without applying
./vendor/bin/drupal-config-orchestrator --dry-run
```

   ```bash
   mkdir -p hooks/post-code-deploy
   cp vendor/wearegenuine/drupal-config-orchestrator/examples/hooks/post-code-deploy.sh \
      hooks/post-code-deploy/
   chmod +x hooks/post-code-deploy/post-code-deploy.sh
   ```

1. The hook will automatically:
   - Detect the environment (dev, test, prod)
   - Run configuration updates
   - Report success/failure

1. Test the hook locally:

   ```bash
   ./hooks/post-code-deploy/post-code-deploy.sh \
       sitename dev master master.dev repo-url git
   ```

### Example Configuration Provider

Here's an example configuration provider that manages site settings:

```php
namespace YourNamespace\Config\Provider;

use WeAreGenuine\DrupalEnvConfig\AbstractConfigurationProvider;

class SiteSettingsConfig extends AbstractConfigurationProvider {
    public function __construct() {
        parent::__construct(
            ['dev', 'test', 'prod'],
            'system.site',
            'Site configuration settings'
        );
    }

    public function getConfigurationOverrides(string $environment): array {
        $settings = [
            'name' => 'My Site',
            'mail' => 'admin@example.com',
        ];

        if ($environment === 'dev') {
            $settings['name'] .= ' (Development)';
        }

        return $this->createConfigGroup($settings);
    }
}
```

## Advanced Usage

### Environment Variables

The package uses the following environment variables:

- `AH_SITE_ENVIRONMENT`: Current environment (dev, test, prod)
- `DRUPAL_ROOT`: Path to Drupal root directory (optional)

### Custom Environment Detection

You can implement your own environment detection by extending the `EnvironmentUtility` class:

```php
namespace YourNamespace\Config\Utility;

use WeAreGenuine\DrupalConfigOrchestrator\Utility\EnvironmentUtility;

class CustomEnvironmentUtility extends EnvironmentUtility {
    public static function detectEnvironment(): string {
        // Your custom environment detection logic
        return 'dev';
    }
}
```

## Directory Structure

```text
├── src/                          # Package source code
│   ├── ConfigurationManager.php  # Main configuration manager
│   ├── Provider/                 # Base provider classes
│   └── Utility/                  # Utility classes
├── examples/                     # Example implementations
│   ├── Provider/                 # Example providers
│   │   └── SiteSettingsConfig.php
│   └── hooks/                    # Cloud platform hooks
│       └── post-code-deploy.sh
└── README.md                     # Documentation

## Environment Support

The configuration system supports any environment name, but commonly used ones include:
- `dev`: Development environment
- `test`: Testing/staging environment
- `prod`: Production environment

## Error Handling

- Exit code 0: All configurations applied successfully
- Exit code 1: One or more configuration updates failed
- Detailed error messages are logged using PHP's error_log function
- Failed configurations are reported in the execution summary

## Dependencies

- PHP 7.4 or higher
- Drupal 9.x or 10.x
- Drush command-line tool
- Composer for dependency management

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request
