<?php
namespace Aptoma\Log;

use Silex\Application;

/**
 * ExtraContextProcessor adds provided extras to every record processed.
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class ExtraContextProcessor
{

    private $extras;

    public function __construct(array $extras)
    {
        $this->extras = $extras;
    }

    public function __invoke(array $record)
    {
        foreach ($this->extras as $key => $value) {
            $record['extra'][$key] = $value;
        }

        return $record;
    }
}
