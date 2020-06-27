<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\VirtualDOM;
use PackageFactory\ComponentEngine\Evaluation\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\AttributeName;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\TagName;
use PackageFactory\ComponentEngine\Parser\Ast\Expression\Spread;

/**
 * @implements AfxEvaluatorInterface<VirtualDOM\Node>
 */
final class AfxEvaluator implements AfxEvaluatorInterface
{
    /**
     * @var ExpressionEvaluator
     */
    private $expressionEvaluator;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param ExpressionEvaluator $expressionEvaluator
     * @param Context $context
     * @return void
     */
    private function __construct(
        ExpressionEvaluator $expressionEvaluator,
        Context $context = null
    ) {
        $this->expressionEvaluator = $expressionEvaluator;
        $this->context = $context ?? Context::createEmpty();
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self(ExpressionEvaluator::default());
    }

    /**
     * @param Context $context
     * @return self
     */
    public function withContext(Context $context): self
    {
        return new self($this->expressionEvaluator, $context);
    }

    /**
     * @param Tag $root
     * @return VirtualDOM\Node
     */
    public function evaluate(Tag $root)
    {
        $context = $this->context;
        return $this->onTag($root, $context);
    }

    /**
     * @param AttributeName $attributeName
     * @return string
     */
    public function onAttributeName(AttributeName $attributeName): string
    {
        return $attributeName->getValue();
    }

    /**
     * @param Attribute $attribute
     * @param Context $context
     * @return \Iterator<string, VirtualDOM\Attribute>
     */
    public function onAttribute(Attribute $attribute, Context $context): \Iterator
    {
        $name = $this->onAttributeName($attribute->getAttributeName());
        $value = $attribute->getValue();

        if (is_bool($value)) {
            yield $name => VirtualDOM\Attribute::createBooleanFromName($name);
        } else {
            yield $name => VirtualDOM\Attribute::createFromNameAndValue(
                $name,
                $this->expressionEvaluator->withContext($context)->evaluate($value)
            );
        }
    }

    /**
     * @param Content $content
     * @return VirtualDOM\Text
     */
    public function onContent(Content $content): VirtualDOM\Text
    {
        return VirtualDOM\Text::createFromString($content->getValue());
    }

    /**
     * @param TagName $tagName
     * @return string
     */
    public function onTagName(TagName $tagName): string
    {
        throw new \Exception('@TODO: AfxEvaluator->onTagName() is not implemented yet');
    }

    /**
     * @param Tag $tag
     * @param Context $context
     * @return VirtualDOM\Node
     */
    public function onTag(Tag $tag, Context $context): VirtualDOM\Node
    {
        if ($tag->getIsFragment()) {
            return $this->onFragment($tag, $context);
        } else {
            $attributes = [];
            foreach ($tag->getAttributes() as $attribute) {
                if ($attribute instanceof Attribute) {
                    foreach ($this->onAttribute($attribute, $context) as $key => $value) {
                        $attributes[] = $value;
                    }
                } elseif ($attribute instanceof Spread) {
                    foreach ($this->expressionEvaluator->withContext($context)->onSpread($attribute, $context) as $key => $value) {
                        $attributes[] = $value;
                    }
                }
            }

            $children = [];
            foreach ($tag->getChildren() as $child) {
                $value = $this->onChild($child, $context);

                if ($value !== null) {
                    $children[] = $value;
                }
            }

            return VirtualDOM\Element::create(
                VirtualDOM\ElementType::createFromTagName($tag->getTagName()->getValue()),
                VirtualDOM\Attributes::createFromArray($attributes),
                VirtualDOM\NodeList::create(...$children)
            );
        }
    }

    /**
     * @param Tag $tag
     * @param Context $context
     * @return VirtualDOM\Node
     */
    public function onFragment(Tag $tag, Context $context): VirtualDOM\Node
    {
        $children = [];
        foreach ($tag->getChildren() as $child) {
            $value = $this->onChild($child, $context);

            if ($value !== null) {
                $children[] = $value;
            }
        }
        
        return VirtualDOM\Fragment::create(...$children);
    }

    /**
     * @param Content|Tag|Operand $child
     * @param Context $context
     * @return VirtualDOM\Node
     */
    public function onChild($child, Context $context): ?VirtualDOM\Node
    {
        if ($child instanceof Content) {
            return $this->onContent($child);
        } elseif ($child instanceof Tag) {
            return $this->onTag($child, $context);
        } else {
            $result = $this->expressionEvaluator->withContext($context)->evaluate($child);
            if (is_string($result)) {
                return VirtualDOM\Text::createFromString($result);
            } elseif (is_null($result)) {
                return $result;
            } elseif ($result instanceof VirtualDOM\Node) {
                return $result;
            } else {
                throw new \Exception('@TODO: Cannot render child node of type ' . gettype($result));
            }
        }
    }
}