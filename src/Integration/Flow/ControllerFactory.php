<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\Component\ControllerFactoryInterface;
use Coroq\Flow\Flow;
use Coroq\HttpKernel\Integration\Flow\ControllerFactory\Instantiator\InstantiatorInterface;
use Coroq\HttpKernel\Integration\Flow\ControllerFactory\Instantiator\ViaNewOperator;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

class ControllerFactory implements ControllerFactoryInterface {
  /** @var ?Flow */
  private $flow;

  /** @var InstantiatorInterface */
  private $instantiator;

  public function __construct(?Flow $flow = null) {
    $this->flow = $flow;
    $this->instantiator = new ViaNewOperator();
  }

  public function setInstantiator(InstantiatorInterface $instantiator): void {
    $this->instantiator = $instantiator;
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
    foreach ($route as $waypoint) {
      $step = $this->instantiate($waypoint);
      $flow->appendStep($step);
    }
    return $flow;
  }

  private function instantiate($waypoint): callable {
    if (is_callable($waypoint)) {
      return $this->instantiateCallable($waypoint);
    }
    return $waypoint;
  }

  private function instantiateCallable(callable $callable): callable {
    if (is_string($callable)) {
      return $this->instantiateStringCallable($callable);
    }
    if (is_array($callable)) {
      return $this->instantiateArrayCallable($callable);
    }
    return $callable;
  }

  private function instantiateStringCallable(string $callable): callable {
    $arrayCallable = explode('::', $callable);
    if (count($arrayCallable) != 2) {
      return $callable;
    }
    return $this->instantiateArrayCallable($arrayCallable);
  }

  private function instantiateArrayCallable(array $callable): callable {
    list($class, $method) = $callable;
    if (!is_string($class)) {
      return $callable;
    }
    // static method
    $methodInfo = new ReflectionMethod($class, $method);
    if ($methodInfo->getModifiers() & ReflectionMethod::IS_STATIC) {
      return $callable;
    }
    // $callable was [classNameString, methodNameString]
    $instance = $this->instantiator->instantiate($class);
    if (!$instance) {
      throw new LogicException("Could not create an instance of $class");
    }
    return [$instance, $method];
  }
}
