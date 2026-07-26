<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Fixture for the station's plan 047 ("run" commands) end-to-end
 * validation: a visible, long-running `concurrent: true` alias that reads
 * and writes the app's own shared DB (the `app_note` table, plan 020's
 * snapshot/restore fixture) so a coexisting app window's own DB access can
 * be observed to see its writes. Runs until killed (SIGTERM/SIGINT with no
 * handler installed is PHP's default terminate — exactly what the
 * launcher's `run <alias>`/coexistence-watchdog termination expects).
 */
#[AsCommand(name: 'app:run:mcp-serve', description: 'Plan 047 fixture: a concurrent-safe long-running command that ticks the shared DB.')]
final class RunMcpServeCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('mcp-serve: starting, ticking the shared DB every 2s (Ctrl-C / SIGTERM to stop)');

        while (true) {
            $id = $this->connection->fetchOne('SELECT COALESCE(MAX(id), 0) + 1 FROM app_note');
            $this->connection->insert('app_note', [
                'id' => $id,
                'body' => sprintf('mcp-serve marker #%d', $id),
                'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
            $io->writeln(sprintf('mcp-serve: inserted app_note id=%d', $id));
            sleep(2);
        }

        return Command::SUCCESS; // unreachable: the loop only ends by being killed
    }
}
