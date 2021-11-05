<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\DonationsGraphQl\Model\Resolver;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use MageWorx\Donations\Api\DonationManagementInterface;
use MageWorx\Donations\Api\Data\DonationDataInterfaceFactory;
use MageWorx\Donations\Api\Data\DonationDataInterface;

class AddDonationToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
     * @var DonationManagementInterface
     */
    protected $donationManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DonationDataInterfaceFactory
     */
    protected $donationDataInterfaceFactory;

    /**
     * AddDonationToCart constructor.
     *
     * @param GetCartForUser $getCartForUser
     * @param DonationManagementInterface $donationManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param DonationDataInterfaceFactory $donationDataInterfaceFactory
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        DonationManagementInterface $donationManagement,
        DataObjectHelper $dataObjectHelper,
        DonationDataInterfaceFactory $donationDataInterfaceFactory
    ) {
        $this->getCartForUser               = $getCartForUser;
        $this->donationManagement           = $donationManagement;
        $this->dataObjectHelper             = $dataObjectHelper;
        $this->donationDataInterfaceFactory = $donationDataInterfaceFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $maskedCartId  = $args['input']['cart_id'];
        $currentUserId = $context->getUserId();
        $storeId       = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart          = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        $cartId        = (int)$cart->getId();

        $donationData = $this->donationDataInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray($donationData, $args['input'], DonationDataInterface::class);

        try {
            $this->donationManagement->addToCart($cartId, $donationData);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (CouldNotSaveException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
