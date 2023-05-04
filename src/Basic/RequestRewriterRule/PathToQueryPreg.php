<?php
namespace Coroq\HttpKernel\Basic\RequestRewriterRule;
use Psr\Http\Message\ServerRequestInterface;

class PathToQueryPreg implements RuleInterface {
  /** @var string */
  protected $pattern;
  /** @var callable */
  protected $callback;

  public function __construct(string $pattern, callable $callback) {
    $this->pattern = $pattern;
    $this->callback = $callback;
  }

  public function rewrite(ServerRequestInterface $request): ServerRequestInterface {
    if (!preg_match($this->pattern, $request->getUri()->getPath(), $matches)) {
      return $request;
    }
    list($path, $query) = call_user_func_array($this->callback, array_slice($matches, 1));
    $request = $request->withUri($request->getUri()->withPath($path));
    $request = $request->withQueryParams($query + $request->getQueryParams());
    return $request;
  }
}
