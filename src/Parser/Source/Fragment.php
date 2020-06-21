<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Source;

final class Fragment
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var Position
     */
    private $start;

    /**
     * @var Position
     */
    private $end;

    /**
     * @var Source
     */
    private $source;

    /**
     * @param string $value
     * @param Position $start
     * @param Position $end
     * @param Source $source
     */
    private function __construct(
        string $value,
        Position $start,
        Position $end,
        Source $source
    ) {
        $this->value = $value;
        $this->start = $start;
        $this->end = $end;
        $this->source = $source;
    }

    /**
     * @param string $value
     * @param Position $start
     * @param Position $end
     * @param Source $source
     * @return Fragment
     */
    public static function create(
        string $value,
        Position $start,
        Position $end,
        Source $source
    ): Fragment {
        return new Fragment(
            $value,
            $start,
            $end,
            $source
        );
    }

    /**
     * @param Source $source
     * @return Fragment
     */
    public static function createEmpty(Source $source): Fragment 
    {
        return new Fragment(
            '',
            Position::create(0, 0, 0),
            Position::create(0, 0, 0),
            $source
        );
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return Position
     */
    public function getStart(): Position
    {
        return $this->start;
    }

    /**
     * @return Position
     */
    public function getEnd(): Position
    {
        return $this->end;
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Fragment $other
     * @return Fragment
     */
    public function append(Fragment $other): Fragment 
    {
        if ($this->getSource() !== $other->getSource()) {
            throw new \Exception('@TODO: Exception');
        }

        return new Fragment(
            $this->getValue() . $other->getValue(),
            $this->getStart(),
            $other->getEnd(),
            $this->getSource()
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}