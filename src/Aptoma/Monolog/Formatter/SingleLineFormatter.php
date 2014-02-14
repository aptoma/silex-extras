<?php

namespace Aptoma\Monolog\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * SingleLineFormatter extends LineFormatter and makes sure each log entry is always exactly on line.
 *
 * This is mostly useful when you log exceptions where the exception message has inline line breaks,
 * like Guzzle's BadResponseException.
 */
class SingleLineFormatter extends LineFormatter
{
    public function format(array $record)
    {
        $record = parent::format($record);

        return str_replace(PHP_EOL, ' ', $record) . PHP_EOL;
    }
}
