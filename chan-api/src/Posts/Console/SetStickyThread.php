<?php

namespace PK\Posts\Console;

use InvalidArgumentException;
use PK\Posts\Services\PostFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SetStickyThread extends Command
{
    public function __construct(
        private PostFacade $post_facade
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('posts:set-sticky-thread')
            ->setDescription('Устанавливает флаг прилипчивой нити')
            ->addArgument('thread_id', InputArgument::REQUIRED, 'Идентификатор треда');
    }

    public function execute(InputInterface $input_interface, OutputInterface $output_interface)
    {
        $io = new SymfonyStyle($input_interface, $output_interface);

        try {
            $this->post_facade->setStickyFlagStateToThread($input_interface->getArgument('thread_id'), true);

            $io->success('Установлен!');

            return Command::SUCCESS;
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }
    }
}
