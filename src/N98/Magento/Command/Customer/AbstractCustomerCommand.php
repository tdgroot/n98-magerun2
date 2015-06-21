<?php

namespace N98\Magento\Command\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Resource\Customer\Collection as CustomerCollection;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\DateTime as DateTimeUtils;

class AbstractCustomerCommand extends AbstractMagentoCommand
{
    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        return $this->getObjectManager()->get(Customer::class);
    }

    /**
     * @return CustomerCollection
     */
    protected function getCustomerCollection()
    {
        return $this->getObjectManager()->get(CustomerCollection::class);
    }

}
