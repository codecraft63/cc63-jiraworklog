<?php

namespace App\Command;

use App\Outputer\StaticFactory as OutputerStaticFactory;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
        ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output type. Default is Console')
        ->addOption('template', 't', InputOption::VALUE_OPTIONAL, 'Template file')
        ->addOption('output-dir', 'd', InputOption::VALUE_OPTIONAL, 'Output directory')
        ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Message')

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

        #$jql = 'project = '.$project.' AND due >= '.$start_date.' AND due <= '.$end_date.' ORDER BY created';
        $jql = 'project = '.$project.' AND created >= '.$start_date.' AND created <= '.$end_date.' ORDER BY created';
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

            if (!$output_type = $input->getOption('output')) {
                $output_type = 'console';
            }

            $output = OutputerStaticFactory::factory($output_type);
            $output->setIO($io);
            $output->setData($ret);
            $output->setOptions($input->getOptions());
            $output->output();


        } catch (JiraException $e) {
            throw  $e;
        }
    }
}
