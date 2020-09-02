<?php

/**
 * Class CartItem
 * @internal
 */
class CartItem {
    /**
     * @var string
     */
    public $sku;

    /**
     * @var int
     */
    public $quantity;
}

/**
 * Class Customer
 * @internal
 */
class Customer {
    /**
     * @var string
     */
    public $name;
}

/**
 * Class TestEntity
 * @internal
 */
class Cart {
    /**
     * @var CartItem[]
     */
    public $cartItems;

    /**
     * @var Customer|null
     */
    public $customer;
}


$input = [
    "cartItems" => [
        [
            "sku" => "prod1",
            "quantity" => 2
        ],
        [
            "sku" => "prod2",
            "quantity" => 6
        ],
    ]
];

$cart = phore_hydrate($input, Cart::class);

assert ($cart instanceof Cart);

