<?php
declare(strict_types=1);

namespace Etechflow\AccountLinksManager\Plugin;

use Etechflow\AccountLinksManager\Model\Config;
use Etechflow\AccountLinksManager\Model\Source\Mode;
use Magento\Framework\View\Element\Html\Links;

/**
 * Removes selected children from the customer "My Account" sidebar before
 * the block renders. Works identically on:
 *   - Magento Open Source 2.4+ (default Luma/Blank themes)
 *   - Adobe Commerce 2.4+
 *   - Hyva-themed storefronts (Hyva re-skins the template but keeps the
 *     same Magento\Framework\View\Element\Html\Links block class and child
 *     block names)
 *
 * Implementation note: we hook on Links::beforeToHtml() (declared on the
 * Magento\Framework\View\Element\Template ancestor) and call
 * Layout::unsetChild($parent, $childName). That's the same mechanism layout
 * XML's <referenceBlock remove="true"/> uses — clean and safe.
 */
class NavigationPlugin
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function beforeToHtml(Links $subject): void
    {
        // Only the customer-account navigation list is relevant. Other Links
        // blocks (e.g. footer link lists) keep all their children intact.
        if ($subject->getNameInLayout() !== 'customer_account_navigation') {
            return;
        }
        if (! $this->config->isEnabled()) {
            return;
        }

        $managed = $this->config->getManagedBlockNames();
        if (! $managed) {
            return;
        }

        $mode   = $this->config->getMode();
        $layout = $subject->getLayout();
        if (! $layout) {
            return;
        }

        $parent = $subject->getNameInLayout();
        $children = $layout->getChildNames($parent);

        foreach ($children as $childName) {
            $isManaged = in_array($childName, $managed, true);

            $shouldRemove = ($mode === Mode::HIDE_SELECTED && $isManaged)
                || ($mode === Mode::SHOW_ONLY && ! $isManaged);

            if ($shouldRemove) {
                $layout->unsetChild($parent, $childName);
            }
        }
    }
}
