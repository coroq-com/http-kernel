<?php
namespace Coroq\HttpKernel\Component;
use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface {
  public function emitResponse(ResponseInterface $response): void;
}
