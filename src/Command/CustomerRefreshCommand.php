<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Tasks;
use App\Helper\CustomerMatcher;
use App\Http\ProfitApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'app:customer-refresh',
    description: 'Refresh Customer Data'   
)]
class CustomerRefreshCommand extends Command
{
    /**
     *@var customerQuery
     */
    private $customerQuery = 'Customers';
    /**
     *@var tenant
     */
    private $tenant = '?Tenant=sm6apxdkrh';

    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly ProfitApiClient $profitApiClient,
        private readonly CustomerMatcher $customerMatcher,
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel, 
        private readonly LoggerInterface $logger)
    {
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
            //first cache current database
            $customerInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:customer-cache',
            ]);
    
            $returnCode = $this->getApplication()->doRun($customerInput, $output);
            dump($returnCode);

            //get new data from api
            $customerProfile = $this->profitApiClient->fetchCustomerProfile($input->getArgument('Customers'), $input->getArgument('Tenant'));

            //dump($customerProfile);
            //dump($customerProfile->getStatusCode());
            //dump($customerProfile->getContent());
            echo "Back in Command";

            if($customerProfile->getStatusCode() !==200){
                dump($customerProfile->getContent());
                return Command::FAILURE;
            }
            $customer = json_decode($customerProfile->getContent(), null, 512, JSON_THROW_ON_ERROR);
            //dump($customer);
    
            foreach($customer as $profile){
                //dump(json_decode(json_encode($profile), true));
                $checkProf = json_decode(json_encode($profile, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
                //dump($prof);
                $result = $this->customerMatcher->matcher($checkProf);
                //dump($result);
                $prof = json_encode($profile, JSON_THROW_ON_ERROR);
                $id = $profile->customerid;
                if($result === 'match'){
                    dump($profile->name . ' is a complete match. Skipping.');
                }
                else{
                    if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['customerid' => $id]))
                    {
                        $this->serializer->deserialize($prof, Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                        //dump($checkProf['name'] . ' has been updated.');
                        //echo "Updating customer";
                        //dump($customer);
                    }
                    else{
                        $customer = $this->serializer->deserialize($prof, Customer::class, 'json');
                        //dump($checkProf['name'] . ' has been added.');
                        //echo "Adding customer";
                        //dump($customer);                 
                    }
                    $this->entityManager->persist($customer);
                    $this->entityManager->flush();
                //$output->writeln($prof->name . ' has been saved / updated.');
                }  
 
            };

            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ' using [Customer] ' . '[' . $input->getArgument('Customers') .
            '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }
    }   
}