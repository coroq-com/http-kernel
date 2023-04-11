<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\ControllerFactoryInterface;
use Coroq\Flow\Flow;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class ControllerFactory implements ControllerFactoryInterface {
  /** @var ?Flow */
  private $flow;

  public function __construct(?Flow $flow = null) {
    $this->flow = $flow;
  }

  public function createController(ServerRequestInterface $request, $route): Flow {
    if (!is_array($route)) {
      throw new InvalidArgumentException();
    }
    if ($this->flow) {
      $flow = clone $this->flow;
    }
    else {
      $flow = new Flow();
    }
    foreach ($route as $callable) {
      $flow->appendStep($callable);
    }
    return $flow;
  }
}
