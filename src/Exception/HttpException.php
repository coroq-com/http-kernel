<?php
declare(strict_types=1);

namespace Coroq\HttpKernel\Exception;

use RuntimeException;
use Throwable;

abstract class HttpException extends RuntimeException implements HttpExceptionInterface {
  /** @var int */
  private $statusCode;

  /** @var string */
  private $reasonPhrase;

  /** @var array<string,mixed> */
  private $headers;

  public function __construct(int $statusCode, string $message = '', int $code = 0, Throwable $previous = null) {
    $this->statusCode = $statusCode;
    $this->reasonPhrase = '';
    $this->headers = [];
    parent::__construct($message, $code, $previous);
  }

  public function getStatusCode(): int {
    return $this->statusCode;
  }

  public function getReasonPhrase(): string {
    return $this->reasonPhrase;
  }

  public function setReasonPhrase(string $reasonPhrase): void {
    $this->reasonPhrase = $reasonPhrase;
  }

  /**
   * @return array<string,mixed>
   */
  public function getHeaders(): array {
    return $this->headers;
  }

  /**
   * @param array<string,mixed> $headers
   */
  public function setHeaders(array $headers): void {
    $this->headers = $headers;
  }
}
