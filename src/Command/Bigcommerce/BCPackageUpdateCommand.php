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
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use App\Http\Bigcommerce\Client;
use Psr\Log\LoggerInterface;

#[AsCommand(
        name: 'app:bc-package-update',
        description: 'send package information to bigcommerce'   
    )]
class BCPackageUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
        private readonly LoggerInterface $logger,
        private readonly Client $client
        )
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
            $packages = $this->entityManager->getRepository(Packages::class)->findAll();
            foreach($packages as $package){

                //if package is on bc
                if($package->getBcPackId() !== null && $package->getBcPackDescription() !== null){
                    dump("package");
                    if($package->isActive() !== true){
                        $id = $package->getBcPackId();
                        $body = json_encode(array(
                            "name" => $package->getBcPackDescription(),
                            "type" => "physical",
                            "price" => $package->getPrice() / 100,
                            "inventory_level" => $package->getPkgQuantity(),
                            "is_visible" => false
                        ), JSON_FORCE_OBJECT);
                        //dump($body);
                        $response = $this->client->setItemVisibility($id, $body);
                    }
                    if($package->isActive() === true){
                        $id = $package->getBcPackId();
                        $body = json_encode(array(
                            "name" => $package->getBcPackDescription(),
                            "type" => "physical",
                            "price" => $package->getPrice() / 100,
                            "inventory_level" => $package->getPkgQuantity(),
                            "is_visible" => true,
                        ), JSON_FORCE_OBJECT);
                            //dump($body);
                            $response = $this->client->setItemVisibility($id, $body);
                    }
                }
            }
            dump("BC Packages updated.");
            return Command::SUCCESS;
        }
    
        catch(\Exception $exception)
        {
            $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ']');

            $output->writeln('There has been an error. Check logs.');

            return Command::FAILURE;
        }
    }   
}