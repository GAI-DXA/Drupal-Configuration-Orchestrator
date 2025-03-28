<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Tests\Console\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WeAreGenuine\DrupalConfigOrchestrator\Console\Command\ApplyConfigCommand;
use WeAreGenuine\DrupalConfigOrchestrator\ConfigurationManager;
use WeAreGenuine\DrupalConfigOrchestrator\Utility\EnvironmentDetector;
use Drupal\Core\Config\ConfigFactory;

class ApplyConfigCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private ConfigurationManager $configManager;
    private EnvironmentDetector $environmentDetector;
    private ConfigFactory $configFactory;

    protected function setUp(): void
    {
        // Mock Drupal's config factory
        $this->configFactory = $this->createMock(ConfigFactory::class);

        // Create real instances of our services
        $this->configManager = new ConfigurationManager($this->configFactory);
        $this->environmentDetector = new EnvironmentDetector();

        // Create the command
        $command = new ApplyConfigCommand($this->configManager, $this->environmentDetector);

        // Create the application and add our command
        $application = new Application();
        $application->add($command);

        // Create a command tester
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSpecificEnvironment(): void
    {
        // Test execution with --env option
        $this->commandTester->execute([
            '--env' => 'test'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Applying configuration for environment: test', $output);
    }

    public function testExecuteWithDryRun(): void
    {
        // Test execution with --dry-run option
        $this->commandTester->execute([
            '--dry-run' => true,
            '--env' => 'dev'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dry run mode - showing changes for environment: dev', $output);
    }

    public function testListProviders(): void
    {
        // Test execution with --list-providers option
        $this->commandTester->execute([
            '--list-providers' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No configuration providers registered', $output);
    }

    public function testExecuteWithInvalidEnvironment(): void
    {
        // Test execution with invalid environment
        $this->commandTester->execute([
            '--env' => 'invalid_env'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Applying configuration for environment: invalid_env', $output);
    }
}
