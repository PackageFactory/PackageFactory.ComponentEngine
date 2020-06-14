<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Source;

final class Position implements \JsonSerializable
{
    /**
     * @var int
     */
    private $index;

    /**
     * @var int
     */
    private $rowIndex;

    /**
     * @var int
     */
    private $columnIndex;

    /**
     * @param integer $index
     * @param integer $rowIndex
     * @param integer $columnIndex
     */
    private function __construct(
        int $index,
        int $rowIndex,
        int $columnIndex
    ) {
        $this->index = $index;
        $this->rowIndex = $rowIndex;
        $this->columnIndex = $columnIndex;
    }

    /**
     * @param integer $index
     * @param integer $rowIndex
     * @param integer $columnIndex
     * @return Position
     */
    public static function create(
        int $index,
        int $rowIndex,
        int $columnIndex
    ): Position {
        return new Position(
            $index,
            $rowIndex,
            $columnIndex
        );
    }

    /**
     * @return integer
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return integer
     */
    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    /**
     * @return integer
     */
    public function getColumnIndex(): int
    {
        return $this->columnIndex;
    }

    /**
     * @param Position $other
     * @return boolean
     */
    public function equals(Position $other): bool
    {
        return $this->getIndex() === $other->getIndex();
    }

    /**
     * @param Position $other
     * @return boolean
     */
    public function gt(Position $other): bool
    {
        return $this->getIndex() > $other->getIndex();
    }

    /**
     * @param Position $other
     * @return boolean
     */
    public function gte(Position $other): bool
    {
        return $this->gt($other) || $this->equals($other);
    }

    /**
     * @param Position $other
     * @return boolean
     */
    public function lt(Position $other): bool
    {
        return $this->getIndex() < $other->getIndex();
    }

    /**
     * @param Position $other
     * @return boolean
     */
    public function lte(Position $other): bool
    {
        return $this->lt($other) || $this->equals($other);
    }

    /**
     * @return int
     */
    public function jsonSerialize()
    {
        return $this->index;
    }
}