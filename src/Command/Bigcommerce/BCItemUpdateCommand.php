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
        name: 'app:bc-item-update',
        description: 'send item information to bigcommerce'   
    )]
class BCItemUpdateCommand extends Command
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
            $items = $this->entityManager->getRepository(Inventory::class)->findAll();

            foreach($items as $item){
                //dump($item);

                //if the product is in bc
                if($item->getBCItemId() !== null){
                    dump("item " . $item->getBCItemId() . " " . $item->getMfcsku());
                    dump($item->getQuantity());
                    //if hide on web and doesn't have a picture
                    if($item->isWebHide() === true || $item->getPictureLink() === null || $item->getQuantity() === 0){
                        $id = $item->getBCItemId();
                        $body = json_encode(array(
                            "name" => $item->getBcItemDescription(),
                            "type" => "physical",
                            "price" => $item->getPrice() / 100,
                            "inventory_level" => $item->getQuantity(),
                            "is_visible" => false
                        ), JSON_FORCE_OBJECT);
                        //dump($body);
                        $response = $this->client->setItemVisibility($id, $body);
                    }

                    if($item->isWebHide() === false && $item->getPictureLink() !== null){
                        $id = $item->getBCItemId();
                        $body = json_encode(array(
                            "name" => $item->getBcItemDescription(),
                            "type" => "physical",
                            "price" => $item->getPrice() / 100,
                            "inventory_level" => $item->getQuantity(),
                            "is_visible" => true,
                        ), JSON_FORCE_OBJECT);
                        //dump($body);
                        $response = $this->client->setItemVisibility($id, $body);
                    }
                }
            }

            dump("BC Items updated.");
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