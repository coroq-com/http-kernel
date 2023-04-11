<?php
namespace Coroq\HttpKernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerFactoryInterface {
  /**
   * @param mixed $route
   * @return mixed|ResponseInterface
   */
  public function createController(ServerRequestInterface $request, $route);
}
