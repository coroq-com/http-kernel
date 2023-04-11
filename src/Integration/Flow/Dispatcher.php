<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\DispatcherInterface;
use Coroq\Flow\Flow;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements DispatcherInterface {
  /** @var ResponseFactoryInterface */
  private $responseFactory;

  public function __construct(ResponseFactoryInterface $responseFactory) {
    $this->responseFactory = $responseFactory;
  }

  public function dispatch(ServerRequestInterface $request, $route, $controller): ResponseInterface {
    if (!($controller instanceof Flow)) {
      throw new InvalidArgumentException();
    }
    $responder = new Responder($this->responseFactory);
    $result = $controller(compact("responder"));
    if (!isset($result["response"]) || !($result["response"] instanceof ResponseInterface)) {
      throw new DomainException("Controller must return an object of ResponseInterface.");
    }
    return $result["response"];
  }
}
