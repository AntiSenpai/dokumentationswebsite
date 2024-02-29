<?php

namespace App\Command;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.'
)]
class CreateUserCommand extends Command {
    protected static $defaultName = 'app:create-user';
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure() {
        $this
            ->setDescription('Creates a new user.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user.')
            ->addArgument('roles', InputArgument::IS_ARRAY, 'The role of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
       $io = new SymfonyStyle($input, $output);

       $username = $input->getArgument('username');
       $email = $input->getArgument('email');
       $password = $input->getArgument('password');
       $roles = $input->getArgument('roles');

       $user = new User();
       $user->setUsername($username);
       $user->setEmail($email);
       $user->setRoles($roles);
       $user->setIsActive(true);
       $user->setIsVerified(false);
       
       $now = new \DateTime();
       $timer = $now->format('Y-m-d');
       $user->setTimer($timer);

       $confirmationToken = bin2hex(random_bytes(32));
         $user->setConfirmationToken($confirmationToken);

       $encodedPassword = $this->passwordEncoder->hashPassword($user, $password);
        $user->setPassword($encodedPassword);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Benutzer %s wurde angelegt.', $username));

        return Command::SUCCESS;
    }
}