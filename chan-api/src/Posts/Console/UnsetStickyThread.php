<?php

namespace PK\Posts\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use PK\Posts\Exceptions\NotIsThreadException;
use PK\Posts\Services\PostFacade;

final class UnsetStickyThread extends Command
{
    public function __construct(
        private PostFacade $post_facade
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('posts:unset-sticky-thread')
            ->setDescription('Снимает флаг прилипчивой нити')
            ->addArgument('thread_id', InputArgument::REQUIRED, 'Идентификатор треда');
    }

    public function execute(InputInterface $input_interface, OutputInterface $output_interface)
    {
        $io = new SymfonyStyle($input_interface, $output_interface);

        try {
            $this->post_facade->setStickyFlagStateToThread($input_interface->getArgument('thread_id'), false);

            $io->success('Снят!');

            return Command::SUCCESS;
        } catch (NotIsThreadException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }
    }
}
