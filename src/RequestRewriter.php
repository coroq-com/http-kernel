<?php
namespace Coroq\HttpKernel;

use Coroq\HttpKernel\RequestRewriterRule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestRewriter {
  /** @var array<RuleInterface> */
  protected $rules;

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