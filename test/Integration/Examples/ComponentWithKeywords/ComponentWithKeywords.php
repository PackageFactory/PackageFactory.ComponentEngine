<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class ComponentWithKeywords extends BaseClass
{
    public function render(): string
    {
        return '<div>Keywords like import or export or component are allowed in here.</div>';
    }
}
