<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\DonationsGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\Data\StoreInterface;

class AddedDonation implements ResolverInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * AddedDonation constructor.
     *
     * @param SerializerInterface $serializer
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(SerializerInterface $serializer, PriceCurrencyInterface $priceCurrency)
    {
        $this->serializer    = $serializer;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|null
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cart    = $value['model'];
        $address = $cart->getIsVirtual() ? $cart->getBillingAddress() : $cart->getShippingAddress();

        if (!$address) {
            return null;
        }

        $details = $address->getMageworxDonationDetails();

        if (empty($details)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store        = $context->getExtensionAttributes()->getStore();
        $details      = (array)$this->serializer->unserialize($details);
        $isUseRoundUp = isset($details['isUseDonationRoundUp']) ? (bool)$details['isUseDonationRoundUp'] : null;
        $ukAddress    = isset($details['uk_address_for_gift_aid']) ? $details['uk_address_for_gift_aid'] : null;

        return [
            'charity_id'       => isset($details['charity_id']) ? (int)$details['charity_id'] : null,
            'charity_name'     => isset($details['charity_title']) ? $details['charity_title'] : null,
            'global_amount'    => $this->getAmountData($details, 'global_donation', $store),
            'amount'           => $this->getAmountData($details, 'donation', $store),
            'round_up'         => $isUseRoundUp,
            'gift_aid_address' => $ukAddress
        ];
    }

    /**
     * @param array $details
     * @param string $key
     * @param StoreInterface $store
     * @return array|null
     */
    protected function getAmountData(array $details, string $key, StoreInterface $store): ?array
    {
        if (isset($details[$key])) {
            return $this->getDonationAmountData((float)$details[$key], $store);
        }

        return null;
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
}
