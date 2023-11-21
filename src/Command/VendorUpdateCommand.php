<?php

namespace App\Command;

use App\Entity\Vendors;
use App\Entity\Tasks;
use App\Helper\VendorMatcher;
use App\Http\VendorApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'app:vendor-update',
    description: 'Update Vendor Data'   
)]
class VendorUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VendorMatcher $vendorMatcher,
        private readonly VendorApiClient $vendorApiClient, 
        private readonly SerializerInterface $serializer, 
        private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('Vendors', InputArgument::REQUIRED, 'Vendors')
            ->addArgument('Tenant', InputArgument::REQUIRED, 'Tenant')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try
        {
            //first cache current database
            $command = $this->getApplication()->find('app:vendor-cache');
            $arguments = [];
            $input2 = new ArrayInput($arguments);
            $returnCode = $command->run($input2, $output);
            $this->logger->info($returnCode);

            $vendors = $this->vendorApiClient->fetchVendors($input->getArgument('Vendors'), $input->getArgument('Tenant'));
            //dump($vendors->getContent());
            
            echo "Back in Command";

            if($vendors->getStatusCode() !==200){
                dump($vendors->getContent());
                //$output->writeln($quantities->getContent());
                return Command::FAILURE;
            }
            $vend = json_decode($vendors->getContent());
            
            //dump($vend);
            
            foreach($vend as $v){
                //dump($v);
                $vnd = json_encode($v);
                $vendId = $v->vendorId;
                $checkVend = json_decode(json_encode($v, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
                $result = $this->vendorMatcher->matcher($checkVend);
                if($result === 'match'){
                    dump($checkVend['vendorName'] . ' is a complete match. Skipping.');
                }
                else{
                    if($vendor = $this->entityManager->getRepository(Vendors::class)->findOneBy(['vendorId' => $vendId])){
                        $this->serializer->deserialize($vnd, Vendors::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $vendor]);
                        dump("Vendor id ".$vendId." updated");
                    }
                    else{
                        $vendor = $this->serializer->deserialize($vnd, Vendors::class, 'json'); 
                        $vendor->setActive(false);
                        dump("Vendor id ".$vendId." added");              
                    }
                    $this->entityManager->persist($vendor);
                    $this->entityManager->flush();

                    dump($vendor->getVendorName() . ' has been saved / updated.');
                }
                
            };

            return Command::SUCCESS;
        }
        catch(\Exception $exception){

            $error = $this->logger->warning($exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile()
                . ' on line ' . $exception->getLine() . ' using [Vendors] ' . '[' . $input->getArgument('Vendors') .
            '/' . $input->getArgument('Tenant') . ']');

            dump($error);

            return Command::FAILURE;
        }
    }
}
