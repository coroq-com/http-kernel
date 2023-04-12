<?php
namespace Coroq\HttpKernel\RequestRewriterRule;

use Psr\Http\Message\ServerRequestInterface;

interface RuleInterface {
  public function rewrite(ServerRequestInterface $request): ServerRequestInterface;
}
