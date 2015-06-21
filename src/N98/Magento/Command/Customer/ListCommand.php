<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class ListCommand extends AbstractCustomerCommand
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterBuilder $filterBuilder = null
    ) {
        parent::__construct();

        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    protected function configure()
    {
        $this
            ->setName('customer:list')
            ->setDescription('Lists all magento customers')
            ->addArgument('search', InputArgument::OPTIONAL, 'Search query')
            ->addOption(
                'format', null, InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        $help = <<<HELP
Lists all Magento Customers of current installation.
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $search = null;
        if ($input->getArgument('search')) {
            $search = $input->getArgument('search');
        }

        $customers = $this->getCustomerList($search);

        $table = [];
        /** @var CustomerInterface[] $items */
        $items = $customers->getItems();
        foreach ($items as $customer) {
            $table[] = [
                'id'         => $customer->getId(),
                'email'      => $customer->getEmail(),
                'firstname'  => $customer->getFirstname(),
                'lastname'   => $customer->getLastname(),
                'website'    => $customer->getWebsiteId(),
                'created_at' => $customer->getCreatedAt(),
            ];
        }

        if (count($table) > 0) {
            $helper = $this->getHelper('table');
            $helper->setHeaders(['id', 'email', 'firstname', 'lastname', 'website', 'created_at']);
            $helper->renderByFormat($output, $table, $input->getOption('format'));
        }
        else {
            $output->writeln('<comment>No customers found</comment>');
        }
    }

    /**
     * @param string $search
     *
     * @return CustomerSearchResultsInterface
     */
    protected function getCustomerList($search = null)
    {
        // Prepare Search
        if ($search !== null) {
            $this->getFilterBuilder()->setField('email');
            $this->getFilterBuilder()->setConditionType('like');
            $this->getFilterBuilder()->setValue('%' . $search . '%');
            $emailFilter = $this->getFilterBuilder()->create();

            $this->getFilterBuilder()->setField('firstname');
            $this->getFilterBuilder()->setConditionType('like');
            $this->getFilterBuilder()->setValue('%' . $search . '%');
            $firstNameFilter = $this->getFilterBuilder()->create();

            $this->getFilterBuilder()->setField('lastname');
            $this->getFilterBuilder()->setConditionType('like');
            $this->getFilterBuilder()->setValue('%' . $search . '%');
            $lastnameFilter = $this->getFilterBuilder()->create();

            $this->getSearchCriteriaBuilder()->addFilter([$emailFilter, $firstNameFilter, $lastnameFilter]);
        }

        // Create SearchCriteria Object
        $searchCriteria = $this->getSearchCriteriaBuilder()->create();

        // Search for Customers
        $customers = $this->getCustomerRepository()->getList($searchCriteria);

        return $customers;
    }

    /**
     * @return CustomerRepositoryInterface
     */
    public function getCustomerRepository()
    {
        if($this->customerRepository===null){
            $this->customerRepository = $this->getObjectManager()->create(CustomerRepositoryInterface::class);
        }
        return $this->customerRepository;
    }

    /**
     * @return FilterBuilder
     */
    public function getFilterBuilder()
    {
        if($this->filterBuilder===null){
            $this->filterBuilder = $this->getObjectManager()->create(FilterBuilder::class);
        }
        return $this->filterBuilder;
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        if($this->searchCriteriaBuilder===null){
            $this->searchCriteriaBuilder = $this->getObjectManager()->create(SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

}