{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    {if {config name='makaira_tracking_page_id' namespace='MakairaConnect'}}
        <script type="text/javascript">
            var _paq = window._paq || [];

            _paq.push(['enableLinkTracking']);

            {* PRODUCT DETAIL PAGE *}
            {if $Controller == "detail"}
                _paq.push([
                    'setEcommerceView',
                    '{$sArticle.ordernumber}',
                    '{$sArticle.articleName|escape:'javascript'}',
                    '{$sCategoryInfo.name|escape:'javascript'}'
                ]);
                _paq.push(['trackPageView']);
            {/if}

            {* CATEGORY *}
            {if $Controller == "listing"}
                _paq.push([
                    'setEcommerceView',
                    false,
                    false,
                    '{$sCategoryContent.name|escape:'javascript'}'
                ]);
                _paq.push(['trackPageView']);
            {/if}

            {* SEARCH *}
            {if $Controller == "search"}
                _paq.push(['deleteCustomVariables', 'page']);
                _paq.push(['trackSiteSearch', '{$sRequests.sSearchOrginal|escape:'javascript'}', false, '{$sSearchResults.sArticlesCount}']);
                _paq.push(['trackPageView']);
            {/if}

            {* CHECKOUT/PURCHASE *}
            {if $sBasket.content and $sOrderNumber}
                {assign var="discount" value=0}

                {foreach $sBasket.content as $sBasketItem}
                    _paq.push(['addEcommerceItem',
                        '{$sBasketItem.ordernumber|escape:'javascript'}',
                        '{$sBasketItem.articlename|escape:'javascript'}',
                        '',
                        '{$sBasketItem.priceNumeric|round:2}',
                        '{$sBasketItem.quantity}',
                    ]);

                    {if $sBasketItem.priceNumeric < 0}
                        {$discount = $discount + $sBasketItem.priceNumeric}
                    {/if}
                {/foreach}

                {* Make discount positive, because discount items have a negative price *}
                {$discount = $discount * -1}

                {if $sAmountWthTax}
                    {assign var="revenue" value=$sAmountWithTax|replace:",":"."}
                {else}
                    {assign var="revenue" value=$sAmount|replace:",":"."}
                {/if}

                {assign var="shipping" value=$sShippingcosts|replace:",":"."}
                {assign var="subTotal" value=$revenue - $shipping}

                {assign var="tax" value=0}
                {foreach $sBasket.sTaxRates as $rate => $value}
                    {$tax = $tax + $value}
                {/foreach}

                _paq.push([
                    'trackEcommerceOrder',
                    '{$sOrderNumber}',
                    '{$revenue|round:2}',
                    '{$subTotal|round:2}',
                    '{$tax|round:2}',
                    '{$shipping|round:2}',
                    '{$discount}'
                ]);
            {/if}

            (function() {
                var u="https://piwik.makaira.io/";
                _paq.push(['setTrackerUrl', u+'piwik.php']);
                _paq.push(['setSiteId', "{config name='makaira_tracking_page_id' namespace='MakairaConnect'}"]);
                var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
                g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
    {/if}
{/block}