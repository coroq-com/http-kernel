<?php
use Coroq\HttpKernel\Basic\BasicRouter;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers Coroq\HttpKernel\Basic\Router
 */
class RouterTest extends TestCase {
  private function makeRequest(string $path): ServerRequestInterface {
    return new ServerRequest("get", $path);
  }

  public function testRoot() {
    $router = new BasicRouter(["" => "root"]);
    $result = $router->route($this->makeRequest(""));
    $this->assertEquals(["root"], $result);
  }

  public function testNamedMapItem() {
    $router = new BasicRouter(["" => "root", "abc" => "ABC"]);
    $result = $router->route($this->makeRequest("/abc"));
    $this->assertEquals(["ABC"], $result);
  }

  public function testNumericMapIndex() {
    $router = new BasicRouter(["first", "" => "root", "last"]);
    $result = $router->route($this->makeRequest("/"));
    $this->assertEquals(["first", "root"], $result);
  }

  public function testCatchAllOnly() {
    $router = new BasicRouter(["*"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterSomeRoute() {
    $router = new BasicRouter(["first", "*"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals(["first"], $result);
  }

  public function testCatchAllAfterDigging() {
    $router = new BasicRouter([
      "first" => [
        "*",
      ],
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterDiggingAndSomeRoute() {
    $router = new BasicRouter([
      "first" => [
        "second",
        "*",
      ],
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals(["second"], $result);
  }

  public function testCatchAllAfterDiggingAndDeadEnd() {
    $router = new BasicRouter([
      "first" => [
        "second",
      ],
      "*",
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals([], $result);
  }

  public function testCatchAllAfterDiggingAndDeadEndAndSomeRoute() {
    $router = new BasicRouter([
      "first" => [
        "second",
      ],
      "third",
      "*",
    ]);
    $result = $router->route($this->makeRequest("/first"));
    $this->assertEquals(["third"], $result);
  }

  public function testDeadEnd() {
    $router = new BasicRouter(["first", "" => "root", "last"]);
    $result = $router->route($this->makeRequest("/not_exists"));
    $this->assertEquals([], $result);
  }

  public function testDeepMap() {
    $router = new BasicRouter([
      "1st",
      "2nd",
      "flower" => [
        "of" => "out",
      ],
      "leaf" => [
        "3rd",
        "of" => [
          "4th",
          "the" => [
            "5th",
            "tall" => [
              "6th",
              "tree" => "last",
              "out",
            ],
            "small" => [
              "tree" => "out",
            ],
          ],
        ],
      ],
      "out",
    ]);
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"));
    $this->assertEquals(["1st", "2nd", "3rd", "4th", "5th", "6th", "last"], $result);
  }

  public function testDeepMapDeadEnd() {
    $router = new BasicRouter([
      "1st",
      "2nd",
      "flower" => [
        "of" => "out",
      ],
      "leaf" => [
        "3rd",
        "of" => [
          "4th",
          "the" => [
            "5th",
            "tall" => [
              "6th",
              "wood" => "last",
              "out",
            ],
            "small" => [
              "tree" => "out",
            ],
          ],
        ],
      ],
      "out",
    ]);
    $result = $router->route($this->makeRequest("leaf/of/the/tall/tree"));
    $this->assertEquals([], $result);
  }

  public function testDefaultClassName() {
    $router = new BasicRouter([
      "::no_class_name",
      "abc" => [
        "SomeClass::",
        "::method1",
        "def" => "::method2",
      ],
    ]);
    $result = $router->route($this->makeRequest("/abc/def"));
    $this->assertEquals(["::no_class_name", "SomeClass::method1", "SomeClass::method2"], $result);
  }

  public function testDefaultMethodName() {
    $router = new BasicRouter([
      "::",
      "abc" => [
        "SomeClass::",
        "::",
        "def" => [
          "SomeClass::",
          "ghi" => "::"
        ],
      ],
    ]);
    $result = $router->route($this->makeRequest("/abc/def/ghi"));
    $this->assertEquals(["::", "SomeClass::", "SomeClass::ghi"], $result);
  }
}
