<?php

namespace App\Command\Bigcommerce;

use App\Entity\Inventory;
use App\Entity\Packages;
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
use App\Helper\InventoryHelper;
use App\Helper\PackageHelper;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:bc-item-cache',
        description: 'Cache Item Data'   
    )]
class BCItemCacheCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
        private readonly InventoryHelper $inventoryHelper,
        private readonly PackageHelper $packageHelper,
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('vendor', InputArgument::OPTIONAL, 'vendor');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $cache = new PhpFilesAdapter(
                $namespace = "BCinventory",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $value = $this->entityManager->getRepository(Inventory::class)->findAll();

            foreach($value as $item){
                $itemCache = $cache->getItem($item->getItemID());
                $result = $this->inventoryHelper->dbBCItemProcessor($item);
                $itemCache->set($result);
                $cache->save($itemCache); 
            }

            $packages = $this->entityManager->getRepository(Packages::class)->findAll();
            foreach($packages as $package){
                $itemCache = $cache->getItem($package->getPackageId());
                $result = $this->packageHelper->dbBCPackageProcessor($package);
                $itemCache->set($result);
                $cache->save($itemCache);    
            }
            
            
            
            //dump($result[0]);
            //dump($cache->getItem('customers'));
            
            return Command::SUCCESS;
        }
    
        catch(\Exception $exception)
        {

            $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . '.');

            $output->writeln('There has been an error. Check logs.');

            return Command::FAILURE;
        }
    }   
}