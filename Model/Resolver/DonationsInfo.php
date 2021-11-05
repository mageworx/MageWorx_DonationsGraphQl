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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
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
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * DonationsInfo constructor.
     *
     * @param HelperData $helperData
     * @param CharitiesDataProvider $charitiesDataProvider
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        HelperData $helperData,
        CharitiesDataProvider $charitiesDataProvider,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->helperData            = $helperData;
        $this->charitiesDataProvider = $charitiesDataProvider;
        $this->priceCurrency         = $priceCurrency;
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
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        return [
            'min_value'                         => $this->getDonationAmountData(
                (float)$this->helperData->getMinimumDonation(),
                $store
            ),
            'default_description'               => $this->helperData->getDefaultDescription(),
            'amount_placeholder'                => $this->helperData->getAmountPlaceholder(),
            'default_charity_id'                => $this->helperData->getDefaultCharity(),
            'predefined_values'                 => $this->getPredefinedValues($store),
            'is_donation_custom_amount_allowed' => $this->helperData->isShowDonationCustomAmount(),
            'allow_round_up'                    => $this->helperData->isShowRoundUpDonation(),
            'enable_round_up_by_default'        => $this->helperData->isRoundUpSelectedByDefault(),
            'is_gift_aid_allowed'               => $this->helperData->isGiftAidDonationsEnabled(),
            'gift_aid_message'                  => $this->helperData->getGiftAidDonationsMessage(),
            'charities'                         => $this->charitiesDataProvider->getData((int)$store->getId())
        ];
    }

    /**
     * @param float $value
     * @param StoreInterface $store
     * @return array
     */
    protected function getDonationAmountData(float $value, StoreInterface $store): array
    {
        return [
            'value'         => $this->priceCurrency->roundPrice($value),
            'currency_code' => $store->getCurrentCurrencyCode(),
            'label'         => $this->priceCurrency->format(
                $value,
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store
            )
        ];
    }

    /**
     * @param StoreInterface $store
     * @return array
     */
    protected function getPredefinedValues(StoreInterface $store): array
    {
        $result = [];
        $values = $this->helperData->isShowPredefinedValues() ? $this->helperData->getPredefinedValuesDonation() : [];

        foreach ((array)$values as $value) {
            $result[] = $this->getDonationAmountData((float)$value, $store);
        }

        return $result;
    }
}
