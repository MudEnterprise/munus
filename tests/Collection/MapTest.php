<?php

declare(strict_types=1);

namespace Munus\Tests\Collection;

use Munus\Collection\GenericList;
use Munus\Collection\Map;
use Munus\Collection\Set;
use Munus\Collection\Stream\Collectors;
use Munus\Control\Option;
use Munus\Exception\NoSuchElementException;
use Munus\Tuple;
use PHPUnit\Framework\TestCase;

final class MapTest extends TestCase
{
    public function testMapPutAndGet(): void
    {
        $map = Map::empty();
        $map = $map->put('munus', 'is awesome');

        self::assertEquals(Option::of('is awesome'), $map->get('munus'));
        self::assertEquals(Option::none(), $map->get('wrong-key'));
    }

    public function testMapRemove(): void
    {
        $map = Map::fromArray(['some' => 'value']);

        self::assertEquals(Option::none(), $map->remove('some')->get('some'));
        self::assertSame($map, $map->remove('not-existing-key'));
        self::assertNotSame($map, $map->remove('some'));
    }

    public function testMapGetWithoutArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Map::fromArray(['a' => 'b'])->get();
    }

    public function testMapImmutability(): void
    {
        $map = Map::fromArray(['munus' => 'is awesome']);
        $newMap = $map->put('munus2', 'is better');

        self::assertNotSame($map, $newMap);
        self::assertEquals(Option::none(), $map->get('munus2'));
    }

    public function testMapLength(): void
    {
        $map = Map::fromArray(['munus' => 'is awesome']);
        self::assertEquals(1, $map->length());
        self::assertEquals(2, $map->put('php', 'is awesome')->length());
    }

    public function testMapHead(): void
    {
        self::assertTrue(Map::fromArray(['a' => 'b', 'c' => 'd', 'e' => 'f'])->head()->equals(Tuple::of('a', 'b')));
        self::assertTrue(Map::fromArray(['e' => 'f', 'a' => 'b'])->head()->equals(Tuple::of('e', 'f')));

        $this->expectException(NoSuchElementException::class);
        Map::empty()->head();
    }

    public function testMapTail(): void
    {
        self::assertTrue(Map::fromArray(['a' => 'b', 'c' => 'd', 'e' => 'f'])->tail()->equals(Tuple::of('e', 'f')));
        self::assertTrue(Map::fromArray(['e' => 'f', 'a' => 'b'])->tail()->equals(Tuple::of('a', 'b')));

        $this->expectException(NoSuchElementException::class);
        Map::empty()->tail();
    }

    public function testIsEmpty(): void
    {
        self::assertTrue(Map::empty()->isEmpty());
        self::assertFalse(Map::empty()->put('some', 'value')->isEmpty());
        self::assertTrue(Map::fromArray(['some' => 'value'])->remove('some')->isEmpty());
    }

    public function testMapTake(): void
    {
        $map = Map::fromArray(['a' => 'apple', 'b' => 'banana', '42' => 'pear', 'd' => 'orange']);

        self::assertTrue(Map::fromArray(['a' => 'apple', 'b' => 'banana'])->equals($map->take(2)));
        self::assertTrue(Map::fromArray(['a' => 'apple'])->equals($map->take(1)));
        self::assertTrue($map->equals($map->take(4)));
        self::assertSame($map, $map->take(5));
        self::assertNotSame($map, $map->take(3));
    }

    public function testMapFilter(): void
    {
        $map = Map::fromArray(['a' => 'apple', 'b' => 'banana', '42' => 'pear', 'd' => 'orange']);

        self::assertTrue(Map::fromArray(['a' => 'apple'])->equals(
            $map->filter(function ($entry) {return $entry[1] === 'apple'; })
        ));

        self::assertTrue(Map::fromArray(['42' => 'pear'])->equals(
            $map->filter(function ($entry) {return is_numeric($entry[0]); })
        ));

        self::assertNotSame($map, $map->filter(function () {return true; }));
    }

    public function testMapMap(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue(Map::fromArray(['A' => 'B', 'C' => 'D'])->equals(
            $map->map(function ($entry) {return $entry->map('strtoupper'); })
        ));
        self::assertTrue(Map::fromArray(['b' => 'c', 'd' => 'e'])->equals(
            $map->map(function ($entry) {return $entry->map(function ($v) {return ++$v; }); })
        ));

        self::assertNotSame($map, $map->map(function ($entry) {return $entry; }));
    }

    public function testMapMapKeys(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue(Map::fromArray(['A' => 'b', 'C' => 'd'])->equals(
            $map->mapKeys('strtoupper')
        ));

        self::assertNotSame($map, $map->mapKeys('ucfirst'));
    }

    public function testMapMapValues(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue(Map::fromArray(['a' => 'B', 'c' => 'D'])->equals(
            $map->mapValues('strtoupper')
        ));

        self::assertNotSame($map, $map->mapValues('ucfirst'));
    }

    public function testMapContains(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue($map->contains(Tuple::of('a', 'b')));
        self::assertFalse($map->contains(Tuple::of('a', 'c')));
    }

    public function testMapContainsKey(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue($map->containsKey('a'));
        self::assertFalse($map->containsKey('b'));
    }

    public function testMapContainsValue(): void
    {
        $map = Map::fromArray(['a' => 'b', 'c' => 'd', 'e' => Option::of('munus')]);

        self::assertTrue($map->containsValue('d'));
        self::assertFalse($map->containsValue('a'));
        self::assertTrue($map->containsValue('munus'));
    }

    public function testMapValues(): void
    {
        self::assertEquals(['b', 'd'], Map::fromArray(['a' => 'b', 'c' => 'd'])->values());
    }

    public function testMapKeys(): void
    {
        self::assertTrue(Set::ofAll(['a', 'b', 'c'])->equals(Map::fromArray(['a' => '1', 'b' => '2', 'c' => '3'])->keys()));
    }

    public function testMapMerge(): void
    {
        $merged = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue($merged->equals(Map::fromArray(['a' => 'b'])->merge(Map::fromArray(['c' => 'd']))));
        self::assertSame($merged, $merged->merge(Map::empty()));
        self::assertSame($merged, Map::empty()->merge($merged));
    }

    public function testMapMergeAndDropKeyOnConflict(): void
    {
        $merged = Map::fromArray(['a' => 'b', 'c' => 'd']);

        self::assertTrue($merged->equals(Map::fromArray(['a' => 'b'])->merge(
            Map::fromArray(['a' => 'conflict', 'c' => 'd'])
        )));
    }

    public function testMapCollect(): void
    {
        self::assertTrue(GenericList::of('b', 'd')->equals(
            Map::fromArray(['a' => 'b', 'c' => 'd'])->collect(Collectors::toList())
        ));
        self::assertTrue(GenericList::empty()->equals(Map::empty()->collect(Collectors::toList())));
    }
}
