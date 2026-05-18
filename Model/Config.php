<?php
declare(strict_types=1);

namespace Etechflow\AccountLinksManager\Model;

use Etechflow\AccountLinksManager\Model\Source\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_ENABLED          = 'etechflow_account_links/general/enabled';
    public const XML_MODE             = 'etechflow_account_links/general/mode';
    public const XML_HIDDEN_LINKS     = 'etechflow_account_links/general/hidden_links';
    public const XML_EXTRA_BLOCKS     = 'etechflow_account_links/general/extra_block_names';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMode(?int $storeId = null): string
    {
        $val = (string) $this->scopeConfig->getValue(self::XML_MODE, ScopeInterface::SCOPE_STORE, $storeId);
        return $val ?: Mode::HIDE_SELECTED;
    }

    /**
     * @return string[] List of layout block names the admin configured.
     */
    public function getManagedBlockNames(?int $storeId = null): array
    {
        $raw   = (string) $this->scopeConfig->getValue(self::XML_HIDDEN_LINKS, ScopeInterface::SCOPE_STORE, $storeId);
        $extra = (string) $this->scopeConfig->getValue(self::XML_EXTRA_BLOCKS, ScopeInterface::SCOPE_STORE, $storeId);

        $selected = array_filter(array_map('trim', explode(',', $raw)));
        $custom   = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $extra)));

        return array_values(array_unique(array_merge($selected, $custom)));
    }
}
