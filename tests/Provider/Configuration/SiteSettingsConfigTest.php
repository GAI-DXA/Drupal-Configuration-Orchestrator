<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Tests\Provider\Configuration;

use PHPUnit\Framework\TestCase;
use WeAreGenuine\DrupalConfigOrchestrator\Provider\Configuration\SiteSettingsConfig;

class SiteSettingsConfigTest extends TestCase
{
    private SiteSettingsConfig $configProvider;

    protected function setUp(): void
    {
        $this->configProvider = new SiteSettingsConfig();
    }

    /**
     * @dataProvider environmentSiteNameProvider
     */
    public function testSiteNameForEnvironment(string $environment, string $expectedSiteName): void
    {
        $config = $this->configProvider->getConfigurationOverrides($environment);
        $this->assertEquals($expectedSiteName, $config['system.site']['name']);
    }

    /**
     * @dataProvider environmentEmailProvider
     */
    public function testAdminEmailForEnvironment(string $environment, string $expectedEmail): void
    {
        $config = $this->configProvider->getConfigurationOverrides($environment);
        $this->assertEquals($expectedEmail, $config['system.site']['mail']);
    }

    /**
     * @dataProvider environmentPerformanceSettingsProvider
     */
    public function testPerformanceSettingsForEnvironment(string $environment, bool $expectedOptimization): void
    {
        $config = $this->configProvider->getConfigurationOverrides($environment);
        $this->assertEquals($expectedOptimization, $config['system.performance']['css']['preprocess']);
        $this->assertEquals($expectedOptimization, $config['system.performance']['css']['gzip']);
        $this->assertEquals($expectedOptimization, $config['system.performance']['js']['preprocess']);
        $this->assertEquals($expectedOptimization, $config['system.performance']['js']['gzip']);
    }

    public function testCacheSettingsForEnvironment(): void
    {
        $prodConfig = $this->configProvider->getConfigurationOverrides('prod');
        $devConfig = $this->configProvider->getConfigurationOverrides('dev');

        $this->assertEquals(3600, $prodConfig['system.performance']['cache']['page']['max_age']);
        $this->assertEquals(0, $devConfig['system.performance']['cache']['page']['max_age']);
    }

    public function testSupportedEnvironments(): void
    {
        $reflection = new \ReflectionClass($this->configProvider);
        $constructor = $reflection->getConstructor();
        $params = $constructor->getParameters();

        // Get the parent constructor call
        $method = $reflection->getMethod('__construct');
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        // This test verifies that the provider is configured for all expected environments
        $expectedEnvironments = ['dev', 'test', 'prod'];
        $this->assertEquals($expectedEnvironments, $this->configProvider->getApplicableEnvironments());
    }

    public function environmentSiteNameProvider(): array
    {
        return [
            'development' => ['dev', 'My Site (Development)'],
            'staging' => ['test', 'My Site (Staging)'],
            'production' => ['prod', 'My Site'],
        ];
    }

    public function environmentEmailProvider(): array
    {
        return [
            'development' => ['dev', 'dev-admin@example.com'],
            'staging' => ['test', 'test-admin@example.com'],
            'production' => ['prod', 'admin@example.com'],
        ];
    }

    public function environmentPerformanceSettingsProvider(): array
    {
        return [
            'development' => ['dev', false],
            'staging' => ['test', false],
            'production' => ['prod', true],
        ];
    }
}
