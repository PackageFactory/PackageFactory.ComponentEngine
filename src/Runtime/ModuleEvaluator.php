<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\VirtualDOM\Node as VirtualDOMNode;
use PackageFactory\ComponentEngine\Evaluation\AfxEvaluatorInterface;
use PackageFactory\ComponentEngine\Evaluation\ExpressionEvaluatorInterface;
use PackageFactory\ComponentEngine\Evaluation\ModuleEvaluatorInterface;
use PackageFactory\ComponentEngine\Loader\LoaderInterface;
use PackageFactory\ComponentEngine\Loader\RootLoader;
use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Constant;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Export;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Import;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;


/**
 * @implements ModuleEvaluatorInterface<VirtualDOMNode|string|array<mixed>|object|bool|float|null>
 */
final class ModuleEvaluator implements ModuleEvaluatorInterface
{
    /**
     * @var ExpressionEvaluator
     */
    private $expressionEvaluator;

    /**
     * @var AfxEvaluator
     */
    private $afxEvaluator;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param LoaderInterface $loader
     * @param Context $context
     * @return void
     */
    private function __construct(
        LoaderInterface $loader,
        Context $context
    ) {
        $this->expressionEvaluator = ExpressionEvaluator::default();
        $this->afxEvaluator = AfxEvaluator::default();
        $this->loader = $loader;
        $this->context = $context;
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self(
            RootLoader::createFromConfiguration([]),
            Context::createEmpty()
        );
    }

    /**
     * @param Context $context
     * @return self
     */
    public function withContext(Context $context): self
    {
        return new self($this->loader, $context);
    }

    /**
     * @param Module $module
     * @return VirtualDOMNode|string|array<mixed>|object|bool|float|null
     */
    public function evaluate(Module $module)
    {
        return $this->evaluateExport($module, 'default');
    }

    /**
     * @param Module $module
     * @param string $export
     * @return VirtualDOMNode|string|array<mixed>|object|bool|float|null
     */
    public function evaluateExport(Module $module, string $export)
    {
        $export = $module->getExport($export);
        $context = $this->context;

        foreach ($module->getConstants() as $constant) {
            $context = $context->withMergedProperties([
                (string) $constant->getName() =>
                    $this->onConstant($constant, $context)
            ]);
        }

        return $this->onExport($export, $context);
    }

    /**
     * @param Import $import
     * @return VirtualDOMNode|string|array<mixed>|object|bool|float|null
     */
    public function onImport(Import $import)
    {
        throw new \Exception('@TODO: ModuleEvaluator->onImport() is not implemented yet');
    }

    /**
     * @param Export $export
     * @param Context $context
     * @return VirtualDOMNode|string|array<mixed>|object|bool|float|null
     */
    public function onExport(Export $export, Context $context)
    {
        $value = $export->getValue();

        if ($value instanceof Tag) {
            return $this->afxEvaluator->withContext($context)->evaluate($value);
        } else {
            return $this->expressionEvaluator->withContext($context)->evaluate($value);
        }
    }

    /**
     * @param Constant $constant
     * @param Context $context
     * @return VirtualDOMNode|string|array<mixed>|object|bool|float|null
     */
    public function onConstant(Constant $constant, Context $context)
    {
        $value = $constant->getValue();

        if ($value instanceof Tag) {
            return $this->afxEvaluator->withContext($context)->evaluate($value);
        } else {
            return $this->expressionEvaluator->withContext($context)->evaluate($value);
        }
    }
}