<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\VirtualDOM\Node as VirtualDOMNode;
use PackageFactory\ComponentEngine\Evaluation\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Evaluation\ExpressionEvaluatorInterface;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Attribute;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\AttributeName;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Content;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\TagName;

/**
 * @implements AfxEvaluatorInterface<VirtualDOMNode>
 */
final class AfxEvaluator implements AfxEvaluatorInterface
{
    /**
     * @var ExpressionEvaluatorInterface
     */
    private $expressionEvaluator;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param ExpressionEvaluatorInterface $expressionEvaluator
     * @param Context $context
     * @return void
     */
    private function __construct(
        ExpressionEvaluatorInterface $expressionEvaluator,
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
     * @return VirtualDOMNode
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
        throw new \Exception('@TODO: AfxEvaluator->onAttributeName() is not implemented yet');
    }

    /**
     * @param Attribute $attribute
     * @param Context $context
     * @return array<string, mixed>
     */
    public function onAttribute(Attribute $attribute, Context $context): array
    {
        throw new \Exception('@TODO: AfxEvaluator->onAttribute() is not implemented yet');
    }

    /**
     * @param Content $content
     * @return string
     */
    public function onContent(Content $content): string
    {
        throw new \Exception('@TODO: AfxEvaluator->onContent() is not implemented yet');
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
     * @return VirtualDOMNode
     */
    public function onTag(Tag $tag, Context $context): VirtualDOMNode
    {
        throw new \Exception('@TODO: AfxEvaluator->onTag() is not implemented yet');
    }
}