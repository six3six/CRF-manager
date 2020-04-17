<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    private $em;
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    public function __construct(EntityManagerInterface $em, EncoderFactoryInterface $encoderFactory)
    {
        parent::__construct();
        $this->encoderFactory = $encoderFactory;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a new user')
            //->addArgument('username', InputArgument::OPTIONAL, 'Username')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $username = $io->ask("Username");
        if(!$username) {
            $io->error("Invalid username");
            return 1;
        }

        $userRepo = $this->em->getRepository("App:User");
        if($userRepo->isExistByUsername($username)) {
            $io->error("User already registered");
            return 1;
        }

        $password = $io->askHidden("Password");
        if($password != $io->askHidden("Confirm password")) {
            $io->error("Password doesn't match");
            return 1;
        }
        $encoder = $this->encoderFactory->getEncoder("App\Entity\User");
        $encodedPassword = $encoder->encodePassword($password, base64_encode(random_bytes(30)));

        $name = $io->ask("Name");
        $surname = $io->ask("Surname");
        $email = $io->ask("Email");

        $isAdmin_text = $io->choice("Is a admin", ["Yes", "No"], "No");
        $isAdmin = $isAdmin_text == "Yes" ? true : false;



        $userE = new User();
        $userE->setUsername($username);
        $userE->setPassword($encodedPassword);
        $userE->setFirstName($name);
        $userE->setLastname($surname);
        $userE->setEmail($email);
        if($isAdmin) {
            $userE->setRoles(["ROLE_ADMIN"]);
        }

        $this->em->persist($userE);
        $this->em->flush();

        $io->success('User successfully created');

        return 0;
    }
}
