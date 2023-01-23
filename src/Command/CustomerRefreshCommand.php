<?php

namespace App\Command;

use App\Entity\Customer;
use App\Http\ProfitApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Psr\Log\LoggerInterface;

class CustomerRefreshCommand extends Command
{
    protected static $defaultName = 'app:customer-refresh';
    protected static $defaultDescription = 'Refresh Customer Data';

    /**
     *@var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     *@var ProfitApiClient
     */
    private ProfitApiClient $profitApiClient;
    /**
     *@var serializer
     */
    private SerializerInterface $serializer;
    /**
     *@var customerQuery
     */
    private $customerQuery = 'Customers';
    /**
     *@var tenant
     */
    private $tenant = '?Tenant=sm6apxdkrh';
    /**
     *@var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, ProfitApiClient $profitApiClient, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->profitApiClient = $profitApiClient;
        $this->serializer = $serializer;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Customers', InputArgument::REQUIRED, 'Customers')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $customerProfile = $this->profitApiClient->fetchCustomerProfile($input->getArgument('Customers'), $input->getArgument('Tenant'));

            //dump($customerProfile);
            //dump($customerProfile->getStatusCode());
            //dump($customerProfile->getContent());
            echo "Back in Command";

            if($customerProfile->getStatusCode() !==200){
                $output->writeln($customerProfile->getContent());
                Return Command::FAILURE;
            }
            $customer = json_decode($customerProfile->getContent());
            //dump($customer);
    
            foreach($customer as $profile){
                //dump($profile);
                $id = $profile->customerid;
                $prof = json_decode(json_encode($profile));
                //dump($prof);
                $finalProf = json_encode($profile);
                //dump($finalProf);
                //dump($prof);
                //dump($comp);
                
                if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['customerid' => $id]))
                {
                    $this->serializer->deserialize($finalProf, Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                    $this->logger->info($prof->name . ' was sucessfully updated.');
                        //echo "Updating customer";
                        //dump($customer);
                }
                else{
                    $customer = $this->serializer->deserialize($finalProf, Customer::class, 'json');
                    $this->logger->info($prof->name . ' was sucessfully added.');
                        //echo "Adding customer";
                        //dump($customer);                 
                }
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                    //return Command::SUCCESS;

                $output->writeln($prof->name . ' has been saved / updated.');

                
            };
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $this->logger->warning(get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ' using [Customer] ' . '[' . $input->getArgument('Customers') .
            '/' . $input->getArgument('Tenant') . ']');

            $output->writeln('There has been an error. Check logs.');

            return Command::FAILURE;
        }
    }

    
}
