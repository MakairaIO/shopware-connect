<?php

use MakairaConnect\Controllers\Frontend\BaseFrontendController;

/**
 * This file is part of a makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.makaira.io
 * @version    0.1
 * @author     Sunny <dt@marmalade.group>
 */
class Shopware_Controllers_Frontend_Cart extends BaseFrontendController
{
    /**
     * @var sBasket
     */
    private $basket;

    public function __construct()
    {
        parent::__construct();
        $this->basket = Shopware()->Modules()->Basket();
    }

    protected function addArticleToCart(): Enlight_Controller_Response_ResponseHttp
    {
        try {
            $requestParams = $this->getRequestParams();
            $this->basket->sAddArticle($requestParams['article_id'], $requestParams['quantity']);

            return $this->createResponse([
                "ok" => true,
            ]);
        } catch (Exception $e) {
            return $this->createResponse([
                "ok" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    protected function updateArticleInCart(): Enlight_Controller_Response_ResponseHttp
    {
        try {
            $requestParams = $this->getRequestParams();
            if ($this->checkCartItemExist($requestParams['cart_item_id'])) {
                $this->basket->sUpdateArticle($requestParams['cart_item_id'], $requestParams['quantity']);

                return $this->createResponse([
                    "ok" => true,
                ]);
            } else {
                return $this->createResponse([
                    "ok" => false,
                    "message" => "Cart item id {$requestParams['cart_item_id']} doesn't exist"
                ], 400);
            }
        } catch (Exception $e) {
            return $this->createResponse([
                "ok" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * @throws Enlight_Event_Exception
     * @throws Zend_Db_Adapter_Exception
     * @throws Enlight_Exception
     */
    private function checkCartItemExist($cartItemId): bool
    {
        $basket = $this->basket->sGetBasket();
        $basketItems = $basket['content'];
        foreach ($basketItems as $basketItem) {
            if ((int)$basketItem['id'] === (int)$cartItemId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Enlight_Event_Exception
     * @throws Enlight_Exception
     * @throws Zend_Db_Adapter_Exception
     */
    protected function getCart(): Enlight_Controller_Response_ResponseHttp
    {
        return $this->createResponse($this->basket->sGetBasket());
    }

    protected function deleteCartItem(): Enlight_Controller_Response_ResponseHttp
    {
        try {
            $requestParams = $this->getRequestParams();

            if ($this->checkCartItemExist($requestParams['cart_item_id'])) {
                $this->basket->sDeleteArticle($requestParams['cart_item_id']);

                return $this->createResponse([
                    "ok" => true,
                ]);
            } else {
                return $this->createResponse([
                    "ok" => false,
                    "message" => "Cart item id {$requestParams['cart_item_id']} doesn't exist"
                ], 400);
            }
        } catch (Exception $e) {
            return $this->createResponse([
                "ok" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
}
