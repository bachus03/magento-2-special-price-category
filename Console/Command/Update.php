<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magmakers\SpecialPriceCategory\Model\ManageProducts;

class Update extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
    /**
     * @var ManageProducts|string|null
     */
    private $manageProducts;

    /**
     * {@inheritdoc}
     */
    public function __construct(ManageProducts $manageProducts,$name=null)
    {

        $this->manageProducts = $manageProducts;
        parent::__construct($name);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);

        $this->manageProducts->AssignProducts();
        $this->manageProducts->RemoveProducts();

            $output->writeln("Promotion category has been updated");

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("ecom_specialpricecategory:update");
        $this->setDescription("Script run through Products and if they have special price assing them to selected category. Next run trought all products in selected cattegory and checks if they sitll have special price. If not remove them. It alse removes them if special price promotion period has ended.");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}
