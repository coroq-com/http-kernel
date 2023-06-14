<?php
namespace Coroq\HttpKernel\Basic;

use Coroq\HttpKernel\Component\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class BasicResponseEmitter implements ResponseEmitterInterface {
  public function emitResponse(ResponseInterface $response): void {
    foreach ($response->getHeaders() as $name => $values) {
      foreach ($values as $value) {
        header("$name: $value", false);
      }
    }
    http_response_code($response->getStatusCode());
    $body = $response->getBody();
    if ($body->isSeekable()) {
      $body->rewind();
    }
    while (!$body->eof()) {
      echo $body->read(1024 * 1024);
    }
  }
}
