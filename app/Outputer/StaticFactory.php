<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 06/04/17
 * Time: 19:48
 */

namespace App\Outputer;


class StaticFactory
{
    /**
     * @param string $type
     *
     * @return OutputerInterface
     */
    public static function factory(string $type): OutputerInterface
    {
        switch ($type) {
            case 'console':
                return new ConsoleOutputer;
            case 'html':
                return new HtmlOutputer;
        }

        throw new \InvalidArgumentException('Unknown format given');
    }
}