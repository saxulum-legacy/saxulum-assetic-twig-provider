<?php

namespace Saxulum\AsseticTwig\Command;

use Saxulum\AsseticTwig\Assetic\Helper\Dumper;
use Saxulum\Console\Command\AbstractPimpleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AsseticDumpCommand extends AbstractPimpleCommand
{
    protected function configure()
    {
        $this
            ->setName('assetic:dump')
            ->setDescription('Dumps all assets to the filesystem')
        ;
    }

    /**
     * @param  InputInterface    $input
     * @param  OutputInterface   $output
     * @return int|null|void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getDumper()->dump();
    }

    /**
     * @return Dumper
     */
    protected function getDumper()
    {
        return $this->container['assetic.asset.dumper'];
    }
}
