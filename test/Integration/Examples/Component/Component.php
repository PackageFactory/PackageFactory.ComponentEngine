<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Hyperscript;

final class Component
{
    public function __construct(
        private readonly string $src,
        private readonly string $alt,
        private readonly string $title
    ) {
    }

    public function render(): string
    {
        return Hyperscript::h(
            "img",
            Hyperscript::attributes(
                Hyperscript::attribute("src", $this->src),
                Hyperscript::attribute("alt", $this->alt),
                Hyperscript::attribute("title", $this->title)
            ),
            Hyperscript::children()
        );
    }
}
