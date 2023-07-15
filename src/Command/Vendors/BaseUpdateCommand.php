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
            $command = $this->getApplication()->find('app:location-cache');
            $arguments = array(
                "vendor" => $vendor
            );
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            //dump($returnCode);
            dump("locations cached");

            $command2 = $this->getApplication()->find('app:item-cache');
            $arguments2 = array(
                "vendor" => $vendor
            );
            $input2 = new ArrayInput($arguments2);
            $returnCode2 = $command2->run($input2, $output);
            //dump($returnCode3);
            dump("items cached");

            //cache item ids for this cendor
            $command3 = $this->getApplication()->find('app:itemId-cache');
            $arguments3 = array(
                "vendor" => $vendor
            );
            $input3 = new ArrayInput($arguments3);
            $returnCode3 = $command3->run($input3, $output);
            //dump($returnCode3);
            dump("item ids cached");
            
            $command4 = $this->getApplication()->find('app:inventory-update');
            $arguments4 = array(
                "ProductsByVendor" => "ProductsByVendor",
                "InventoriesByVendor" => "InventoriesByVendor",
                "Tenant" => '?Tenant=sm6apxdkrh',
                "vendor" => $vendor
            );
            $input4 = new ArrayInput($arguments4);
            $returnCode4 = $command4->run($input4, $output);
            //dump($returnCode3);
            dump("items updated cached");
            
            //run location update for this vendor
            $command5 = $this->getApplication()->find('app:location-update');
            $arguments5 = array(
                "Inventories" => 'Inventories',
                "Tenant" => '?Tenant=sm6apxdkrh',
                "vendor" => $vendor
            );
            $input5 = new ArrayInput($arguments5);
            $returnCode5 = $command5->run($input5, $output);
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