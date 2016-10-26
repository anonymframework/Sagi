<?php

namespace Sagi\Database\Console;


use Sagi\Cron\Cron;
use Sagi\Cron\Task\TaskReposity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrationCreateFormat
 * @package Sagi\Database\Console
 */
class ScheduleRunCommand extends Command
{

    protected function configure()
    {
        $this->setName('schedule:run')
            ->setDescription('Runs all scheduled commands');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schedule = new Cron();

        if(file_exists('schedule.php'))
        {
            include 'schedule.php';
        }

        $events = $schedule->dueEvents($schedule->getEvents());

        if (!count($events)) {
            $output->write('<error>There isnt any event from schedule</error>');

            return false;
        }

        foreach ($events as $event) {

            if ($event instanceof TaskReposity) {
                $output->writeln(sprintf('<info> %s Command is Runnig', $event->getSummaryForDescription().'</info>'));

                $event->execute();
            }
        }
    }


}