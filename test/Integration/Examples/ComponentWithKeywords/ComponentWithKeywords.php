<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Hyperscript;

final class ComponentWithKeywords extends BaseClass
{
    public function render(): string
    {
        return Hyperscript::tag(
            'div',
            Hyperscript::attributes(),
            Hyperscript::children(
                Hyperscript::text('Keywords like import or export or component are allowed in here.')
            )
        );
    }
}
