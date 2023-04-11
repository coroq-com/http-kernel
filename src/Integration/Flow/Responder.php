<?php
namespace Coroq\HttpKernel\Integration\Flow;

use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Responder {
  /** @var ResponseFactoryInterface */
  private $responseFactory;

  /** @var callable */
  private $jsonEncoder;

  public function __construct(ResponseFactoryInterface $responseFactory, ?callable $jsonEncoder = null) {
    $this->responseFactory = $responseFactory;
    $this->jsonEncoder = $jsonEncoder ?? [$this, "encodeJson"];
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function ok($body = null): array {
    $response = $this->responseFactory->createResponse(200);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param mixed $data
   * @return array<string,ResponseInterface>
   */
  public function okJson($data): array {
    $response = $this->responseFactory->createResponse(200);
    $response = $response->withHeader("Content-Type", "application/json");
    $response->getBody()->write(($this->jsonEncoder)($data));
    return compact("response");
  }

  /**
   * @return array<string,ResponseInterface>
   */
  public function okDownload(StreamInterface $body, string $contentType, ?string $fileName = null): array {
    $response = $this->responseFactory->createResponse(200);
    $response = $response->withHeader("Content-Type", $contentType);
    $dispositionHeader = "attachment;";
    if ($fileName !== null) {
      $dispositionHeader .= "filename='$fileName';filename*=UTF-8''" . rawurlencode($fileName);
    }
    $response = $response->withHeader("Content-Disposition", $dispositionHeader);
    $response = $response->withBody($body);
    return compact("response");
  }

  /**
   * @param mixed $url
   * @param array<string,string> $query
   * @return array<string,ResponseInterface>
   */
  public function found($url, array $query = [], string $fragment = null): array {
    if ($query) {
      $url .= "?" . http_build_query($query);
    }
    if ($fragment !== null) {
      $url .= "#$fragment";
    }
    $response = $this->responseFactory->createResponse(301);
    $response = $response->withHeader("Location", $url);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function forbidden($body = null): array {
    $response = $this->responseFactory->createResponse(403);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function notFound($body = null): array {
    $response = $this->responseFactory->createResponse(404);
    $this->writeToResponseBody($response, $body);
    return compact("response");
  }

  /**
   * @param string|StreamInterface|null $body
   * @return array<string,ResponseInterface>
   */
  public function serviceUnavailable($body = null): array {
    $response = $this->responseFactory->createResponse(503);
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
