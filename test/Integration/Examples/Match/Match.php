<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Component\ButtonType;

final class Button extends BaseClass
{
    public function __construct(
        public readonly ButtonTypeEnum $type,
        public readonly SlotInterface $content
    ) {
    }

    public function render(): string
    {
        return match ($this->type) { ButtonType::LINK => '<a class="btn" href="#">' . $this->content . '</a>', ButtonType::BUTTON, ButtonType::SUBMIT => '<button class="btn" type="' . match ($this->type) { ButtonType::SUBMIT => 'submit', default => 'button' } . '">' . $this->content . '</button>', ButtonType::NONE => '<div class="btn">' . $this->content . '</div>' };
    }
}
