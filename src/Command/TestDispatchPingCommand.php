<?php

namespace App\Command;

use App\Message\PingMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

/**
 * Fixture for the declared workers' priority ordering (CONTRACT.md §2:
 * a transport list's order is its priority): dispatches a
 * PingMessage onto an explicitly named transport, overriding
 * messenger.yaml's routing via TransportNamesStamp. Lets the priority
 * ordering claim ("a message dispatched to the first transport handled
 * before one already waiting in the last") be demonstrated from the CLI,
 * without adding a routing rule per transport.
 */
#[AsCommand(name: 'app:test:dispatch-ping', description: 'Dispatch a PingMessage onto a named transport.')]
final class TestDispatchPingCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('transport', InputArgument::REQUIRED, 'Transport name to dispatch onto')
            ->addArgument('text', InputArgument::REQUIRED, 'Correlation text carried by the message');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $transport = $input->getArgument('transport');
        $text = $input->getArgument('text');

        $this->bus->dispatch(new Envelope(
            new PingMessage(uniqid('dispatch-', true), $text),
            [new TransportNamesStamp([$transport])],
        ));

        $io->writeln(sprintf('dispatched "%s" onto transport "%s"', $text, $transport));

        return Command::SUCCESS;
    }
}
