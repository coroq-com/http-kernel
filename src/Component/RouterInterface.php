<?php
namespace Coroq\HttpKernel\Component;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface {
  /**
   * @return mixed
   */
  public function route(ServerRequestInterface $request);
}
