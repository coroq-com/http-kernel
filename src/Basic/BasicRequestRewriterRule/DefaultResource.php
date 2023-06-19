<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Basic\BasicRequestRewriterRule;
use Psr\Http\Message\ServerRequestInterface;

class DefaultResource implements RuleInterface {
  /** @var string */
  private $defaultResourceName;

  public function __construct(string $defaultResourceName) {
    $this->defaultResourceName = $defaultResourceName;
  }

  public function rewrite(ServerRequestInterface $request): ServerRequestInterface {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if (substr($path, -1) != '/') {
      return $request;
    }
    $uri = $uri->withPath($path . $this->defaultResourceName);
    return $request->withUri($uri);
  }
}
