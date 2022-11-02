<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Hyperscript;
use Vendor\Project\Component\ButtonType;
use Vendor\Project\Component\Slot;

final class Button extends BaseClass
{
    public function __construct(
        private readonly ButtonType $type,
        private readonly Slot $content
    ) {
    }

    public function render(): string
    {
        return match ($this->type) {
            ButtonType::LINK => Hyperscript::tag(
                'a',
                Hyperscript::attributes(
                    Hyperscript::attribute('class', 'btn'),
                    Hyperscript::attribute('href', '#')
                ),
                Hyperscript::children(
                    $this->content->render()
                )
            ),
            ButtonType::BUTTON,
            ButtonType::SUBMIT => Hyperscript::tag(
                'button',
                Hyperscript::attributes(
                    Hyperscript::attribute('class', 'btn'),
                    Hyperscript::attribute('type', match ($this->type) {
                        ButtonType::SUBMIT => 'submit',
                        default => 'button'
                    })
                ),
                Hyperscript::children(
                    $this->content->render()
                )
            ),
            ButtonType::NONE => Hyperscript::tag(
                'div',
                Hyperscript::attributes(
                    Hyperscript::attribute('class', 'btn')
                ),
                Hyperscript::children(
                    $this->content->render()
                )
            )
        };
    }
}
