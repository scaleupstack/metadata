<?php declare(strict_types = 1);

/**
 * This file is part of ScaleUpStack/Metadata
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\Tests\PhpUnit\Metadata;

use ScaleUpStack\Annotations\Annotations;
use ScaleUpStack\Metadata\Metadata\PropertyMetadata;
use ScaleUpStack\Metadata\Tests\Resources\ClassForTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Metadata\PropertyMetadata
 */
final class PropertyMetadataTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     */
    public function it_can_be_constructed_including_annotations()
    {
        // given a class name, a property name and some \ScaleUpStack\Annoations\Annotations
        $className = ClassForTesting::class;
        $propertyName = 'someProperty';
        $annotations = new Annotations();

        // when constructing the metadata
        $metadata = new PropertyMetadata($className, $propertyName, $annotations);

        // then the properties are available
        $this->assertSame($className, $metadata->class);
        $this->assertSame($propertyName, $metadata->name);
        $this->assertSame($annotations, $metadata->annotations);
    }

    /**
     * @test
     * @covers ::serialize()
     * @covers ::unserialize()
     */
    public function it_can_be_serialized_and_unserialized()
    {
        // given PropertyMetadata with \ScaleUpStack\Annotations\Annotations
        $metadata = new PropertyMetadata(
            ClassForTesting::class,
            'firstProperty',
            new Annotations()
        );

        // when serializing and unserializng the metadata
        $unserialized = unserialize(serialize($metadata));

        // then the unserialized metadata is equal to the original instance
        $this->assertEquals($metadata, $unserialized);
    }
}

