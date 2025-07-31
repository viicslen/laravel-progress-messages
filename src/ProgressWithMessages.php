<?php

namespace ViicSlen\ProgressWithMessages;

use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Progress;

/**
 * @template TSteps of iterable<mixed>|int
 */
class ProgressWithMessages extends Progress
{
    use Colors;

    /**
     * @var string[]
     */
    public array $messages = [];

    /**
     * Create a new ProgressBar instance.
     *
     * @param  TSteps  $steps
     */
    public function __construct(string $label, iterable|int $steps, string $hint = '')
    {
        parent::__construct($label, $steps, $hint);
        static::$themes['default'][static::class] = ProgressWithMessagesRenderer::class;
    }

    /**
     * Add a normal message to the progress view.
     */
    public function message(string $message, ?string $color = null, bool $render = true): static
    {
        if ($color && method_exists($this, $color)) {
            $message = $this->{$color}($message);
        }

        $this->messages[] = $message;

        if ($render) {
            $this->render();
        }

        return $this;
    }

    public function success(string $message, bool $render = true): static
    {
        return $this->message("✓ {$message}", 'green', $render);
    }

    public function info(string $message, bool $render = true): static
    {
        return $this->message("- {$message}", 'blue', $render);
    }

    public function warning(string $message, bool $render = true): static
    {
        return $this->message("⚠ {$message}", 'yellow', $render);
    }

    public function error(string $message, bool $render = true): static
    {
        return $this->message("⚠ {$message}", 'red', $render);
    }

    public function newLine(bool $render = true): static
    {
        return $this->message('', render: $render);
    }
}
