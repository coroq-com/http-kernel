<?php
namespace Coroq\HttpKernel\Basic\RequestRewriterRule;
use Psr\Http\Message\ServerRequestInterface;

class ParseJsonBody implements RuleInterface {
  public function rewrite(ServerRequestInterface $request): ServerRequestInterface {
    $content_type = $request->getHeaderLine("content-type");
    if (!in_array($content_type, ["application/json"])) {
      return $request;
    }
    return $request->withParsedBody(json_decode((string)$request->getBody(), true));
  }
}
