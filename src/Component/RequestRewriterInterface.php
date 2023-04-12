<?php
namespace Coroq\HttpKernel\Component;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestRewriterInterface {
  /**
   * @return ServerRequestInterface|ResponseInterface
   */
  public function rewriteRequest(ServerRequestInterface $request);
}
