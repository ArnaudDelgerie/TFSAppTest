<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Fixture for the station's plan 061 (process-group teardown) validation: a
 * long-running command that itself spawns a child process (a plain `sleep`,
 * detached from PHP's own signal handling) and then blocks forever — proving
 * a launcher's teardown reaches a whole descendant tree, not just the one PHP
 * pid it recorded. `concurrent: false` (standalone-only): nothing about this
 * fixture needs to run alongside a live window.
 */
#[AsCommand(name: 'app:run:spawn-child', description: 'Plan 061 fixture: spawns a grandchild process and blocks forever.')]
final class RunSpawnChildCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $child = new Process(['sleep', '300']);
        $child->start();
        $io->writeln(sprintf('spawn-child: started grandchild pid=%d, blocking forever', $child->getPid()));

        while (true) {
            sleep(2);
        }

        return Command::SUCCESS; // unreachable: the loop only ends by being killed
    }
}
