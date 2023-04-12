<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Integration\Flow\ControllerFactory\Instantiator;

interface InstantiatorInterface {
  public function instantiate(string $className): ?object;
}
