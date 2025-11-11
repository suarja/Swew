<?php

namespace App\Command;

use App\Entity\ApiToken;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:token:create',
    description: 'Create a CLI/API token for a user',
)]
final class CreateApiTokenCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('label', InputArgument::REQUIRED, 'Token label (e.g. "CLI on laptop")')
            ->addOption('expires-in', null, InputOption::VALUE_OPTIONAL, 'Relative expiry interval (e.g. "P30D")', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->findOneBy(['email' => mb_strtolower((string) $input->getArgument('email'))]);
        if (!$user) {
            $io->error('User not found.');

            return Command::FAILURE;
        }

        $rawToken = bin2hex(random_bytes(32));
        $token = (new ApiToken())
            ->setLabel((string) $input->getArgument('label'))
            ->setUser($user)
            ->setTokenHash(hash('sha256', $rawToken));

        $expiresIn = $input->getOption('expires-in');
        if (is_string($expiresIn) && $expiresIn !== '') {
            try {
                $token->setExpiresAt((new DateTimeImmutable())->add(new DateInterval($expiresIn)));
            } catch (\Exception $e) {
                $io->warning('Invalid expires-in interval, token will not expire automatically.');
            }
        }

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $io->success('Token created. Store it securely; it will not be shown again.');
        $io->writeln(sprintf('<info>%s</info>', $rawToken));

        return Command::SUCCESS;
    }
}
