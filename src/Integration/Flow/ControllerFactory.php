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

  /**
   * @param mixed $waypoint
   */
  private function instantiate($waypoint): callable {
    if (is_string($waypoint)) {
      return $this->instantiateStringWaypoint($waypoint);
    }
    if (is_array($waypoint)) {
      return $this->instantiateArrayWaypoint($waypoint);
    }
    if (is_callable($waypoint)) {
      return $waypoint;
    }
    if (is_object($waypoint)) {
      $class = get_class($waypoint);
      throw new InvalidArgumentException("Invalid waypoint: $class object");
    }
    $type = gettype($waypoint);
    throw new InvalidArgumentException("Invalid waypoint: $waypoint ($type)");
  }

  private function instantiateStringWaypoint(string $waypoint): callable {
    $arrayWaypoint = explode('::', $waypoint);
    if (count($arrayWaypoint) != 2) {
      if (is_callable($waypoint)) {
        return $waypoint;
      }
      throw new InvalidArgumentException("Invalid waypoint: $waypoint");
    }
    return $this->instantiateArrayWaypoint($arrayWaypoint);
  }

  /**
   * @param array<mixed> $waypoint
   */
  private function instantiateArrayWaypoint(array $waypoint): callable {
    if (count($waypoint) != 2) {
      throw new InvalidArgumentException("Invalid waypoint: " . print_r($waypoint, true));
    }
    list($class, $method) = $waypoint;
    if (is_object($class)) {
      if (is_callable($waypoint)) {
        return $waypoint;
      }
      throw new InvalidArgumentException("Invalid waypoint: " . get_class($class));
    }
    if (!is_string($class) || !is_string($method)) {
      throw new InvalidArgumentException("Invalid waypoint: " . print_r($waypoint, true));
    }
    // static method
    $methodInfo = new ReflectionMethod($class, $method);
    if ($methodInfo->getModifiers() & ReflectionMethod::IS_STATIC) {
      if (is_callable($waypoint)) {
        return $waypoint;
      }
      throw new InvalidArgumentException("Invalid waypoint: " . join("::", $waypoint));
    }
    // $callable was [classNameString, methodNameString]
    $instance = $this->instantiator->instantiate($class);
    if (!$instance) {
      throw new LogicException("Invalid waypoint: Could not create an instance of $class");
    }
    $instanceAndMethod = [$instance, $method];
    if (!is_callable($instanceAndMethod)) {
      throw new LogicException("Invalid waypoint: " . join("::", $waypoint));
    }
    return $instanceAndMethod;
  }
}
