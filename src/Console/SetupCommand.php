<?php

namespace App\Console;

use Exception;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phinx\Console\PhinxApplication;

final class SetupCommand extends Command
{
    private bool $isFresh;
    private string $dbHost;
    private string $dbPort;
    private string $dbNameDev;
    private string $dbNameTest;
    private string $dbNameProd;    
    private string $dbUsername;
    private string $dbPassword;
    private array $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    protected function configure(): void
    {
        parent::configure();

        $this->setName('setup');
        $this->setDescription('Configuration and database installation');
        $this->isFresh = $this->askBoolean('is a Fresh instalation?', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->setDbParameters();
            
            if ($this->isFresh) {
                $this->deleteDatabases();
                $this->deleteDbUser();
            }            

            if (!file_exists(getcwd() . '/.env')) {
                $output->writeln('The file .env not exists, coping');
                copy('.env.example', '.env');
            }

            $this->setDbParameters();
            $this->createDbUser($output);
            $this->setupDatabase($output);
            
            copy('./config/env.example.php', './config/env.php');
            
            $this->runMigrations($output);
            $output->writeln('<info>Setup was successfully!</info>');
            $output->writeln('To start all tests, run: composer test');

            return 0;
        } catch (Exception $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return 1;
        }
    }

    private function askBoolean(string $question, bool $default): bool
    {
        $defaultText = $default ? 'Y' : 'N';
        $input = readline(sprintf('%s [%s]:', $question, $defaultText)) ?: $defaultText;
        return strtolower(trim($input)) === 'y';
    }

    private function setDbParameters(): void
    {
        $this->dbHost = $_ENV['DB_HOST'];
        $this->dbPort = $_ENV['DB_PORT'];
        $this->dbNameDev = $_ENV['DB_DEVELOPMENT'];
        $this->dbNameTest = $_ENV['DB_TEST'];
        $this->dbUsername = $_ENV['DB_USERNAME'];
        $this->dbPassword = $_ENV['DB_PASSWORD'];
    }   
    
    private function connectToDatabase($username = null, $password = null): PDO
    {
        $dbUsername = $username ?? $this->dbUsername;
        $dbPassword = $password ?? $this->dbPassword;

        return new PDO(
            "mysql:host=$this->dbHost;port=$this->dbPort;charset=utf8mb4",
            $dbUsername,
            $dbPassword,
            $this->pdoOptions
        );
    }    

    private function createDbUser(OutputInterface $output): void
    {
        $pdo = $this->connectToDatabase('root', $_ENV['DB_ROOT_PASSWORD']);

        $stmt = $pdo->prepare("SELECT user FROM mysql.user WHERE user = :username");
        $stmt->bindParam(':username', $this->dbUsername, PDO::PARAM_STR);
        $stmt->execute();
            
        if ($stmt->rowCount() == 0) {
            $output->writeln('User does not exist.');

            $stmt = $pdo->prepare("CREATE USER :username@'%' IDENTIFIED BY :password");
            $stmt->bindParam(':username', $this->dbUsername, PDO::PARAM_STR);            
            $stmt->bindParam(':password', $this->dbPassword, PDO::PARAM_STR); 
            $stmt->execute();

            $stmt = $pdo->prepare("GRANT ALL PRIVILEGES ON *.* TO :username@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;");
            $stmt->bindParam(':username', $this->dbUsername, PDO::PARAM_STR);              
            $stmt->execute();

            $stmt = $pdo->prepare("GRANT ALL PRIVILEGES ON `:username\_%`.* TO :username@'%'; FLUSH PRIVILEGES;");
            $stmt->bindParam(':username', $this->dbUsername, PDO::PARAM_STR);              
            $stmt->execute();            
        }      
    }

    private function deleteDbUser(): void
    {
        $pdo = $this->connectToDatabase('root', $_ENV['DB_ROOT_PASSWORD']);

        $stmt = $pdo->prepare("SELECT user as user, host as host FROM mysql.user WHERE user = :username");
        $stmt->bindParam(':username', $this->dbUsername, PDO::PARAM_STR);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $del = $pdo->prepare("DELETE FROM mysql.user WHERE host = :host AND user = :user");
            $del->bindParam(':host', $row['host'], PDO::PARAM_STR);
            $del->bindParam(':user', $row['user'], PDO::PARAM_STR);
            $del->execute();    
            
            $del = $pdo->prepare("DROP USER :user");
            $del->bindParam(':user', $row['user'], PDO::PARAM_STR);
            $del->execute();              
        }      
        
        $pdo->exec("FLUSH PRIVILEGES");
    }    

    private function createDatabase(PDO $pdo, string $database): void
    {
        $pdo->exec($this->createTableSql($database));
    }

    private function createTableSql(string $table): string
    {
        return sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;', $table);
    }    

    private function deleteDatabases(): void
    {
        $dbs = $this->dbUsername . '_%';
        $pdo = $this->connectToDatabase('root', $_ENV['DB_ROOT_PASSWORD']);
        
        $stmt = $pdo->prepare("SHOW DATABASES LIKE :username");
        $stmt->bindParam(':username', $dbs, PDO::PARAM_STR);            
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            // Process $databaseName here
            $pdo->exec("DROP DATABASE $row[0];");
        }
    }

    private function existsDatabase(PDO $pdo, string $database): bool
    {
        $statement = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :database');
        $statement->bindValue('database', $database);
        $statement->execute();

        return !empty($statement->fetch());
    }

    private function setupDatabase(OutputInterface $output): void
    {
        $output->writeln('Connect to database server');
        $pdo = $this->connectToDatabase('root', $_ENV['DB_ROOT_PASSWORD']);

        $output->writeln('Create TEST database');
        if ($this->existsDatabase($pdo, $this->dbNameTest)) {
            $output->writeln('<info>TEST database already exists</info>');
        } else {
            $this->createDatabase($pdo, $this->dbNameTest);
            $output->writeln('<info>TEST database created successfully</info>');
        }
        

        $output->writeln('Create DEV database');
        if ($this->existsDatabase($pdo, $this->dbNameDev)) {
            $output->writeln('<info>DEV database already exists</info>');            
        } else {
            $this->createDatabase($pdo, $this->dbNameDev);
            $output->writeln('<info>DEV database created successfully</info>');
        }
    }

    private function runMigrations(OutputInterface $output): void
    {
        $output->writeln('Running migrations!');
        $phinx = new PhinxApplication();
        $command = $phinx->find('migrate');
    
        $arguments = [
            'command'         => 'migrate',
            '--environment'   => 'development',
            '--configuration' => './phinx.yaml'
        ];
    
        $input = new ArrayInput($arguments);
        $command->run(new ArrayInput($arguments), $output);
    }
}
