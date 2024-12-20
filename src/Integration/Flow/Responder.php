<?php
namespace Coroq\HttpKernel\Integration\Flow;

use Coroq\Flow\Flow;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Responder {
  /** @var ResponseInterface */
  private $response;

  /** @var Flow */
  private $controller;

  /** @var callable */
  private $jsonEncoder;

  public function __construct(ResponseInterface $response, Flow $controller, ?callable $jsonEncoder = null) {
    $this->response = $response;
    $this->controller = $controller;
    $this->jsonEncoder = $jsonEncoder ?? [$this, "encodeJson"];
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function ok($body = null): array {
    $this->controller->break();
    $response = $this->response->withStatus(200);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param mixed $data
   * @return array<string,ResponseInterface>
   */
  public function okJson($data): array {
    $this->controller->break();
    $response = $this->response->withStatus(200);
    $response = $response->withHeader("Content-Type", "application/json");
    $response->getBody()->write(($this->jsonEncoder)($data));
    return compact("response");
  }

  /**
   * @param string|StreamInterface $body
   * @return array<string,ResponseInterface>
   */
  public function okDownload($body, string $contentType, ?string $fileName = null): array {
    $this->controller->break();
    $response = $this->response->withStatus(200);
    $response = $response->withHeader("Content-Type", $contentType);
    $dispositionHeader = "attachment;";
    if ($fileName !== null) {
      $dispositionHeader .= "filename*=UTF-8''" . rawurlencode($fileName);
    }
    $response = $response->withHeader("Content-Disposition", $dispositionHeader);
    if ($body instanceof StreamInterface) {
      $response = $response->withBody($body);
    }
    else {
      $response->getBody()->write($body);
    }
    return compact("response");
  }

  /**
   * @param mixed $url
   * @param array<string,string> $query
   * @return array<string,ResponseInterface>
   */
  public function found($url, array $query = [], string $fragment = null): array {
    $this->controller->break();
    if ($query) {
      $url .= "?" . http_build_query($query);
    }
    if ($fragment !== null) {
      $url .= "#$fragment";
    }
    $response = $this->response->withStatus(301);
    $response = $response->withHeader("Location", $url);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function forbidden($body = null): array {
    $this->controller->break();
    $response = $this->response->withStatus(403);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function notFound($body = null): array {
    $this->controller->break();
    $response = $this->response->withStatus(404);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function serviceUnavailable($body = null): array {
    $this->controller->break();
    $response = $this->response->withStatus(503);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   */
  private function writeToResponseBody(ResponseInterface $response, $body): void {
    if ($body === null) {
      return;
    }
    if ($body instanceof StreamInterface) {
      $response = $response->withBody($body);
    }
    elseif (is_string($body)) {
      $response->getBody()->write($body);
    }
    else {
      throw new InvalidArgumentException();
    }
  }

  /**
   * @param mixed $data
   */
  private function encodeJson($data): string {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if ($json === false) {
      throw new RuntimeException();
    }
    return $json;
  }
}
