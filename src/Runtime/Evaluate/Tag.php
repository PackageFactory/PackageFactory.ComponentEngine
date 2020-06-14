<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluate;

use PackageFactory\ComponentEngine\Parser\Ast;
use PackageFactory\ComponentEngine\Runtime\Context;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM;

final class Tag
{
    public static function evaluate(Context $context, Ast\Tag $tag): ?VirtualDOM\Node
    {
        if ($tag->getTagName()->getNameSpace() === 'c') {
            if ($tag->getTagName()->getValue() === 'c:fragment') {
                return self::evaluateElement($context, $tag);
            }
            else {
                throw new \RuntimeException('@TODO: Unknown special tag ' . $tag->getTagName());
            }
        }
        elseif ($tag->getTagName()->getNameSpace() === null) {
            if (ctype_upper($tag->getTagName()->getValue()[0])) {
                return self::evaluateComponent($context, $tag);
            }
            else {
                return self::evaluateElement($context, $tag);
            }
        }
        else {
            throw new \RuntimeException('@TODO: Unknown Tag Namespace');
        }
    }

    public static function evaluateComponent(
        Context $context, 
        Ast\Tag $tag,
        bool &$conditionalContext = null
    ): ?VirtualDOM\Node {
        $component = $context->getProperty((string) $tag->getTagName());
        if (!($component instanceof Runtime)) {
            throw new \Exception('@TODO: Invalid component');
        }

        $props = [];
        $iteration = null;
        $condition = null;
        foreach ($tag->getAttributes() as $astAttribute) {
            if ($astAttribute->getName()->getNameSpace() === 'c') {
                if ($astAttribute->getName()->getValue() === 'c:map') {
                    $iteration = Iteration::evaluate($context, $astAttribute);
                }
                elseif ($astAttribute->getName()->getValue() === 'c:if') {
                    if ($astAttribute->getValue() instanceof Ast\Expression) {
                        $condition = Expression::evaluate($context, $astAttribute->getValue());
                    }
                    else {
                        throw new \RuntimeException('@TODO: Ivalid attribute value for c:if');
                    }
                }
                else {
                    throw new \RuntimeException('@TODO: Unknown c-attribute');
                }
            }
            elseif ($astAttribute->getName()->getNameSpace() === null) {
                $prop = Prop::evaluate($context, $astAttribute);
                $props[(string) $astAttribute->getName()] = $prop;
            }
            else {
                throw new \RuntimeException('@TODO: Unknown attribute namespace ' . $astAttribute->getName()->getNameSpace());
            }
        }

        if ($condition !== null) {
            $conditionalContext = $condition;

            if (!$condition) {
                return null;
            }
        }

        if ($iteration === null) {
            $children = self::evaluateChildren($context, $tag);
        }
        else {
            $children = [];
            foreach ($iteration as $iterationContext) {
                foreach (self::evaluateChildren($iterationContext, $tag) as $child) {
                    $children[] = $child;
                }
            }
        }

        $props['children'] = $children;

        return $component->evaluate(Context::createFromArray([
            'props' => $props
        ]));
    }

    public static function evaluateElement(
        Context $context, 
        Ast\Tag $tag,
        bool &$conditionalContext = null
    ): ?VirtualDOM\Node {
        $attributes = [];
        $iteration = null;
        $condition = null;
        foreach ($tag->getAttributes() as $astAttribute) {
            if ($astAttribute->getName()->getNameSpace() === 'c') {
                if ($astAttribute->getName()->getValue() === 'c:map') {
                    $iteration = Iteration::evaluate($context, $astAttribute);
                }
                elseif ($astAttribute->getName()->getValue() === 'c:if') {
                    if ($astAttribute->getValue() instanceof Ast\Expression) {
                        $condition = Expression::evaluate($context, $astAttribute->getValue());
                    }
                    else {
                        throw new \RuntimeException('@TODO: Ivalid attribute value for c:if');
                    }
                }
                else {
                    throw new \RuntimeException('@TODO: Unknown c-attribute');
                }
            }
            elseif ($astAttribute->getName()->getNameSpace() === null) {
                if ($attribute = Attribute::evaluate($context, $astAttribute)) {
                    $attributes[] = $attribute;
                }
            }
            else {
                throw new \RuntimeException('@TODO: Unknown attribute namespace ' . $astAttribute->getName()->getNameSpace());
            }
        }

        if ($condition !== null) {
            $conditionalContext = $condition;

            if (!$condition) {
                return null;
            }
        }

        if ($iteration === null) {
            $children = self::evaluateChildren($context, $tag);
        }
        else {
            $children = [];
            foreach ($iteration as $iterationContext) {
                foreach (self::evaluateChildren($iterationContext, $tag) as $child) {
                    $children[] = $child;
                }
            }
        }

        if ($tag->getTagName()->getValue() === 'c:fragment') {
            return VirtualDOM\Fragment::create(
                ...$children
            );
        }
        else {
            return VirtualDOM\Element::create(
                VirtualDOM\ElementType::createFromTagName((string) $tag->getTagName()),
                VirtualDOM\Attributes::createFromArray($attributes),
                VirtualDOM\NodeList::create(...$children)
            );
        }
    }

    /**
     * @param Context $context
     * @param Ast\Tag $tag
     * @return array<int, VirtualDOM\Node>
     */
    public static function evaluateChildren(Context $context, Ast\Tag $tag): array
    {
        $children = [];
        foreach ($tag->getChildren() as $astChild) {
            if ($astChild instanceof Ast\Content) {
                $children[] = VirtualDom\Text::createFromString(
                    $astChild->getValue()
                );
            }
            elseif ($astChild instanceof Ast\Expression) {
                $values = Expression::evaluate($context, $astChild);
                if (!is_array($values)) {
                    $values = [$values];
                }

                foreach ($values as $value) {
                    if (is_string($value)) {
                        $children[] = VirtualDOM\Text::createFromString($value);
                    }
                    elseif (is_numeric($value)) {
                        $children[] = VirtualDOM\Text::createFromString((string) $value);
                    }
                    elseif (is_null($value)) {
                        // Ignore
                    }
                    elseif ($value instanceof VirtualDOM\Node) {
                        $children[] = $value;
                    }
                    else {
                        throw new \RuntimeException('@TODO: Invalid child expression result');
                    }
                }
            }
            elseif ($astChild instanceof Ast\Tag) {
                if ($content = self::evaluate($context, $astChild)) {
                    $children[] = $content;
                }
            }
            else {
                throw new \RuntimeException('@TODO: Invalid child');
            }
        }

        return $children;
    }
}