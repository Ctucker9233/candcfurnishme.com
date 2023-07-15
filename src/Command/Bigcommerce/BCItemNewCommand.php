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
        name: 'app:bc-item-new',
        description: 'send item information to bigcommerce'   
    )]
class BCItemNewCommand extends Command
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

                //if not in bigcommerce and not hidden and is backorderable
                if($item->getBCItemId() === null && $item->isWebHide() === false && $item->getBackorderCode() === 'B' && $item->getBcItemDescription() !== null){
                    dump("new item");
                    $price = $item->getPrice() / 100;
                    $body = json_encode(array(
                        "name" => $item->getBcItemDescription(),
                        "type" => "physical",
                        "weight" => 0,
                        "sku" => $item->getItemID(),
                        "price" => $price,
                        "brand_id" => $item->getBCVendorId(),
                        "inventory_tracking" => "product",
                        "inventory_level" => $item->getQuantity(),
                        "is_visible" => false,
                        "mpn" => $item->getMfcsku(),
                        "gtin" => $item->getGtin()
                    ), JSON_FORCE_OBJECT);
                    dump($body);
                    $response = $this->client->postProduct($body);
                    $newid = json_decode($response->getContent())->data->id;
                    dump(json_decode($response->getContent()));
                    $itm = $this->entityManager->getRepository(Inventory::class)->findOneBy(['itemID' => $item->getItemID()]);
                    $itm->setBCItemId($newid);
                    $this->entityManager->persist($itm);
                    $this->entityManager->flush();
                }
            }

            $packages = $this->entityManager->getRepository(Packages::class)->findAll();
            foreach($packages as $package){

                if($package->getBcPackId() === null && $package->isActive() === true && $package->getBcPackDescription() !== null){
                    dump("new package");
                    $body = json_encode(array(
                        "name" => $package->getBcPackDescription(),
                        "type" => "physical",
                        "weight" => 0,
                        "sku" => $package->getPackageId(),
                        "price" => $package->getPrice() / 100,
                        "brand_name" => "Tuckers Valley Furniture",
                        "inventory_tracking" => "product",
                        "inventory_level" => $package->getPkgQuantity(),
                        "is_visible" => false,
                        "mpn" => $package->getBcPackId(),
                    ), JSON_FORCE_OBJECT);
                    //dump($body);
                    $response = $this->client->postProduct($body);
                    $newid = json_decode($response->getContent())->data->id;
                    dump(json_decode($response->getContent()));
                    $pkg = $this->entityManager->getRepository(Packages::class)->findOneBy(['packageId' => $package->getPackageId()]); 
                    $pkg->setBCPackId($newid);
                    $this->entityManager->persist($pkg);
                    $this->entityManager->flush(); 
                }
            }
            
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