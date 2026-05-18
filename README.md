# Lockstation_AccountLinksManager

Admin-configurable module that lets you hide unwanted links from the customer **My Account** sidebar without editing any templates or layout XML.

Replicates the behaviour of MagePal's "Customer Dashboard Links Manager", with the same configuration model: pick which links to manage, choose whether to *hide* them or *show only* them, save — done.

## Features

| | |
|---|---|
| Hide individual customer dashboard links | ✓ |
| Inverse mode (show only the picked ones, hide everything else) | ✓ |
| Configure entirely from Magento Admin — zero coding | ✓ |
| Works on standard Magento + Adobe Commerce + Hyvä themes | ✓ |
| Supports per-store-view configuration | ✓ |
| Supports custom extension links via the "Extra block names" textarea | ✓ |
| Doesn't change any templates or layout XML on disk | ✓ |
| Doesn't add any frontend JS or CSS | ✓ |

## Compatibility

| Platform | Status |
|---|---|
| Magento Open Source 2.4.4 – 2.4.8 | ✓ |
| Adobe Commerce 2.4.4 – 2.4.8 | ✓ (includes AC-only links: Reward Points, Gift Card, Gift Registries, RMA, Store Credit, Recurring Payments) |
| Hyvä themes (any version) | ✓ (Hyvä keeps the same `Magento\Customer\Block\Account\Navigation` block class, just re-skins the template — the plugin works unchanged) |
| PHP 8.1 / 8.2 / 8.3 / 8.4 | ✓ |

## Installation

### Option A — Composer

```bash
composer require lockstation/module-account-links-manager:^1.0
bin/magento module:enable Lockstation_AccountLinksManager
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento cache:flush
```

### Option B — Manual drop-in

Copy the `Lockstation_AccountLinksManager` folder into `app/code/Lockstation/AccountLinksManager/`, then:

```bash
bin/magento module:enable Lockstation_AccountLinksManager
bin/magento setup:upgrade
bin/magento setup:di:compile      # production mode only
bin/magento cache:flush
```

No database tables are created — settings live in `core_config_data`.

## Configuration

**Admin → Stores → Configuration → Lockstation → Customer Dashboard Links Manager**

| Field | Description |
|---|---|
| **Enabled** | Master switch — turns the filtering on/off without uninstalling the module. |
| **Action** | • *Hide selected links* — every link you pick is hidden, the rest stays visible.<br/>• *Show only selected links* — only the links you pick are visible, everything else is hidden. |
| **Links** | Multi-select of the standard Magento + Adobe Commerce customer-account-navigation links. Pick whichever you want to manage. |
| **Extra block names** | Newline-separated list of layout block names for links added by third-party extensions (e.g. `customer-account-navigation-magefan-blog-comments-link`). |

Per-store-view configuration is supported — the same Hide/Show config can differ between Default Store View, French View, etc.

## How it works

1. The module registers an admin config section under **Lockstation → Customer Dashboard Links Manager**.
2. When a customer-facing page renders the navigation block, our plugin (`Plugin/NavigationPlugin.php`) intercepts the block right before it outputs HTML.
3. The plugin iterates the navigation's child link blocks. For each one, it asks: *"Should this link be removed?"* — based on the admin's selected mode and link list.
4. Hidden links are removed via `Layout::unsetChild($parent, $childName)` — the same mechanism Magento itself uses for `<referenceBlock remove="true"/>` in layout XML.
5. The customer sees the cleaned-up sidebar.

No HTML/CSS is rewritten — the link element is simply never rendered. The result is identical to writing `<referenceBlock name="customer-account-navigation-orders-link" remove="true"/>` in a layout XML file, except it's runtime-configurable from admin.

## Why this is Hyvä-safe

Hyvä themes replace the storefront's CSS framework, JS, and templates — but they don't change the PHP block class names. The customer-account sidebar on Hyvä is still rendered by `Magento\Customer\Block\Account\Navigation` (which extends `Magento\Framework\View\Element\Html\Links`).

Our plugin hooks the parent class `Magento\Framework\View\Element\Html\Links::beforeToHtml()` and guards by checking `getNameInLayout() === 'customer_account_navigation'`. This means:

- It works on vanilla Magento ✓
- It works on Hyvä's re-skinned navigation ✓
- It doesn't touch any *other* Links block on the storefront (footer links, etc.) ✓

The module ships zero frontend assets (no `view/frontend/web/css`, no `js`, no `phtml` overrides). Hyvä has nothing to "be incompatible with."

## Uninstall

```bash
bin/magento module:disable Lockstation_AccountLinksManager
bin/magento cache:flush
```

To remove completely:

```bash
# If Composer-installed
composer remove lockstation/module-account-links-manager

# If manual drop-in
rm -rf app/code/Lockstation/AccountLinksManager
bin/magento setup:upgrade
bin/magento cache:flush
```

Config entries in `core_config_data` are left behind but harmless — they're just unread XML paths after the module is gone. To purge them:

```sql
DELETE FROM core_config_data WHERE path LIKE 'lockstation_account_links/%';
```

## File layout

```
Lockstation_AccountLinksManager/
├── registration.php
├── composer.json
├── LICENSE
├── README.md
├── etc/
│   ├── module.xml
│   ├── acl.xml
│   ├── config.xml                              # default config values
│   ├── adminhtml/
│   │   └── system.xml                          # admin config UI
│   └── frontend/
│       └── di.xml                              # plugin registration (frontend only)
├── Model/
│   ├── Config.php                              # config reader
│   └── Source/
│       ├── Mode.php                            # Hide/Show option source
│       └── AvailableLinks.php                  # multiselect link names
├── Plugin/
│   └── NavigationPlugin.php                    # the filter (Links::beforeToHtml)
└── i18n/
    └── en_GB.csv
```

## License

MIT — see `LICENSE`.
