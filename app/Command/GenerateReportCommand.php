<?php

namespace App\Command;

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Worklog report generator command.
 */
class GenerateReportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console").
        ->setName('report')

        // the short description shown while running "php bin/console list".
        ->setDescription('Creates a new monthly worklog report.')

        ->addArgument('project', InputArgument::REQUIRED, 'The Project Key.')
        ->addArgument('month', InputArgument::REQUIRED, 'The month.')
        ->addArgument('year', InputArgument::REQUIRED, 'The year.')

        // the full command description shown when running the command with.
        // the "--help" option.
        ->setHelp('This command allows you to create a report...');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $project = $input->getArgument('project');
        $start_date = $input->getArgument('year') . '-' . $input->getArgument('month') . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $jql = 'project = '.$project.' AND due >= '.$start_date.' AND due <= '.$end_date.' ORDER BY created';
        $io->title('JQL: '.$jql);
        $io->newLine();


        try {
            $issueService = new IssueService();
            $io->progressStart(100);

            $ret = $issueService->search($jql);
            $issues_step = 100 / (count($ret->issues) + 1);

            $total = 0;

            foreach ($ret->issues as $issue) {
                $io->progressAdvance($issues_step);
                $worklogs = $issueService->getWorklog($issue->key)->getWorklogs();
                $issue->worklogs = $worklogs;
            }

            $io->progressFinish();
            $io->newLine(1);

            $data = [];
            foreach ($ret->issues as $issue) {
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

            $io->table(['Issue', 'Description', 'Time Spent'], $data);

            $output->writeln([
                '',
                '------------------------------------------------',
                'TOTAL: '. round($total / 3600, 2) . ' hours.',
                '------------------------------------------------'
            ]);
        } catch (JiraException $e) {
            throw  $e;
        }
    }
}
