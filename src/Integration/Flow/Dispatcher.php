<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\Component\DispatcherInterface;
use Coroq\Flow\Flow;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements DispatcherInterface {
  /**
   * @var callable
   */
  private $responderFactory;

  public function __construct(?callable $responderFactory = null) {
    $this->responderFactory = $responderFactory ?? function(ResponseInterface $response, Flow $controller) {
      return new Responder($response, $controller);
    };
  }

  public function dispatch(ServerRequestInterface $request, $route, $controller, ResponseInterface $response): ResponseInterface {
    if (!($controller instanceof Flow)) {
      throw new InvalidArgumentException();
    }
    $responder = ($this->responderFactory)($response, $controller);
    $result = $controller(compact("request", "response", "responder"));
    if (!isset($result["response"]) || !($result["response"] instanceof ResponseInterface)) {
      throw new DomainException("Controller must return an object of ResponseInterface for 'response' key.");
    }
    return $result["response"];
  }
}
