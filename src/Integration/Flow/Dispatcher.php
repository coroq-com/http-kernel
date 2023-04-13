<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\Component\DispatcherInterface;
use Coroq\Flow\Flow;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements DispatcherInterface {
  public function dispatch(ServerRequestInterface $request, $route, $controller, ResponseInterface $response): ResponseInterface {
    if (!($controller instanceof Flow)) {
      throw new InvalidArgumentException();
    }
    $responder = new Responder($response);
    $result = $controller(compact("responder"));
    if (!isset($result["response"]) || !($result["response"] instanceof ResponseInterface)) {
      throw new DomainException("Controller must return an object of ResponseInterface.");
    }
    return $result["response"];
  }
}
