<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 06/04/17
 * Time: 23:56
 */

namespace App\Outputer;

use Symfony\Component\Console\Style\SymfonyStyle;


abstract class BaseOutputer
{
    protected $data;
    protected $io;
    protected $options;

    public function setIO(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function output()
    {
    }
}