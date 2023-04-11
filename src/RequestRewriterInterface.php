<?php
namespace Coroq\HttpKernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestRewriterInterface {
  /**
   * @return ServerRequestInterface|ResponseInterface
   */
  public function rewriteRequest(ServerRequestInterface $request);
}
