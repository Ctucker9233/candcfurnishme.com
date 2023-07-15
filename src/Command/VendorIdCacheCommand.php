<?php

namespace App\Command;

use App\Entity\Vendors;
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
use App\Helper\VendorHelper;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:vendorId-cache',
        description: 'Cache Vendor Data'   
    )]
class VendorIdCacheCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
        private readonly VendorHelper $vendorHelper,
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $cache = new PhpFilesAdapter(
                $namespace = "vendor",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $vendorIdCache = $cache->getItem('vendorIds');
            $value = $this->entityManager->getRepository(Vendors::class)->findAll();
            $result = $this->vendorHelper->dbVendorIdProcessor($value);
            //dump($result);
            $vendorIdCache->set([$result]);
            $cache->save($vendorIdCache);
            
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