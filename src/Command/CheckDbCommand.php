<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:check-db')]
class CheckDbCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $user = $this->connection->fetchOne('SELECT current_user');
            $db = $this->connection->fetchOne('SELECT current_database()');
            $output->writeln("<info>✔ Подключение к базе прошло успешно!</info>");
            $output->writeln("👤 Пользователь: <comment>$user</comment>");
            $output->writeln("🗃 База данных: <comment>$db</comment>");
        } catch (\Throwable $e) {
            $output->writeln("<error>❌ Ошибка подключения к базе данных:</error>");
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
