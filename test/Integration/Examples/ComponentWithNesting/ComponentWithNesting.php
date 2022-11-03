<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class TextWithImage extends BaseClass
{
    public function __construct(
        public readonly string $src,
        public readonly string $alt,
        public readonly string $title,
        public readonly string $text
    ) {
    }

    public function render(): string
    {
        return '<div class="text-with-image"><img class="image" src="' . $this->src . '" alt="' . $this->alt . '" title="' . $this->title . '" /><p class="text">' . $this->text . '</p></div>';
    }
}
