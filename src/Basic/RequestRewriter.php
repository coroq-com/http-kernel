<?php
namespace Coroq\HttpKernel\Basic;

use Coroq\HttpKernel\Basic\RequestRewriterRule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestRewriter {
  /** @var array<RuleInterface> */
  private $rules;

  /**
   * @param array<RuleInterface> $rules
   */
  public function __construct(array $rules) {
    $this->rules = $rules;
  }

  public function rewriteRequest(ServerRequestInterface $request): ServerRequestInterface {
    foreach ($this->rules as $rule) {
      $request = $rule->rewrite($request);
    }
    return $request;
  }
}
