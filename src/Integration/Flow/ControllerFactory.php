<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\HttpKernel\ControllerFactoryInterface;
use Coroq\Flow\Flow;
use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

class ControllerFactory implements ControllerFactoryInterface {
  /** @var ?Flow */
  private $flow;

  /** @var ?ContainerInterface */
  private $diContainer;

  public function __construct(?Flow $flow = null) {
    $this->flow = $flow;
  }

  public function setContainer(?ContainerInterface $diContainer): void {
    $this->diContainer = $diContainer;
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
      $callable = $this->instantiate($waypoint);
      $flow->appendStep($callable);
    }
    return $flow;
  }

  private function instantiate($waypoint): callable {
    $step = $this->getFromDiContainer($waypoint);
    if ($step) {
      if (!is_callable($step)) {
        throw new InvalidArgumentException("$waypoint in DI Container was not a callable.");
      }
      return $step;
    }
    if (!is_callable($waypoint)) {
      throw new InvalidArgumentException("$waypoint is not a callable.");
    }
    if (is_string($waypoint)) {
      return $this->instantiateString($waypoint);
    }
    if (is_array($waypoint)) {
      return $this->instantiateArray($waypoint);
    }
    return $waypoint;
  }

  private function getFromDiContainer(string $id) {
    if (!$this->diContainer) {
      return null;
    }
    if (!$this->diContainer->has($id)) {
      return null;
    }
    return $this->diContainer->get($id);
  }

  private function instantiateString(string $callable) {
    $arrayCallable = explode('::', $callable);
    if (count($arrayCallable) != 2) {
      return $callable;
    }
    return $this->instantiateArray($arrayCallable);
  }

  private function instantiateArray(array $callable) {
    $method = new ReflectionMethod($callable[0], $callable[1]);
    if ($method->getModifiers() & ReflectionMethod::IS_STATIC) {
      return $callable;
    }
    $class = $method->getDeclaringClass();
    $className = $class->getName();
    $instance = $this->getFromDiContainer($className);
    if (!$instance) {
      $constructor = $class->getConstructor();
      if ($constructor->getNumberOfParameters()) {
        throw new LogicException('Could not create an instance of ' . $callable[0]);
      }
      $instance = $class->newInstance();
    }
    return [$instance, $method->getName()];
  }
}
