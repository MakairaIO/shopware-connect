<?php
namespace MakairaConnect\Controllers\Frontend;

use Enlight_Controller_Action;
use Enlight_Controller_Response_ResponseHttp;

class BaseFrontendController extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
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
}