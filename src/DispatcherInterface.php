<?php
namespace Coroq\HttpKernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface DispatcherInterface {
  /**
   * @param mixed $route
   * @param mixed $controller
   */
  public function dispatch(ServerRequestInterface $request, $route, $controller): ResponseInterface;
}
