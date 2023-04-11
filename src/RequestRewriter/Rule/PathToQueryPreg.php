<?php
namespace Coroq\HttpKernel\RequestRewriter\Rule;
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
    list($path, $query) = call_user_func_array($this->callback, $matches);
    $request = $request->withUri($request->getUri()->withPath($path));
    $request = $request->withQueryParams($query + $request->getQueryParams());
    return $request;
  }
}
