# MageWorx_DonationsGraphQl

GraphQL API module for Mageworx [Magento 2 Donations Suite](https://www.mageworx.com/magento-2-donations-suite.html) extension. 

## Installation
**1) Copy-to-paste method**
- Download this module and upload it to the `app/code/MageWorx/DonationsGraphQl` directory *(create "DonationsGraphQl" first if missing)*

**2) Installation using composer (from packagist)**
- Execute the following command: `composer require mageworx/module-donation-graph-ql`

## How to use
**1.** mwDonationsInfo query returns the information about donations

**Syntax**<br />
```
mwDonationsInfo: MwDonationsInfo
```

The MwDonationsInfo object may contain the following attributes:

```
min_value: Float @doc(description: "Minimum donation amount")
default_description: String @doc(description: "Donation default description")
amount_placeholder: String @doc(description: "Donation amount placeholder")
default_charity_id: Int @doc(description: "Default organization")
predefined_values: [Float] @doc(description: "Predefined donation amounts")
allow_round_up: Boolean @doc(description: "Allow Round Up Donation")
enable_round_up_by_default: Boolean @doc(description: "Enable Round Up by default")
is_gift_aid_allowed: Boolean @doc(description: "Indicates if gift aid donations are allowed")
gift_aid_message: String @doc(description: "Gift Aid confirmation message")
charities: [MwCharity] @doc(description: "An array of Charities")
```

The MwCharity object may contain the following attributes:

```
id: Int @doc(description: "Charity ID")
name: String @doc(description: "Charity name")
description: String @doc(description: "Charity description")
sort_order: Int @doc(description: "Sort Order")
```

**Request:**

```
{
    mwDonationsInfo {
        min_value
        default_description
        amount_placeholder
        default_charity_id
        predefined_values
        allow_round_up
        enable_round_up_by_default
        is_gift_aid_allowed
        gift_aid_message
        charities {
            id
            name
            description
            sort_order
        }
    }
}
```

**Response:**

```json
{
    "data": {
        "mwDonationsInfo": {
            "min_value": 10,
            "default_description": "Default Description: Default text of the donation box, shown on the front-end",
            "amount_placeholder": "Enter your donation",
            "default_charity_id": 3,
            "predefined_values": [
                15,
                20,
                25
            ],
            "allow_round_up": true,
            "enable_round_up_by_default": true,
            "is_gift_aid_allowed": true,
            "gift_aid_message": "I confirm I'm a UK taxpayer.",
            "charities": [
                {
                    "id": 2,
                    "name": "Don Org 1 - def_st_v - name",
                    "description": "Don Org 1 - def_st_v - descr",
                    "sort_order": 0
                },
                {
                    "id": 3,
                    "name": "test 2",
                    "description": "Description for test 2",
                    "sort_order": 0
                }
            ]
        }
    }
}
```

**2.** The *addMwDonationToCart* mutation is used to add a donation to the shopping cart.

**Syntax**<br />
```
addMwDonationToCart(input: AddMwDonationToCartInput): AddMwDonationToCartOutput
```

The AddMwDonationToCartInput object may contain the following attributes:

```
cart_id: String! @doc(description:"The unique ID that identifies the customer's cart")
charity_id: Int @doc(description: "Charity ID")
amount: Float @doc(description: "Donation amount")
round_up: Boolean @doc(description: "Round up")
uk_confirm: Boolean @doc(description: "Confirms UK Taxpayer")
gift_aid_address: String @doc(description: "UK address")
```

The AddMwDonationToCartOutput object contains the Cart object. We add the new attribute *added_mw_donation* to the Cart object.
```
added_mw_donation: AddedMwDonation @doc(description:"Added donation")
```

The AddedMwDonation object may contain the following attributes:

```
charity_id: Int @doc(description: "Charity ID")
charity_name: String @doc(description: "Charity name")
global_amount: Float @doc(description: "Global donation amount")
amount: Float @doc(description: "Donation amount")
round_up: Boolean @doc(description: "Round up")
gift_aid_address: String @doc(description: "UK address")
```

**Request:**

```
mutation {
  addMwDonationToCart(
    input: {
      cart_id: "kPi7RAFpz6qNJMEYDmjwXenMWvj5NqSz"
      charity_id: 2
      amount: 20
      round_up: false
      uk_confirm: true
      gift_aid_address: "test address. API"
    }
  ) {
    cart {
      items {
        id
        product {
          sku
          stock_status
        }
        quantity
      }
      added_mw_donation {
            charity_id
            charity_name
            global_amount
            amount
            round_up
            gift_aid_address
        }
    }
  }
}
```

**Response:**

```json
{
    "data": {
        "addMwDonationToCart": {
            "cart": {
                "items": [
                    {
                        "id": "122",
                        "product": {
                            "sku": "24-MB02",
                            "stock_status": "IN_STOCK"
                        },
                        "quantity": 4
                    },
                    {
                        "id": "123",
                        "product": {
                            "sku": "24-MB04",
                            "stock_status": "IN_STOCK"
                        },
                        "quantity": 1
                    },
                    {
                        "id": "126",
                        "product": {
                            "sku": "MW-Gift-mail",
                            "stock_status": "IN_STOCK"
                        },
                        "quantity": 4
                    }
                ],
                "added_mw_donation": {
                    "charity_id": 2,
                    "charity_name": "Don Org 1 - def_st_v - name",
                    "global_amount": 20,
                    "amount": 20,
                    "round_up": false,
                    "gift_aid_address": "test address. API"
                }
            }
        }
    }
}
```
