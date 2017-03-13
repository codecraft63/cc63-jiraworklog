<?php

namespace App\Command;

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateReportCommand extends Command
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('report')

        // the short description shown while running "php bin/console list"
        ->setDescription('Creates a new monthly worklog report.')

        ->addArgument('month', InputArgument::REQUIRED, 'The month.')
        ->addArgument('year', InputArgument::REQUIRED, 'The year.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to create a report...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_date = $input->getArgument('year') . '-' . $input->getArgument('month') . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $jql = 'project = FIOP AND due >= '.$start_date.' AND due <= '.$end_date.' ORDER BY created';
        $output->writeln(['JQL: '.$jql, '']);

        try {
            $issueService = new IssueService();

            $ret = $issueService->search($jql);

            $total = 0;

            foreach ($ret->issues as $issue) {
                $worklogs = $issueService->getWorklog($issue->key)->getWorklogs();
                $issue->worklogs = $worklogs;
            }

            foreach ($ret->issues as $issue) {
                $output->writeln([
                    $issue->key . ': '. $issue->fields->summary,
                    '--------------------------------------------------------------'
                ]);
                
                foreach ($issue->worklogs as $wl) {
                    $output->writeln($wl->timeSpent . ' | '. $wl->comment);
                    $total += $wl->timeSpentSeconds;
                }
                $output->writeln('');
            }

            $output->writeln([
                '',
                '------------------------------------------------',
                'TOTAL: '. $this->secsToH($total),
                '------------------------------------------------'
            ]);
        } catch (JiraException $e) {
            throw  $e;
        }
    }

    /*
     * Convert seconds to human readable text.
     * Found at: http://csl.sublevel3.org/php-secs-to-human-text/
     *
     */
    protected function secsToH($secs)
    {
        $units = array(
            "hour"   =>      3600,
            "minute" =>        60,
            "second" =>         1,
            );
        // specifically handle zero
        if ( $secs == 0 ) return "0 seconds";
        $s = "";
        foreach ( $units as $name => $divisor ) {
            if ( $quot = intval($secs / $divisor) ) {
                $s .= "$quot $name";
                $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                $secs -= $quot * $divisor;
            }
        }
        return substr($s, 0, -2);
    }
}
