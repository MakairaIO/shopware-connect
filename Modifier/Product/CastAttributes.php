<?php

namespace MakairaConnect\Modifier\Product;

use MakairaConnect\Modifier\ProductModifierInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class CastAttributes implements ProductModifierInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $attributesAsInt;

    /**
     * @var array
     */
    private $attributesAsFloat;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config            = $config;
        $this->attributesAsInt   = array_map(
            'trim',
            array_filter((array) explode(',', $this->config['makaira_attribute_as_int']))
        );
        $this->attributesAsFloat = array_map(
            'trim',
            array_filter((array) explode(',', $this->config['makaira_attribute_as_float']))
        );
    }

    public function modifyProduct(array &$mappedData, Product $item, ShopContext $context): void
    {
        if (0 < count($this->attributesAsInt) || 0 < count($this->attributesAsFloat)) {
            $attributesInt       = [];
            $attributesFloat     = [];
            $reindexAttributeStr = false;
            $attributeStr        = $mappedData['attributeStr'] ?? [];

            foreach ($attributeStr as $key => $attribute) {
                $unsetAttributeStr = false;
                if (in_array($attribute['id'], $this->attributesAsInt)) {
                    $attributesInt[]   = $attribute;
                    $unsetAttributeStr = true;
                } elseif (in_array($attribute['id'], $this->attributesAsFloat)) {
                    $attributesFloat[] = $attribute;
                    $unsetAttributeStr = true;
                }

                if ($unsetAttributeStr) {
                    unset($mappedData['attributeStr'][$key]);
                    $reindexAttributeStr = true;
                }
            }

            if (0 < count($attributesInt)) {
                $mappedData['attributeInt'] = array_merge($mappedData['attributeInt'] ?? [], $attributesInt);
            }

            if (0 < count($attributesFloat)) {
                $mappedData['attributeFloat'] = array_merge($mappedData['attributeFloat'] ?? [], $attributesFloat);
            }

            if ($reindexAttributeStr) {
                $mappedData['attributeStr'] = array_values($mappedData['attributeStr']);
            }
        }
    }

}
