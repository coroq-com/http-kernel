<?php
namespace Coroq\HttpKernel\Basic\BasicRequestRewriterRule;

use Psr\Http\Message\ServerRequestInterface;

interface RuleInterface {
  public function rewrite(ServerRequestInterface $request): ServerRequestInterface;
}
