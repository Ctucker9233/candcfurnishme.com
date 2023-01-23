<?php

namespace App\Command;

use App\Entity\Inventory;
use App\Entity\ItemLocation;
use App\Http\InventoryApiClient;
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

class InventoryUpdateCommand extends Command
{
    protected static $defaultName = 'app:inventory-update';
    protected static $defaultDescription = 'Update Item Data';

    /**
     *@var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     *@var InventoryApiClient
     */
    private InventoryApiClient $inventoryApiClient;
    /**
     *@var serializer
     */
    private SerializerInterface $serializer;
    /**
     *@var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, InventoryApiClient $inventoryApiClient, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->inventoryApiClient = $inventoryApiClient;
        $this->serializer = $serializer;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Products', InputArgument::REQUIRED, 'Products')
            ->addArgument('Inventories', InputArgument::REQUIRED, 'Inventories')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $items = $this->inventoryApiClient->fetchItems($input->getArgument('Products'), $input->getArgument('Tenant'));
            $quantities = $this->inventoryApiClient->fetchQuantity($input->getArgument('Inventories'), $input->getArgument('Tenant'), $Id);
            
            echo "Back in Command";

            if(($items->getStatusCode() !==200) && ($quantities->getStatusCode() !==200)){
                $output->writeln($items->getContent());
                $output->writeln($quantities->getContent());
                Return Command::FAILURE;
            }
            $inventory = json_decode($items->getContent());
            //$quantity = json_decode($quanitites->getContent());
            //dump($inventory);
            //dump($quantity);
            foreach($inventory as $item){
                $Id = $item->itemId;
                
                
                //dump($customerRecord);
                //if($customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['customerid' => $profId])){
                    //$this->serializer->deserialize($customerRecord->getContent(), Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $customer]);
                    //$this->logger->info("Customer id ".$profId." updated");
                //}
                //else{
                    //$customer = $this->serializer->deserialize($customerRecord->getContent(), Customer::class, 'json'); 
                    //$this->logger->info("Customer id ".$profId." added");              
                //}
                //$this->entityManager->persist($customer);
                //$this->entityManager->flush();
                //return Command::SUCCESS;

                //$output->writeln($customer->getName() . ' has been saved / updated.');
                
            };
            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $this->logger->warning(get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ' using [Customer] ' . '[' . $input->getArgument('Products') .
            '/' . $input->getArgument('Tenant') . ']');

            $output->writeln('There has been an error. Check logs.');

            return Command::FAILURE;
        }
    }
}
