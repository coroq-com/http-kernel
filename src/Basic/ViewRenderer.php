<?php
namespace Coroq\HttpKernel\Basic;

use InvalidArgumentException;

class ViewRenderer {
  /** @var string */
  protected $template_directory;

  public function __construct(string $template_directory) {
    $this->template_directory = realpath($template_directory);
    if ($this->template_directory === false) {
      throw new InvalidArgumentException("Template directory $template_directory not found.");
    }
  }

  public function render(string $__template_name, array $__arguments = []): string {
    $template_file = "$this->template_directory/$__template_name";
    if (!is_readable($template_file)) {
      throw new InvalidArgumentException("Template file $__template_name in $this->template_directory is not readable.");
    }
    try {
      ob_start();
      extract($__arguments);
      include $template_file;
      return ob_get_clean();
    }
    catch (\Throwable $error) {
      ob_end_clean();
      throw $error;
    }
  }

  public function __invoke(string $template_name, array $arguments = []): string {
    return $this->render($template_name, $arguments);
  }

  protected function include(string $template_name, array $arguments = []): void {
    echo $this->render($template_name, $arguments);
  }
}
