<?php
namespace Coroq\HttpKernel\Component;

use Psr\Http\Message\ServerRequestInterface;

interface RequestRewriterInterface {
  public function rewriteRequest(ServerRequestInterface $request): ServerRequestInterface;
}
