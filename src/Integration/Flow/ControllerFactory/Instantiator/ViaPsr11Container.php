<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Integration\Flow\ControllerFactory\Instantiator;

use Psr\Container\ContainerInterface;

class ViaPsr11Container implements InstantiatorInterface {
  /** @var ContainerInterface */
  private $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function instantiate(string $className): ?object {
    if (!$this->container->has($className)) {
      return null;
    }
    return $this->container->get($className);
  }
}
