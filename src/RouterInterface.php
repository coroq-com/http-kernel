<?php
namespace Coroq\HttpKernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface {
  /**
   * @return mixed|ResponseInterface
   */
  public function route(ServerRequestInterface $request);
}
