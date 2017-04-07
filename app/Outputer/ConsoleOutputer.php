<?php
/**
 * Created by PhpStorm.
 * User: pedro
 * Date: 06/04/17
 * Time: 19:56
 */

namespace App\Outputer;


class ConsoleOutputer extends BaseOutputer implements OutputerInterface
{
    public function output() {
        $this->io->newLine(1);

        $total = 0;
        $data = [];
        foreach ($this->data->issues as $issue) {
            $data[] = [
                '<comment>'.$issue->key.'</comment>',
                '<comment>'.$issue->fields->summary.'</comment>',
                ''
            ];

            foreach ($issue->worklogs as $wl) {
                $comment = substr(preg_replace('/\n\r?/', ' || ', trim($wl->comment)), 0, 60);
                $data[] = ['', $comment, utf8_decode($wl->timeSpent)];
                $total += $wl->timeSpentSeconds;
            }
        }

        $this->io->table(['Issue', 'Description', 'Time Spent'], $data);

        $this->io->writeln([
            '',
            '------------------------------------------------',
            'TOTAL: '. round($total / 3600, 2) . ' hours.',
            '------------------------------------------------'
        ]);
    }
}