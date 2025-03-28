<?php

declare(strict_types=1);

namespace WeAreGenuine\DrupalConfigOrchestrator\Utility;

/**
 * Utility class for environment-related operations.
 */
class EnvironmentUtility {

  /**
   * Retrieves the value of an environment variable or throws an exception.
   *
   * @param string $name
   *   The name of the environment variable.
   *
   * @return string
   *   The value of the environment variable.
   *
   * @throws \Exception
   *   If the environment variable is not set.
   */
  public static function getEnvOrThrow(string $name): string {
    $value = getenv($name);
    if (empty($value)) {
      throw new \Exception("$name environment variable is not set.");
    }
    return $value;
  }

}
