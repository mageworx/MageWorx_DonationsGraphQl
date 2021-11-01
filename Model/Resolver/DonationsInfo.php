<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\DonationsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use MageWorx\Donations\Helper\Data as HelperData;
use MageWorx\DonationsGraphQl\Model\CharitiesDataProvider;

class DonationsInfo implements ResolverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CharitiesDataProvider
     */
    protected $charitiesDataProvider;

    /**
     * DonationsInfo constructor.
     *
     * @param HelperData $helperData
     * @param CharitiesDataProvider $charitiesDataProvider
     */
    public function __construct(HelperData $helperData, CharitiesDataProvider $charitiesDataProvider)
    {
        $this->helperData            = $helperData;
        $this->charitiesDataProvider = $charitiesDataProvider;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        return [
            'min_value'                         => $this->helperData->getMinimumDonation(),
            'default_description'               => $this->helperData->getDefaultDescription(),
            'amount_placeholder'                => $this->helperData->getAmountPlaceholder(),
            'default_charity_id'                => $this->helperData->getDefaultCharity(),
            'predefined_values'                 => $this->getPredefinedValues(),
            'is_donation_custom_amount_allowed' => $this->helperData->isShowDonationCustomAmount(),
            'allow_round_up'                    => $this->helperData->isShowRoundUpDonation(),
            'enable_round_up_by_default'        => $this->helperData->isRoundUpSelectedByDefault(),
            'is_gift_aid_allowed'               => $this->helperData->isGiftAidDonationsEnabled(),
            'gift_aid_message'                  => $this->helperData->getGiftAidDonationsMessage(),
            'charities'                         => $this->charitiesDataProvider->getData($storeId)
        ];
    }

    /**
     * @return array
     */
    protected function getPredefinedValues(): array
    {
        return $this->helperData->isShowPredefinedValues() ? $this->helperData->getPredefinedValuesDonation() : [];
    }
}
