<?php
namespace Coroq\HttpKernel\Basic\RequestRewriterRule;

use Psr\Http\Message\ServerRequestInterface;

interface RuleInterface {
  public function rewrite(ServerRequestInterface $request): ServerRequestInterface;
}
