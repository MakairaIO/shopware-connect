<?php
namespace MakairaConnect\Controllers\Frontend;

use Enlight_Controller_Action;
use Enlight_Controller_Exception;
use Enlight_Controller_Response_ResponseHttp;
use Exception;
use Shopware\Components\CSRFWhitelistAware;

abstract class BaseFrontendController extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * Pre dispatch method
     * @throws Enlight_Controller_Exception
     */
    public function preDispatch()
    {
        $this->container->get('plugin_manager')->Controller()->ViewRenderer()->setNoRender();
    }

    protected function createResponse(array $data, int $statusCode = 200): Enlight_Controller_Response_ResponseHttp
    {
        $response = $this->Response();
        $response->setContent(json_encode($data));
        $response->setHeader('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }

    protected function getRequestParams(): array
    {
        return json_decode($this->request->getContent(), true);
    }

    /**
     * @throws Exception
     */
    public function indexAction(): Enlight_Controller_Response_ResponseHttp
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->createResponse([
                "ok" => false,
                "message" => 'Only POST is allowed'
            ], 405);
        }

        $requestParams = $this->getRequestParams();
        if (!isset($requestParams['action'])) {
            return $this->createResponse([
                "ok" => false,
                "message" => '"action" is required'
            ], 400);
        }

        if (!method_exists($this, $requestParams['action'])) {
            return $this->createResponse([
                "ok" => false,
                "message" => "Action {$requestParams['action']} doesn't exist"
            ], 400);
        }

        return $this->{$requestParams['action']}();
    }

    public function getWhitelistedCSRFActions(): array
    {
        return [
            'index'
        ];
    }
}
