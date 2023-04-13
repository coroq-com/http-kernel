<?php
declare(strict_types=1);

namespace Coroq\HttpKernel\Exception;

use Throwable;

/**
 * Interface for HTTP error exceptions.
 */
interface HttpExceptionInterface extends Throwable {
  /**
   * @return int the status code.
   */
  public function getStatusCode(): int;

  /**
   * @return string the reason phrase.
   */
  public function getReasonPhrase(): string;

  /**
   * @return array<mixed> response headers.
   */
  public function getHeaders(): array;
}
