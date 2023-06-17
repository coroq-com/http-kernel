<?php
declare(strict_types=1);
namespace Coroq\HttpKernel\Basic\BasicRequestRewriterRule;

class PathToQueryOption {
  /** @var callable|null */
  public $format;

  /** @var string|null */
  public $replacement;

  /** @var string */
  public $default;

  /**
   * @param null|callable $format 
   * @param null|string $replacement 
   * @param string $default 
   */
  public function __construct(?callable $format = null, ?string $replacement = null, string $default = '') {
    $this->replacement = $replacement;
    $this->format = $format;
    $this->default = $default;
  }
}
