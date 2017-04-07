<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 06/04/17
 * Time: 19:49
 */

namespace app\Outputer;

use Symfony\Component\Console\Style\SymfonyStyle;


interface OutputerInterface
{
    public function setIO(SymfonyStyle $io);

    public function setData($data);

    public function output();

    public function setOptions(array $options);
}