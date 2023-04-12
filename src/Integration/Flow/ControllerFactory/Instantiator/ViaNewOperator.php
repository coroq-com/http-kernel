<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Integration\Flow\ControllerFactory\Instantiator;

class ViaNewOperator implements InstantiatorInterface {
  public function instantiate(string $className): ?object {
    return new $className();
  }
}
