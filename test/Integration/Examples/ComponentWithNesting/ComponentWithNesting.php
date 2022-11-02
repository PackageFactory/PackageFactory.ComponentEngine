<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

final class TextWithImage extends BaseClass
{
    public function __construct(
        private readonly string $src,
        private readonly string $alt,
        private readonly string $title,
        private readonly string $text
    ) {
    }

    public function render(): string
    {
        return '<div class="text-with-image">'
            . '<img class="image" src={src} alt={alt} title={title} />'
            . '<p class="text">'
            . $this->text
            . '</p>'
            . '</div>';
    }
}
