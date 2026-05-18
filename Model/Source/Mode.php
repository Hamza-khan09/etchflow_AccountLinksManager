<?php
declare(strict_types=1);

namespace Etechflow\AccountLinksManager\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    public const HIDE_SELECTED = 'hide_selected';
    public const SHOW_ONLY     = 'show_only';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::HIDE_SELECTED, 'label' => __('Hide selected links')],
            ['value' => self::SHOW_ONLY,     'label' => __('Show only selected links')],
        ];
    }
}
