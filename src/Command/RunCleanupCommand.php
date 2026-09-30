<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Fixture for the standalone `run` check (release checklist E1): a quick,
 * `concurrent: false` (standalone-only) alias —
 * sweeps the markers RunMcpServeCommand leaves in `app_note` and exits
 * immediately, modelling a one-shot maintenance task that must not run
 * while a window (and thus a possibly-concurrent mcp-serve) is live.
 */
#[AsCommand(name: 'app:run:cleanup', description: 'Standalone `run` fixture: sweeps the mcp-serve markers.')]
final class RunCleanupCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $deleted = $this->connection->executeStatement(
            "DELETE FROM app_note WHERE body LIKE 'mcp-serve marker%'"
        );
        $io->writeln(sprintf('cleanup: removed %d marker(s) from app_note', $deleted));

        return Command::SUCCESS;
    }
}
