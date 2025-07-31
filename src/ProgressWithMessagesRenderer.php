<?php

namespace ViicSlen\ProgressWithMessages;

use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Renderer;

class ProgressWithMessagesRenderer extends Renderer
{
    use DrawsBoxes;

    /**
     * The character to use for the progress bar.
     */
    protected string $barCharacter = '█';

    /**
     * Render the progress bar.
     *
     * @param  ProgressWithMessages<int|array>  $progress
     */
    public function __invoke(ProgressWithMessages $progress): string
    {
        $filled = str_repeat($this->barCharacter, (int) ceil($progress->percentage() * min($this->minWidth, $progress->terminal()->cols() - 6)));

        $renderer = $this->when(
            ! empty($progress->messages),
            function () use ($progress) {
                $messages = ! in_array($progress->state, ['submit', 'cancel', 'error'])
                    ? array_slice($progress->messages, -$progress->terminal()->lines())
                    : $progress->messages;

                foreach ($messages as $message) {
                    if ($progress->state === 'submit') {
                        $message = $this->dim($message);
                    }

                    $this->message($message);
                }

                $this->newLine();
            },
        );

        return match ($progress->state) {
            'submit' => $renderer->box(
                $this->dim($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                $this->dim($filled),
                info: $progress->progress . '/' . $progress->total,
            ),

            'error' => $renderer
                ->box(
                    $this->truncate($progress->label, $progress->terminal()->cols() - 6),
                    $this->dim($filled),
                    color: 'red',
                    info: $progress->progress . '/' . $progress->total,
                ),

            'cancel' => $renderer
                ->box(
                    $this->truncate($progress->label, $progress->terminal()->cols() - 6),
                    $this->dim($filled),
                    color: 'red',
                    info: $progress->progress . '/' . $progress->total,
                )
                ->error($progress->cancelMessage),

            default => $renderer
                ->box(
                    $this->cyan($this->truncate($progress->label, $progress->terminal()->cols() - 6)),
                    $this->dim($filled),
                    info: $progress->progress . '/' . $progress->total,
                )
                ->when(
                    $progress->hint,
                    fn () => $this->hint($progress->hint),
                    fn () => $this->newLine(), // Space for errors
                )
        };
    }

    /**
     * Render a message.
     */
    protected function message(string $message): self
    {
        $message = $this->mbWordwrap($message, $this->prompt->terminal()->cols() - 6);

        return $this->line("  {$message}");
    }
}
