<?php

namespace App\Command\Vendors;

use App\Entity\Inventory;
use App\Entity\Vendors;
use App\Entity\Tasks;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Helper\InventoryHelper;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:master-update',
        description: 'Trigger Update items and locations'   
    )]
class MasterUpdateCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            $vendorCacheInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'app:vendor-cache',
                ]); 
                
                $returnCode2 = $this->getApplication()->doRun($vendorCacheInput, $output);

            $cache = new PhpFilesAdapter(
                $namespace = "vendor",
                $defaultLifetime = 0,
                // single file where values are cached
                $directory = $this->kernel->getProjectDir() . '/var/cache'
            );
            $vendorCache = $cache->getItem('vendors');
            //dump($vendorCache);
            $vendors = $vendorCache->get();

            dump($vendors);
            foreach($vendors as $index)
            {
                foreach($index as $j){
                    //dump($j);
                    if($j['active']){
                       $commandString = $j['command'];
                       dump("Running ".$commandString);
                        $delimiter = ' ';
                        $splitCommand = explode($delimiter, $commandString);

                        $vendorInput = new ArrayInput([
                        // the command name is passed as first argument
                        'command' => $splitCommand[0],
                        'vendor'    => $splitCommand[1],
                        ]); 
                        
                        $returnCode = $this->getApplication()->doRun($vendorInput, $output);
                    }
                }
            }
            
            
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