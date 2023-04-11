<?php
namespace Coroq\HttpKernel\RequestRewriter\Rule;

use Psr\Http\Message\ServerRequestInterface;

interface RuleInterface {
  public function rewrite(ServerRequestInterface $request): ServerRequestInterface;
}
