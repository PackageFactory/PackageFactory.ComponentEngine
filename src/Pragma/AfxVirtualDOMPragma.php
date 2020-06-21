<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Pragma;

use PackageFactory\VirtualDOM\Attributes;
use PackageFactory\VirtualDOM\Element;
use PackageFactory\VirtualDOM\ElementType;
use PackageFactory\VirtualDOM\Fragment;
use PackageFactory\VirtualDOM\Node;
use PackageFactory\VirtualDOM\NodeList;
use PackageFactory\VirtualDOM\Text;

final class AfxVirtualDOMPragma implements AfxPragmaInterface
{
    private function __construct() {}

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param mixed $constructor
     * @param array<mixed> $props
     * @param array<mixed> $children
     * @return mixed
     */
    public function createElement($constructor, array $props, array $children)
    {
        $nodes = $this->getNodesFromChildren($children);

        if (is_string($constructor)) {
            return Element::create(
                ElementType::createFromTagName($constructor),
                Attributes::createFromJsonArray($props),
                NodeList::create(...$nodes)
            );
        } else {
            throw new \Exception('@TODO: AfxVirtualDOMPragma::createElement');
        }
    }

    /**
     * @param array<mixed> $children
     * @return mixed
     */
    public function createFragment(array $children)
    {
        $nodes = $this->getNodesFromChildren($children);
        return Fragment::create(...$nodes);
    }

    /**
     * @param array<mixed> $children
     * @return array|Node[]
     */
    public function getNodesFromChildren(array $children): array
    {
        $nodes = [];
        foreach ($children as $child) {
            if (is_string($child)) {
                $nodes[] = Text::createFromString($child);
            } elseif ($child instanceof Node) {
                $nodes[] = $child;
            } elseif (is_array($child)) {
                foreach ($this->getNodesFromChildren($child) as $node) {
                    $nodes[] = $node;
                }
            } else {
                var_dump($child);
                throw new \Exception('@TODO: AfxVirtualDOMPragma::getNodesFromChildren');
            }
        }

        return $nodes;
    }
}