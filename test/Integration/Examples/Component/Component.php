<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;

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
        return '<img src="' . $this->src . '" alt="' . $this->alt . '" title="' . $this->title . '" />';
    }
}
