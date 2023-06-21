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

  public function setFormat(?callable $format): self {
    $this->format = $format;
    return $this;
  }

  public function setReplacement(?string $replacement): self {
    $this->replacement = $replacement;
    return $this;
  }

  public function setDefault(string $default): self {
    $this->default = $default;
    return $this;
  }

  public function shouldNotBeEmpty(): self {
    return $this->setFormat(function(string $value): bool {
      return $value !== "";
    });
  }

  public function shouldBePositiveInteger(): self {
    return $this->setFormat(function(string $value): bool {
      if ((string)intval($value) !== $value) {
        return false;
      }
      return 0 < intval($value);
    });
  }

  public function shouldMatchPattern(string $pattern): self {
    return $this->setFormat(function(string $value) use ($pattern): bool {
      return (bool)preg_match($pattern, $value);
    });
  }
}
