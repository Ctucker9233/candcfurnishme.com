<?php

namespace App\Command;

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
use App\Helper\InventoryHelper;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:item-cache',
        description: 'Cache Item Data'   
    )]
class ItemCacheCommand extends Command
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
            $cache = new PhpFilesAdapter(
                $namespace = "inventory",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $value = $this->entityManager->getRepository(Inventory::class)->findAll();
            foreach($value as $item){
                if($item->getVendorId() === $vendor){
                    $itemCache = $cache->getItem($item->getItemID()."_".$vendor);
                    $result = $this->inventoryHelper->dbItemProcessor($item, $vendor);
                    $itemCache->set($result);
                    $cache->save($itemCache);
                }
            }
            
            
            
            //dump($result[0]);
            //dump($cache->getItem('customers'));
            
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