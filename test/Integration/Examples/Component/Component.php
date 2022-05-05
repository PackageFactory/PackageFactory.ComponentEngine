<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\HyperScript;

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
        return HyperScript::h(
            "img",
            HyperScript::attributes(
                HyperScript::attribute("src", $this->src),
                HyperScript::attribute("alt", $this->alt),
                HyperScript::attribute("title", $this->title)
            ),
            HyperScript::children()
        );
    }
}
