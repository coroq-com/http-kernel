<?php
use Coroq\HttpKernel\Basic\BasicRequestRewriterRule\PathToQuery;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers Coroq\HttpKernel\Basic\RequestRewriterRule\PathToQuery;
 */
class RewritePathToQueryTest extends TestCase {
  private function makeRequest(string $path): ServerRequestInterface {
    return new ServerRequest("get", $path);
  }

  public function testNotMatch() {
    $rule = new PathToQuery("/abc/{param}");
    $request = $this->makeRequest("/def/123");
    $this->assertEquals($request, $rule->rewrite($request));
  }

  public function testMatchAtBegining() {
    $rule = new PathToQuery("/{param}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/", $result->getUri()->getPath());
  }

  public function testMatchAtBeginingWithTrailingSlash() {
    $rule = new PathToQuery("/{param}/");
    $request = $this->makeRequest("/123/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/", $result->getUri()->getPath());
  }

  public function testMatchAtEnd() {
    $rule = new PathToQuery("/abc/def/{param}");
    $request = $this->makeRequest("/abc/def/123");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def", $result->getUri()->getPath());
  }

  public function testMatchAtEndWithTrailingSlash() {
    $rule = new PathToQuery("/abc/def/{param}/");
    $request = $this->makeRequest("/abc/def/123/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def/", $result->getUri()->getPath());
  }

  public function testMatchInMiddle() {
    $rule = new PathToQuery("/abc/{param}/def");
    $request = $this->makeRequest("/abc/123/def");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def", $result->getUri()->getPath());
  }

  public function testMatchInMiddleWithTrailingSlash() {
    $rule = new PathToQuery("/abc/{param}/def/");
    $request = $this->makeRequest("/abc/123/def/");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def/", $result->getUri()->getPath());
  }

  public function testMatchPartialy() {
    $rule = new PathToQuery("/abc/{param}");
    $request = $this->makeRequest("/abc/123/def");
    $result = $rule->rewrite($request);
    $this->assertEquals(["param" => "123"], $result->getQueryParams());
    $this->assertEquals("/abc/def", $result->getUri()->getPath());
  }
}
