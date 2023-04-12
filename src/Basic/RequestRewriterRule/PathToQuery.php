<?php
namespace Coroq\HttpKernel\Basic\RequestRewriterRule;
use Psr\Http\Message\ServerRequestInterface;

class PathToQuery implements RuleInterface {
  /** @var string */
  protected $format;

  public function __construct(string $format) {
    $this->format = $format;
  }

  public function rewrite(ServerRequestInterface $request): ServerRequestInterface {
    $path = $request->getUri()->getPath();
    $path = explode("/", ltrim($path, "/")); // assuming path is absolute
    $format = explode("/", ltrim($this->format, "/"));
    $newPath = [];
    $query = [];
    foreach ($format as $index => $formatItem) {
      if (!isset($path[$index])) {
        return $request;
      }
      $pathItem = $path[$index];
      if (preg_match('#^\{([a-z_][a-z0-9_]*)(:.+?)?\}$#i', $formatItem, $matches)) {
        $query[$matches[1]] = urldecode($pathItem);
        if (isset($matches[2])) {
          $newPath[] = substr($matches[2], 1);
        }
        continue;
      }
      if ($formatItem === "" || $pathItem == $formatItem) {
        $newPath[] = $pathItem;
        continue;
      }
      return $request;
    }
    $request = $request->withUri($request->getUri()->withPath("/" . join("/", $newPath)));
    $request = $request->withQueryParams($query + $request->getQueryParams());
    return $request;
  }
}
