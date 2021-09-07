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
use Magento\Framework\Serialize\SerializerInterface;

class AddedDonation implements ResolverInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * AddedDonation constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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

        $details      = $this->serializer->unserialize($details);
        $isUseRoundUp = isset($details['isUseDonationRoundUp']) ? (bool)$details['isUseDonationRoundUp'] : null;
        $ukAddress    = isset($details['uk_address_for_gift_aid']) ? $details['uk_address_for_gift_aid'] : null;

        return [
            'charity_id'       => isset($details['charity_id']) ? (int)$details['charity_id'] : null,
            'charity_name'     => isset($details['charity_title']) ? $details['charity_title'] : null,
            'global_amount'    => isset($details['global_donation']) ? (float)$details['global_donation'] : null,
            'amount'           => isset($details['donation']) ? (float)$details['donation'] : null,
            'round_up'         => $isUseRoundUp,
            'gift_aid_address' => $ukAddress
        ];
    }
}
