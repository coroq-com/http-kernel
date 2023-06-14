<?php
namespace Coroq\HttpKernel\Basic;

use Coroq\HttpKernel\Basic\BasicRequestRewriterRule\RuleInterface;
use Coroq\HttpKernel\Component\RequestRewriterInterface;
use Psr\Http\Message\ServerRequestInterface;

class BasicRequestRewriter implements RequestRewriterInterface {
  /** @var array<RuleInterface> */
  private $rules;

  /**
   * @param array<RuleInterface> $rules
   */
  public function __construct(array $rules = []) {
    $this->rules = $rules;
  }

  public function rewriteRequest(ServerRequestInterface $request): ServerRequestInterface {
    foreach ($this->rules as $rule) {
      $request = $rule->rewrite($request);
    }
    return $request;
  }

  public function addRule(RuleInterface $rule): void {
    $this->rules[] = $rule;
  }
}
