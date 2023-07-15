<?php

namespace App\Command\Vendors;

use App\Entity\Inventory;
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
        name: 'app:park-hill-update',
        description: 'Update items and locations'   
    )]
class ParkHillUpdateCommand extends Command
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

            $command = $this->getApplication()->find('app:base-update');
            $arguments = array(
                "vendor" => $vendor
            );
            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);
            //dump($returnCode);
            
            dump('Park Hill Success!');

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