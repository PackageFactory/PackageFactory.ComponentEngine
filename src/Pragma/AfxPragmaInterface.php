<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Pragma;

interface AfxPragmaInterface
{
    /**
     * @param mixed $constructor
     * @param array $props
     * @param array $children
     * @return mixed
     */
    public function createElement($constructor, array $props, array $children);

    /**
     * @param array $children
     * @return mixed
     */
    public function createFragment(array $children);
}
