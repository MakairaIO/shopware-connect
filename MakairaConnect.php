<?php
/**
 * This file is part of a makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit https://www.makaira.io
 *
 * @version    1.0
 * @author     Stefan Krenz, Christopher Schnecke, Jennifer Timm
 * @link       https://www.makaira.io
 */

namespace MakairaConnect;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Tools\SchemaTool;
use MakairaConnect\DependencyInjection\ModifierCompilerPass;
use MakairaConnect\Models\MakRevision as MakRevisionModel;
use PDO;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function file_exists;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class MakairaConnect extends Plugin
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ModifierCompilerPass());
    }

    /**
     * automatic called (from shopware system)
     * Install plugin method
     *
     * @param InstallContext $installContext
     *
     * @return void
     */
    public function install(InstallContext $installContext)
    {
        $this->checkSchemaStructure();
        $this->installModels();
    }

    public function update(UpdateContext $context)
    {

        $this->checkSchemaStructure();
        parent::update($context);
    }

    private function checkSchemaStructure()
    {
        /** @var Connection $conn */
        $conn = $this->container->get('dbal_connection');

        $state = $conn->prepare(
            "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'mak_revision' LIMIT 1"
        );
        $state->execute([$conn->getDatabase()]);

        if ($state->fetchColumn()) {
            $conn->exec('ALTER TABLE mak_revision RENAME TO s_plugin_makaira_connect_revision');
        }
    }

    /**
     * Installs all registered models
     * -> make sure to use the save mode
     */
    private function installModels()
    {
        $schemaTool = $this->fetchSchemaTool();
        $schemaTool->updateSchema(
            $this->getMappingClassesMetaData(),
            true
        );
    }

    /**
     * @return SchemaTool
     */
    private function fetchSchemaTool()
    {
        $entityManager = $this->container->get('models');

        return new SchemaTool($entityManager);
    }

    /**
     * @return array
     */
    private function getMappingClassesMetaData()
    {
        return [
            $this->container->get('models')->getClassMetadata(MakRevisionModel::class),
        ];
    }

    /**
     * automatic called (from shopware system)
     * Uninstall plugin method
     *
     * @param UninstallContext $unInstallContext
     *
     * @return void
     */
    public function uninstall(UninstallContext $unInstallContext)
    {
        //abort if the user chose to keep the userdata
        if ($unInstallContext->keepUserData()) {
            return;
        }

        $this->uninstallModels();
    }

    /**
     * Drops all registered models
     */
    private function uninstallModels()
    {
        $this->fetchSchemaTool()->dropSchema(
            $this->getMappingClassesMetaData()
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'CookieCollector_Collect_Cookies' => 'addComfortCookie'
        ];
    }

    public function addComfortCookie(): CookieCollection
    {
        $ns = $this->container->get('snippets')->getNamespace('makaira/tracking');

        $collection = new CookieCollection();
        $collection->add(new CookieStruct(
            'makairaTracking',
            "/^makaira/",
            (string) $ns->get('makaira/tracking/consent_description'),
            CookieGroupStruct::STATISTICS
        ));

        return $collection;
    }
}
