<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\DonationsGraphQl\Model;

use MageWorx\Donations\Model\CharityRepository;
use MageWorx\Donations\Api\Data\CharityInterface;

class CharitiesDataProvider
{
    /**
     * @var CharityRepository
     */
    protected $charityRepository;

    /**
     * CharitiesDataProvider constructor.
     *
     * @param CharityRepository $charityRepository
     */
    public function __construct(CharityRepository $charityRepository)
    {
        $this->charityRepository = $charityRepository;
    }

    /**
     * @param int $storeId
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(int $storeId)
    {
        /** @var \MageWorx\Donations\Model\ResourceModel\Charity\Collection $charityCollection */
        $charityCollection = $this->charityRepository->getListCharity();
        $charityCollection->addFieldToFilter(CharityInterface::IS_ACTIVE, 1);
        $charityCollection->addStoreFilter($storeId);
        $charityCollection->addLocales($storeId);

        if ($charityCollection->count() > 0) {
            $data = [];

            /** @var \MageWorx\Donations\Model\Charity $charity */
            foreach ($charityCollection as $charity) {
                $data[$charity->getCharityId()] = [
                    'id'          => $charity->getCharityId(),
                    'name'        => $charity->getName(),
                    'description' => $charity->getDescription(),
                    'sort_order'  => $charity->getSortOrder()
                ];
            }

            return $data;
        }

        return null;
    }
}
