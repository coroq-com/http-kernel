<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Basic\BasicRequestRewriterRule;

use Psr\Http\Message\ServerRequestInterface;

class PathToQuery implements RuleInterface {
  /** @var string */
  private $format;

  /** @var array<string,PathToQueryOption> */
  private $options;

  /**
   * @param string $format Format string for path of URL with placeholders
   * @param array<string,PathToQueryOption> $options the keys are the name of placeholder
   */
  public function __construct(string $format, array $options = []) {
    $this->format = $format;
    $this->options = $options;
  }

  public function rewrite(ServerRequestInterface $request): ServerRequestInterface {
    $path = $request->getUri()->getPath();
    // check if the path matches the format and get values from path
    $pattern = preg_quote($this->format);
    $pattern = preg_replace('#\\\\{([a-z_][a-z0-9_]*)\\\\}#ui', '(?P<$1>[^/]*)', $pattern);
    $pattern = '#\A' . $pattern . '#u';
    if (!preg_match($pattern, $path, $pathMatches)) {
      return $request;
    }
    $matchedPathLength = strlen($pathMatches[0]);
    $values = [];
    foreach ($pathMatches as $placeholderName => $value) {
      if (!is_string($placeholderName)) {
        continue;
      }
      $values[$placeholderName] = urldecode($value);
    }
    // complement default value for empty values
    foreach ($values as $placeholderName => $value) {
      if ($value === '' && isset($this->options[$placeholderName]->default)) {
        $values[$placeholderName] = $this->options[$placeholderName]->default;
      }
    }
    // validate values
    foreach ($values as $placeholderName => $value) {
      if (!$this->assertValueConformsToFormat($placeholderName, $value)) {
        return $request;
      }
    }
    // replace
    $replaced = preg_replace_callback('#\{([a-z_][a-z0-9_]*)\}#ui', function($matches): string {
      $placehodlerName = $matches[1];
      assert(is_string($placehodlerName));
      $replacement = $this->options[$placehodlerName]->replacement ?? null;
      $replacement = $replacement ?? $placehodlerName;
      return $replacement;
    }, $this->format);
    $newPath = substr_replace($path, $replaced, 0, $matchedPathLength);
    // update the request
    $request = $request->withUri($request->getUri()->withPath($newPath));
    $request = $request->withQueryParams($values + $request->getQueryParams());
    return $request;
  }

  private function assertValueConformsToFormat(string $name, string $value): bool {
    $format = $this->options[$name]->format ?? null;
    if ($format === null) {
      return true;
    }
    return (bool)$format($value, $name);
  }
}
