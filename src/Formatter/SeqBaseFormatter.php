<?php

namespace Msschl\Monolog\Formatter;

use Monolog\DateTimeImmutable;
use Monolog\Formatter\JsonFormatter;
use Throwable;

/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
abstract class SeqBaseFormatter extends JsonFormatter
{

    /**
     * The log level map.
     * Maps the monolog log levels to the seq log levels.
     *
     * @var array
     */
    protected array $logLevelMap = [
        '100' => 'Debug',
        '200' => 'Information',
        '250' => 'Information',
        '300' => 'Warning',
        '400' => 'Error',
        '500' => 'Error',
        '550' => 'Fatal',
        '600' => 'Fatal',
    ];

    /**
     * Initializes a new instance of the {@see SeqBaseFormatter} class.
     *
     * @param int $batchMode The json batch mode.
     */
    function __construct($batchMode)
    {
        $this->appendNewline = false;
        $this->batchMode     = $batchMode;
    }

    /**
     * Returns a string with the content type for the seq-formatter.
     *
     * @return string
     */
    public abstract function getContentType(): string;

    /**
     * Normalizes the log record array.
     *
     * @param array $data The log record to normalize.
     * @param int   $depth
     *
     * @return array
     */
    protected function normalize($data, int $depth = 0): array
    {
        if (!is_array($data) && !$data instanceof \Traversable) {
            /* istanbul ignore next */
            throw new \InvalidArgumentException(
                'Array/Traversable expected, got '.gettype($data).' / '.get_class($data)
            );
        }

        $normalized = [];

        foreach ($data as $key => $value) {
            $key = SeqBaseFormatter::ConvertSnakeCaseToPascalCase($key);

            $this->{'process'.$key}($normalized, $value);
        }

        return $normalized;
    }

    /**
     * Processes the log message.
     *
     * @param array  &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param string  $message    The log message.
     *
     * @return void
     */
    protected abstract function processMessage(array &$normalized, string $message);

    /**
     * Processes the context array.
     *
     * @param array &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param array  $context    The context array.
     *
     * @return void
     */
    protected abstract function processContext(array &$normalized, array $context);

    /**
     * Processes the log level.
     *
     * @param array &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param int    $level      The log level.
     *
     * @return void
     */
    protected abstract function processLevel(array &$normalized, int $level);

    /**
     * Processes the log level name.
     *
     * @param array  &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param string  $levelName  The log level name.
     *
     * @return void
     */
    protected abstract function processLevelName(array &$normalized, string $levelName);

    /**
     * Processes the channel name.
     *
     * @param array  &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param string  $name       The log channel name.
     *
     * @return void
     */
    protected abstract function processChannel(array &$normalized, string $name);

    /**
     * Processes the log timestamp.
     *
     * @param array    &        $normalized Reference to the normalized array, where all normalized data get stored.
     * @param DateTimeImmutable $datetime   The log timestamp.
     *
     * @return void
     */
    protected abstract function processDatetime(array &$normalized, DateTimeImmutable $datetime);

    /**
     * Processes the extras array.
     *
     * @param array &$normalized Reference to the normalized array, where all normalized data get stored.
     * @param array  $extras     The extras array.
     *
     * @return void
     */
    protected abstract function processExtra(array &$normalized, array $extras);

    /**
     * Extracts the exception from an array.
     *
     * @param array  &$array The array.
     *
     * @return Throwable|null
     */
    protected function extractException(array &$array): ?Throwable
    {
        $exception = $array['exception'] ?? null;

        if ($exception === null) {
            return null;
        }

        unset($array['exception']);

        if (!($exception instanceof Throwable)) {
            return null;
        }

        return $exception;
    }

    /**
     * Converts a snake case string to a pascal case string.
     *
     * @param string|null $value The string to convert.
     *
     * @return string
     */
    protected static function ConvertSnakeCaseToPascalCase(string $value = null): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}
