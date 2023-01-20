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

class CustomerUpdateCommand extends Command
{
    protected static $defaultName = 'app:customer-update';
    protected static $defaultDescription = 'Update Customer Data';

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
        try{
            $customerIds = $this->profitApiClient->fetchProfileIds($input->getArgument('Customers'), $input->getArgument('Tenant'));

            //dump($customerIds);
            //dump($customerIds->getStatusCode());
            //dump($customerIds->getContent());
            echo "Back in Command";

            if($customerIds->getStatusCode() !==200){
                $output->writeln($customerIds->getContent());
                Return Command::FAILURE;
            }
            $custIds = json_decode($customerIds->getContent());
            //dump(count($customers));
            foreach($custIds as $id){
                dump($id->customerid);
                //$custId = $profile->customerid;
                $profId = $id->customerid;
                //dump($custId);
                //dump($profId);
                
                $customerRecord = $this->profitApiClient->fetchSingleProfile($input->getArgument('Customers'), $input->getArgument('Tenant'), $profId);
                //dump($customerRecord);
                if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['customerid' => $profId])){
                    $this->serializer->deserialize($customerRecord->getContent(), Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                    $this->logger->info("Customer id ".$profId." updated");
                }
                else{
                    $customer = $this->serializer->deserialize($customerRecord->getContent(), Customer::class, 'json'); 
                    $this->logger->info("Customer id ".$profId." added");              
                }
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                //return Command::SUCCESS;

                $output->writeln($customer->getName() . ' has been saved / updated.');
                
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
