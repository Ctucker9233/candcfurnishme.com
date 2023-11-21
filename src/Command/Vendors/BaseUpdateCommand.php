<?php

namespace App\Command\Vendors;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use App\Helper\InventoryHelper;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:base-update',
        description: 'Update items and locations'   
    )]
class BaseUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
        private readonly InventoryHelper $inventoryHelper,
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('vendor', InputArgument::REQUIRED, 'vendor');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $vendor = $input->getArgument('vendor');

            //cache locations for this vendor
            $cacheInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:location-cache',
                'vendor'    => $vendor,
            ]);
    
            $returnCode = $this->getApplication()->doRun($cacheInput, $output);

            //dump($returnCode);
            dump("locations cached");

            //cache items
            $itemInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:item-cache',
                'vendor'    => $vendor,
            ]);

            $returnCode2 = $this->getApplication()->doRun($itemInput, $output);
            //dump($returnCode3);
            dump("items cached");

            //cache item ids for this cendor

            $itemIDInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:itemId-cache',
                'vendor'    => $vendor,
            ]);

            $returnCode3 = $this->getApplication()->doRun($itemIDInput, $output);
            //dump($returnCode3);
            dump("item ids cached");
            
            //update inventory
            $inventoryInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:inventory-update',
                'ProductsByVendor' => 'ProductsByVendor',
                'InventoriesByVendor' => 'InventoriesByVendor',
                'Tenant' => '?Tenant=sm6apxdkrh',
                'vendor' => $vendor
            ]);

            $returnCode4 = $this->getApplication()->doRun($inventoryInput, $output);
            //dump($returnCode3);
            dump("items updated cached");
            
            //run location update for this vendor
            $locationInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:location-update',
                'Inventories' => 'Inventories',
                'Tenant' => '?Tenant=sm6apxdkrh',
                'vendor' => $vendor
            ]);
            
            $returnCode5 = $this->getApplication()->doRun($locationInput, $output);
            //dump($returnCode5);
            dump("locations updated");
            
            return Command::SUCCESS;
        }
    
        catch(\Exception $exception)
        {

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . '.');

            dump($error);

            return Command::FAILURE;
        }
    }   
}