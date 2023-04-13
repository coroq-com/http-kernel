<?php
namespace Coroq\HttpKernel\Component;

use Psr\Http\Message\ServerRequestInterface;

interface ControllerFactoryInterface {
  /**
   * @param mixed $route
   * @return mixed
   */
  public function createController(ServerRequestInterface $request, $route);
}
