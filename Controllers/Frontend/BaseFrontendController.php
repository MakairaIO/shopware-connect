<?php
namespace MakairaConnect\Controllers\Frontend;

use Enlight_Controller_Action;
use Enlight_Controller_Exception;
use Enlight_Controller_Response_ResponseHttp;
use Exception;
use Makaira\Signing\Hash\Sha256;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Plugin\Configuration\ReaderInterface;

class BaseFrontendController extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * Pre dispatch method
     * @throws Enlight_Controller_Exception
     */
    public function preDispatch()
    {
        $this->container->get('plugin_manager')->Controller()->ViewRenderer()->setNoRender();
        $configReader = $this->container->get(ReaderInterface::class);
        $config = $configReader->getByPluginName('MakairaConnect');

        $result = $this->verifySignature($config['makaira_connect_secret']);
        if ($result instanceof Enlight_Controller_Response_ResponseHttp) {
            $result->send();
            exit;
        }
    }

    /**
     * @param string $secret
     * @return ?Enlight_Controller_Response_ResponseHttp
     */
    private function verifySignature(string $secret): ?Enlight_Controller_Response_ResponseHttp
    {
        if (!$this->request->headers->has('x-makaira-nonce') ||
            !$this->request->headers->has('x-makaira-hash')) {
            return $this->createResponse([
                'message' => 'Unauthorized'
            ], 401);
        }

        $signer = new Sha256();

        $expected = $signer->hash(
            $this->request->headers->get('x-makaira-nonce'),
            $this->request->getContent(),
            $secret
        );

        $current = $this->request->headers->get('x-makaira-hash');

        if (!hash_equals($expected, $current)) {
            return $this->createResponse([
                'message' => 'Forbidden'
            ], 403);
        }

        return null;
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