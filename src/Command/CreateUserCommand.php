<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create an authenticated user for the SWE Wannabe platform',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email (also used as identifier)')
            ->addArgument('name', InputArgument::REQUIRED, 'Display name')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password to hash')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = mb_strtolower((string) $input->getArgument('email'));

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error(sprintf('A user with email "%s" already exists.', $email));

            return Command::FAILURE;
        }

        $user = (new User())
            ->setEmail($email)
            ->setName((string) $input->getArgument('name'));

        $hashedPassword = $this->passwordHasher->hashPassword($user, (string) $input->getArgument('password'));
        $user->setPassword($hashedPassword);

        $roles = [];
        if ($input->getOption('admin')) {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User "%s" created. You can now log in at /login.', $email));

        return Command::SUCCESS;
    }
}
