<?php
namespace Coroq\HttpKernel\Basic;

use Coroq\HttpKernel\Component\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Router implements RouterInterface {
  /** @var array<mixed> */
  private $map;

  /** @var ?LoggerInterface */
  private $logger;

  /**
   * @param array<mixed> $map
   */
  public function __construct(array $map) {
    $this->map = $map;
  }

  public function setLogger(?LoggerInterface $logger): void {
    $this->logger = $logger;
  }

  /**
   * @return array<int,mixed>
   */
  public function route(ServerRequestInterface $request, string $basePath = "/"): array {
    $waypoints = $this->getWaypoints($request, $basePath);
    $this->logDebug("waypoints", $waypoints);
    return $this->routeHelper([], $waypoints, $this->map, "");
  }

  /**
   * @return array<int,mixed>
   */
  private function getWaypoints(ServerRequestInterface $request, string $basePath): array {
    $basePath = ltrim($basePath, "/");
    $path = $request->getUri()->getPath();
    $path = ltrim($path, "/");
    if ($basePath != "" && strpos($path, $basePath) !== 0) {
      throw new RuntimeException(); // TODO
    }
    $path = substr($path, strlen($basePath));
    $waypoints = array_diff(explode("/", $path), [""]);
    return $waypoints;
  }

  /**
   * @param array<int,mixed> $route
   * @param array<int,string> $waypoints
   * @param array<mixed> $map
   * @return array<int,mixed> empty if no route found.
   */
  private function routeHelper(array $route, array $waypoints, array $map, string $defaultClassName): array {
    $currentWaypoint = array_shift($waypoints);
    if ($currentWaypoint === null) {
      $currentWaypoint = "";
    }
    foreach ($map as $mapIndex => $mapItem) {
      if (is_int($mapIndex)) {
        if ($mapItem == "*") {
          $this->logDebug("Matched catch-all (*)");
          return $route;
        }
        if (is_string($mapItem) && preg_match('#(.+)::$#', $mapItem, $matches)) {
          $defaultClassName = $matches[1];
          continue;
        }
        $route[] = $this->resolveDefaultClassName($defaultClassName, $mapItem);
        continue;
      }
      if ($this->doesWaypointMatchToMapIndex($currentWaypoint, $mapIndex)) {
        if (is_array($mapItem)) {
          $foundRoute = $this->routeHelper($route, $waypoints, $mapItem, $defaultClassName);
          if (!$foundRoute) {
            continue;
          }
          return $foundRoute;
        }
        if ($waypoints) {
          return [];
        }
        if (is_string($mapItem) && preg_match('#::$#', $mapItem)) {
          $mapItem .= $mapIndex;
        }
        $route[] = $this->resolveDefaultClassName($defaultClassName, $mapItem);
        return $route;
      }
    }
    return [];
  }

  private function doesWaypointMatchToMapIndex(string $waypoint, string $mapIndex): bool {
    // if $map_index is a regular expression
    if (preg_match("|^[/#]|", "$mapIndex") && preg_match($mapIndex, $waypoint)) {
      return true;
    }
    return $mapIndex === $waypoint;
  }

  /**
   * @param string $defaultClassName
   * @param mixed $mapItem
   * @return mixed
   */
  protected function resolveDefaultClassName(string $defaultClassName, $mapItem) {
    if (is_string($mapItem)) {
      if (substr($mapItem, 0, 2) == "::") {
        $mapItem = "$defaultClassName$mapItem";
      }
    }
    return $mapItem;
  }

  /**
   * @param array<mixed> $context
   */
  private function logDebug(string $message, array $context = []): void {
    if ($this->logger) {
      $this->logger->debug("Router: $message", $context);
    }
  }
}
