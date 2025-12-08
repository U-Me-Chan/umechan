<?php

namespace PK\Boards\Console;

use PK\Boards\Board;
use PK\Boards\BoardStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateBoard extends Command
{
    public function __construct(
        private BoardStorage $board_storage
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('boards:create')
            ->setDescription('Создаёт новый раздел')
            ->addArgument('tag', InputArgument::REQUIRED, 'Тег раздела')
            ->addArgument('name', InputArgument::REQUIRED, 'Имя раздела');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->board_storage->save(Board::draft(
                $input->getArgument('tag'),
                $input->getArgument('name')
            ));

            $io->success('Создан!');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }
    }
}
