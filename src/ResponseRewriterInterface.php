<?php
namespace Coroq\HttpKernel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseRewriterInterface {
  public function rewriteResponse(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface;
}
