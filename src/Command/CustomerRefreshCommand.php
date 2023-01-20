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
            $id = json_decode($customerProfile->getContent())->customerid ?? null;
            //dump(count($customers));
    
            foreach($customer as $profile){
                //dump($profile);
                $id = $profile->customerid;
                $prof = json_decode(json_encode($profile));
                $finalProf = json_encode($profile);
                dump($finalProf);
                $dbCustomer = json_decode(($this->profitApiClient->fetchSingleProfile($input->getArgument('Customers'), $input->getArgument('Tenant'), $id))->getContent());
                //dump($dbCustomer);
                //dump($prof);
                $comp = self::compare($prof, $dbCustomer);
                //dump($comp);
                
                if($comp!==true)
                {
                    if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['customerid' => $custId]))
                    {
                        $this->serializer->deserialize($finalProf, Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                        //$this->entityManager->persist($customer);
                        //$this->entityManager->flush();
                        //echo "Updating customer";
                        //dump($customer);
                    }
                    else{
                        $customer = $this->serializer->deserialize($finalProf, Customer::class, 'json');
                        //$this->entityManager->persist($customer);
                        //$this->entityManager->flush();
                        //echo "Adding customer";
                        //dump($customer);                 
                    }
                    $this->entityManager->persist($customer);
                    $this->entityManager->flush();
                    //return Command::SUCCESS;

                    $output->writeln($prof->name . ' has been saved / updated.');
                }
                else{
                    $output->writeln($prof->name . ' matches completely. No update.');
                }
                
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

    public function compare($profile, $dbCustomer)
    {
        //dump($profile);
        //dump($dbCustomer);
        $result;
        (($profile->name)!==($dbCustomer[0]->name)) ? false :
        ((($profile->address1)!==($dbCustomer[0]->address1)) ? false :
        ((($profile->address2)!==($dbCustomer[0]->address2)) ? false :
        ((($profile->city)!==($dbCustomer[0]->city)) ? false :
        ((($profile->state)!==($dbCustomer[0]->state)) ? false :
        ((($profile->postalcode)!==($dbCustomer[0]->postalcode)) ? false :
        ((($profile->email)!==($dbCustomer[0]->email)) ? false :
        ((($profile->phone1)!==($dbCustomer[0]->phone1)) ? false :
        ((($profile->phone2)!==($dbCustomer[0]->phone2)) ? false :
        ((($profile->phone3)!==($dbCustomer[0]->phone3)) ? false :
        ((($profile->customerid)!==($dbCustomer[0]->customerid)) ? false :
        ((($profile->isDeleted)!==($dbCustomer[0]->isDeleted)) ? ($result = false) : ($result = true))))))))))));

        return $result;
    }
}
