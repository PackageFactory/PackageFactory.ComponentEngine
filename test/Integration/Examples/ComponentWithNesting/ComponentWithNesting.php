<?php

declare(strict_types=1);

namespace Vendor\Project\Component;

use Vendor\Project\BaseClass;
use Vendor\Project\Std;
use Vendor\Project\Hyperscript;

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
        return Hyperscript::tag(
            'div',
            Hyperscript::attributes(
                Hyperscript::attribute('class', 'text-with-image'),
                Hyperscript::attribute('src', Std::string($this->src)),
                Hyperscript::attribute('alt', Std::string($this->alt)),
                Hyperscript::attribute('title', Std::string($this->title))
            ),
            Hyperscript::children(
                Hyperscript::tag(
                    'img',
                    Hyperscript::attributes(
                        Hyperscript::attribute('class', 'image'),
                        Hyperscript::attribute('src', Std::string($this->src)),
                        Hyperscript::attribute('alt', Std::string($this->alt)),
                        Hyperscript::attribute('title', Std::string($this->title))
                    ),
                    Hyperscript::children()
                ),
                Hyperscript::tag(
                    'p',
                    Hyperscript::attributes(
                        Hyperscript::attribute('class', 'text')
                    ),
                    Hyperscript::children(
                        Std::string($this->text)
                    )
                )
            )
        );
    }
}
