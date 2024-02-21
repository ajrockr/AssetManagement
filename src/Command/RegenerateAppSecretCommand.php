<?php

namespace App\Command;

use sixlive\DotenvEditor\DotenvEditor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'secret:regenerate-app-secret',
    description: 'Regenerate a random value and update APP_SECRET',
)]
class RegenerateAppSecretCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('envfile', InputArgument::REQUIRED, 'env File {.env, .env.local}')
        ;
    }

    // https://stackoverflow.com/questions/60837428/symfony-terminal-command-to-generate-a-new-app-secret
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $envname = $input->getArgument('envfile');

        if ($envname && ($envname == '.env' || $envname == '.env.local')) {
            $io->note(sprintf('You chose to update: %s', $envname));
            $secret = bin2hex(random_bytes(16));
            $filepath = realpath(dirname(__FILE__).'/../..') . '/' . $envname;
            $io->note(sprintf('Editing file: %s', $filepath));

            $editor = new DotenvEditor();
            $editor->load($filepath);
            $editor->set('APP_SECRET', $secret);
            $editor->save();
            $io->success(sprintf('New APP_SECRET was generated: %s', $secret));
            return Command::SUCCESS;
        }

        $io->error('You did not provide a valid environment file to change');
        return Command::FAILURE;
    }
}
