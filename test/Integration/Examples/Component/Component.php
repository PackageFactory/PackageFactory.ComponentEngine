<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Hyperscript;

final class Image extends BaseClass
{
    public function __construct(
        public readonly string $src,
        public readonly string $alt,
        public readonly string $title
    ) {
    }

    public function render(): string
    {
        return Hyperscript::tag(
            'img',
            Hyperscript::attributes(
                Hyperscript::attribute('src', $this->src),
                Hyperscript::attribute('alt', $this->alt),
                Hyperscript::attribute('title', $this->title)
            ),
            Hyperscript::children(
            )
        );
    }
}
