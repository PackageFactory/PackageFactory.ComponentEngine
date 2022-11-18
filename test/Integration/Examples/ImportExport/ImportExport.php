<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Component\Link;
use Vendor\Project\Component\Button;

final class Card extends BaseClass
{
    public function __construct(
        public readonly string $title,
        public readonly LinkStruct $link,
        public readonly ButtonComponent $button
    ) {
    }

    public function render(): string
    {
        return '<div class="card"><a href="' . $this->link->href . '" target="' . $this->link->target . '">' . $this->title . '</a>' . $this->button->render() . '</div>';
    }
}
