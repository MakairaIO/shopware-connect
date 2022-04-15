<?php

use MakairaConnect\Controllers\Frontend\BaseFrontendController;

/**
 * This file is part of a makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.makaira.io
 * @version    0.1
 * @author     Sunny <dt@marmalade.group>
 */
class Shopware_Controllers_Frontend_User extends BaseFrontendController
{
    private sAdmin $admin;

    public function __construct()
    {
        parent::__construct();
        $this->admin = Shopware()->Modules()->Admin();
    }

    public function getCurrentUser(): Enlight_Controller_Response_ResponseHttp
    {
        if (!$this->admin->sCheckUser()) {
            return $this->createResponse([
                "ok" => false,
                "message" => "Forbidden"
            ], 403);
        }
        $userData = $this->admin->sGetUserData();
        if ($userData === false) {
            return $this->createResponse([
                "ok" => false
            ]);
        }

        return $this->createResponse($this->admin->sGetUserData());
    }

    public function login(): Enlight_Controller_Response_ResponseHttp
    {
        try {
            $requestParams = $this->getRequestParams();
            Shopware()->Front()->Request()->setPost([
                'email' => $requestParams['email'],
                'password' => $requestParams['password']
            ]);
            $result = $this->admin->sLogin();

            if ($result === false) {
                return $this->createResponse([
                    "ok" => false,
                    "message" => "The process is interrupted by an event"
                ], 500);
            }

            if (is_array($result) && !empty($result['sErrorMessages'])) {
                return $this->createResponse([
                    "ok" => false,
                    "errors" => $result
                ], 500);
            }

            return $this->createResponse([
                "ok" => true
            ]);
        } catch (Exception $e) {
            return $this->createResponse([
                "ok" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function logout(): Enlight_Controller_Response_ResponseHttp
    {
        $this->admin->logout();

        return $this->createResponse([
            "ok" => true
        ]);
    }
}
