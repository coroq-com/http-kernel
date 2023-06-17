<?php
use Coroq\HttpKernel\Basic\BasicRequestRewriterRule\PathToQuery;
use Coroq\HttpKernel\Basic\BasicRequestRewriterRule\PathToQueryOption;
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

  private function assertQueryParamsEqual(array $expected, ServerRequestInterface $request): void {
    $this->assertEquals($expected, $request->getQueryParams());
  }

  private function assertPathEquals(string $expected, ServerRequestInterface $request): void {
    $this->assertEquals($expected, $request->getUri()->getPath());
  }

  public function testShouldNotRewriteWhenPathDoesNotMatchFormat() {
    $rule = new PathToQuery("/abc/{param}");
    $request = $this->makeRequest("/def/123");
    $this->assertEquals($request, $rule->rewrite($request));
  }

  public function testShouldRewriteAtBeginningWhenPathMatchesFormat() {
    $rule = new PathToQuery("/{param}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/param", $result);
  }

  public function testShouldRewriteAtBeginningWithTrailingSlashWhenPathMatchesFormat() {
    $rule = new PathToQuery("/{param}/");
    $request = $this->makeRequest("/123/");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/param/", $result);
  }

  public function testShouldRewriteAtEndWhenPathMatchesFormat() {
    $rule = new PathToQuery("/abc/def/{param}");
    $request = $this->makeRequest("/abc/def/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/abc/def/param", $result);
  }

  public function testShouldRewriteAtEndWithTrailingSlashWhenPathMatchesFormat() {
    $rule = new PathToQuery("/abc/def/{param}/");
    $request = $this->makeRequest("/abc/def/123/");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/abc/def/param/", $result);
  }

  public function testShouldRewriteInMiddleWhenPathMatchesFormat() {
    $rule = new PathToQuery("/abc/{param}/def");
    $request = $this->makeRequest("/abc/123/def");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/abc/param/def", $result);
  }

  public function testShouldRewriteInMiddleWithTrailingSlashWhenPathMatchesFormat() {
    $rule = new PathToQuery("/abc/{param}/def/");
    $request = $this->makeRequest("/abc/123/def/");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/abc/param/def/", $result);
  }

  public function testShouldRewritePartiallyWhenPathMatchesFormat() {
    $rule = new PathToQuery("/abc/{param}");
    $request = $this->makeRequest("/abc/123/def");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["param" => "123"], $result);
    $this->assertPathEquals("/abc/param/def", $result);
  }

  public function testCanHandlePlaceholderNameOfUnderscore() {
    $rule = new PathToQuery("/{_}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["_" => "123"], $result);
    $this->assertPathEquals("/_", $result);
  }

  public function testCanHandlePlaceholderNameOfUppercase() {
    $rule = new PathToQuery("/{ABC}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["ABC" => "123"], $result);
    $this->assertPathEquals("/ABC", $result);
  }

  public function testShouldNotReplacePlaceholderNameStartingWithNumber() {
    $rule = new PathToQuery("/{1234567890}");
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual([], $result);
    $this->assertPathEquals("/123", $result);
  }

  public function testCanHandleMultiplePlaceholdersInPathSegment() {
    $rule = new PathToQuery("/{x}-{y}");
    $request = $this->makeRequest("/123-abc");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["x" => "123", "y" => "abc"], $result);
    $this->assertPathEquals("/x-y", $result);
  }

  public function testPlaceholderShouldTakeValueEagerly() {
    $rule = new PathToQuery("/{x}{y}");
    $request = $this->makeRequest("/123abc");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["x" => "123abc", "y" => ""], $result);
    $this->assertPathEquals("/xy", $result);
  }

  public function testPlaceholderShouldNotCrossSlash() {
    $rule = new PathToQuery("/{x}");
    $request = $this->makeRequest("/123/abc");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["x" => "123"], $result);
    $this->assertPathEquals("/x/abc", $result);
  }

  public function testShouldReplaceWithProvidedReplacementOption() {
    $rule = new PathToQuery("/{x}/{y}", [
      "x" => new PathToQueryOption(null, "xxx"),
      "y" => new PathToQueryOption(null, "yyy"),
    ]);
    $request = $this->makeRequest("/123/abc");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["x" => "123", "y" => "abc"], $result);
    $this->assertPathEquals("/xxx/yyy", $result);
  }

  public function testShouldPassFormatValidation() {
    $rule = new PathToQuery("/{x}", [
      "x" => new PathToQueryOption(function($value, $name) {
        $this->assertEquals("123", $value);
        $this->assertEquals("x", $name);
        return true;
      }),
    ]);
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertQueryParamsEqual(["x" => "123"], $result);
    $this->assertPathEquals("/x", $result);
  }

  public function testShouldFailFormatValidation() {
    $rule = new PathToQuery("/{x}", [
      "x" => new PathToQueryOption(function($value, $name) {
        $this->assertEquals("123", $value);
        $this->assertEquals("x", $name);
        return false;
      }),
    ]);
    $request = $this->makeRequest("/123");
    $result = $rule->rewrite($request);
    $this->assertSame($request, $result);
  }

  public function testShouldUseDefaultValueWhenValueIsEmpty() {
    $rule = new PathToQuery("/{x}", [
      "x" => new PathToQueryOption(null, null, "abc"),
    ]);
    $request = $this->makeRequest("/");
    $result = $rule->rewrite($request);
    $this->assertPathEquals("/x", $result);
    $this->assertQueryParamsEqual(["x" => "abc"], $result);
  }
}
