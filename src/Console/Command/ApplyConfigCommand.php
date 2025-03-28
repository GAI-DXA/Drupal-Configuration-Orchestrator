<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WeAreGenuine\DrupalConfigOrchestrator\ConfigurationManager;
use WeAreGenuine\DrupalConfigOrchestrator\Utility\EnvironmentDetector;

/**
 * Command to apply configuration overrides.
 */
class ApplyConfigCommand extends Command {

  /**
   * The name of the command.
   *
   * @var string
   */
  protected static $defaultName = 'apply-config';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected static $defaultDescription = 'Apply environment-specific configuration overrides';

  /**
   * The configuration manager service.
   *
   * @var \WeAreGenuine\DrupalConfigOrchestrator\ConfigurationManager
   */
  private ConfigurationManager $manager;

  /**
   * The environment detector service.
   *
   * @var \WeAreGenuine\DrupalConfigOrchestrator\Utility\EnvironmentDetector
   */
  private EnvironmentDetector $environmentDetector;

  public function __construct(ConfigurationManager $manager, EnvironmentDetector $environmentDetector) {
    parent::__construct();
    $this->manager = $manager;
    $this->environmentDetector = $environmentDetector;
  }

  /**
   * Configures the command options.
   */
  protected function configure(): void {
    $this
      ->addOption(
        'env',
        'e',
        InputOption::VALUE_REQUIRED,
        'Target environment (dev, test, prod)'
      )
      ->addOption(
        'list-providers',
        'l',
        InputOption::VALUE_NONE,
        'List all registered configuration providers'
      )
      ->addOption(
        'dry-run',
        'd',
        InputOption::VALUE_NONE,
        'Show configuration changes without applying them'
      );
  }

  /**
   * Executes the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input interface.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   *
   * @return int
   *   The command exit code.
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    if ($input->getOption('list-providers')) {
      return $this->listProviders($output);
    }

    $environment = $input->getOption('env') ?? $this->environmentDetector->detect();
    if (!$environment) {
      $output->writeln('<error>No environment specified and could not detect environment</error>');
      return Command::FAILURE;
    }

    $dryRun = $input->getOption('dry-run');
    if ($dryRun) {
      $output->writeln("<info>Dry run mode - showing changes for environment: {$environment}</info>");
    }
    else {
      $output->writeln("<info>Applying configuration for environment: {$environment}</info>");
    }

    try {
      $result = $this->manager->applyConfigurations($environment, $dryRun);
      $this->outputResults($output, $result);
      return Command::SUCCESS;
    }
    catch (\Exception $e) {
      $output->writeln("<error>{$e->getMessage()}</error>");
      return Command::FAILURE;
    }
  }

  /**
   * Lists all registered configuration providers.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   *
   * @return int
   *   The command exit code.
   */
  private function listProviders(OutputInterface $output): int {
    $providers = $this->manager->getProviders();
    if (empty($providers)) {
      $output->writeln('<comment>No configuration providers registered</comment>');
      return Command::SUCCESS;
    }

    $output->writeln('<info>Registered configuration providers:</info>');
    foreach ($providers as $provider) {
      $output->writeln(sprintf(
        '- %s (%s)',
        get_class($provider),
        implode(', ', $provider->getApplicableEnvironments())
      ));
    }

    return Command::SUCCESS;
  }

  /**
   * Outputs the results of configuration updates.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output interface.
   * @param array $result
   *   The configuration update results containing success count and details.
   */
  private function outputResults(OutputInterface $output, array $result): void {
    $output->writeln(sprintf(
      '<info>Configuration updates completed: %d/%d successful</info>',
      $result['success'],
      $result['total']
    ));

    if (!empty($result['details'])) {
      $output->writeln('<comment>Details:</comment>');
      foreach ($result['details'] as $detail) {
        $output->writeln("- {$detail['message']}");
      }
    }
  }

}
