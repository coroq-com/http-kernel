<?php
namespace Coroq\HttpKernel;

use Coroq\HttpKernel\Component\ControllerFactoryInterface;
use Coroq\HttpKernel\Component\DispatcherInterface;
use Coroq\HttpKernel\Component\RequestRewriterInterface;
use Coroq\HttpKernel\Component\ResponseEmitterInterface;
use Coroq\HttpKernel\Component\ResponseRewriterInterface;
use Coroq\HttpKernel\Component\RouterInterface;
use Coroq\HttpKernel\Exception\HttpExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HttpKernel {
  /** @var ?RequestRewriterInterface */
  private $requestRewriter;

  /** @var ?RouterInterface */
  private $router;

  /** @var ?ControllerFactoryInterface */
  private $controllerFactory;

  /** @var DispatcherInterface */
  private $dispatcher;

  /** @var ?ResponseRewriterInterface */
  private $responseRewriter;

  /** @var ?ResponseEmitterInterface */
  private $responseEmitter;

  /** @var ?LoggerInterface */
  private $logger;

  public function __construct(
    ?RequestRewriterInterface $requestRewriter,
    ?RouterInterface $router,
    ?ControllerFactoryInterface $controllerFactory,
    DispatcherInterface $dispatcher,
    ?ResponseRewriterInterface $responseRewriter,
    ?ResponseEmitterInterface $responseEmitter,
    ?LoggerInterface $logger)
  {
    $this->requestRewriter =$requestRewriter;
    $this->router =$router;
    $this->controllerFactory =$controllerFactory;
    $this->dispatcher =$dispatcher;
    $this->responseRewriter =$responseRewriter;
    $this->responseEmitter =$responseEmitter;
    $this->logger =$logger;
  }

  public function handleRequest(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    $this->logRequest($request, "Request");
    try {
      $response = $this->createAndExecuteController($request, $response);
      $this->logResponse($response, "Response");
    }
    catch (HttpExceptionInterface $exception) {
      $response = $response->withStatus(
        $exception->getStatusCode(),
        $exception->getReasonPhrase()
      );
      foreach ($exception->getHeaders() as $name => $value) {
        $response = $response->withHeader($name, $value);
      }
    }

    // Rewrite response
    if ($this->responseRewriter) {
      $response = $this->responseRewriter->rewriteResponse($response, $request);
      $this->logResponse($response, "Response rewritten");
    }

    // Emit response
    if ($this->responseEmitter) {
      $this->responseEmitter->emitResponse($response);
      $this->logDebug("Response emitted.");
    }
    return $response;
  }

  private function createAndExecuteController(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    // Rewrite request
    if ($this->requestRewriter) {
      $request = $this->requestRewriter->rewriteRequest($request);
      if ($request instanceof ResponseInterface) {
        return $request;
      }
      $this->logRequest($request, "Rewritten request");
    }

    // Route
    $route = null;
    if ($this->router) {
      $route = $this->router->route($request);
      if ($route instanceof ResponseInterface) {
        return $route;
      }
      $this->logDebug("Route", compact("route"));
    }

    // Create controller
    $controller = null;
    if ($this->controllerFactory) {
      $controller = $this->controllerFactory->createController($request, $route);
      if ($controller instanceof ResponseInterface) {
        return $controller;
      }
      $this->logDebug("Controller", compact("controller"));
    }

    // Dispatch
    return $this->dispatcher->dispatch($request, $route, $controller, $response);
  }

  public function setLogger(LoggerInterface $logger): void {
    $this->logger = $logger;
  }

  /**
   * @param array<string,mixed> $context
   */
  private function logDebug(string $message, array $context = []): void {
    if ($this->logger) {
      $this->logger->debug($message, $context);
    }
  }

  private function logRequest(ServerRequestInterface $request, string $message): void {
    $this->logDebug($message, [
      "method" => $request->getMethod(),
      "uri" => (string)$request->getUri(),
      "headers" => $request->getHeaders(),
      "body_length" => $request->getBody()->getSize(),
    ]);
  }

  private function logResponse(ResponseInterface $response, string $message): void {
    $this->logDebug($message, [
      "status_code" => $response->getStatusCode(),
      "headers" => $response->getHeaders(),
      "body_length" => $response->getBody()->getSize(),
    ]);
  }
}
