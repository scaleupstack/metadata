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

namespace ScaleUpStack\Metadata\Tests\PhpUnit;

use Metadata\ClassHierarchyMetadata;
use ScaleUpStack\Metadata\Factory;
use ScaleUpStack\Metadata\Tests\Resources\ClassForTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;
use ScaleUpStack\Reflection\Reflection;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Factory
 */
final class FactoryTest extends TestCase
{
    /**
     * @test
     * @covers ::getMetadataForClass()
     * @covers ::metadataFactory()
     */
    public function it_retrieves_class_metadata_for_a_classname()
    {
        // given a class name, and a reset Factory
        $className = ClassForTesting::class;
        Reflection::setStaticPropertyValue(Factory::class, 'metadataFactory', null);

        // when getting the metadata for a class
        $hierarchyMetadata = Factory::getMetadataForClass($className);

        // then the ClassHierarchyMetadata of the class is returned
        $this->assertInstanceOf(ClassHierarchyMetadata::class, $hierarchyMetadata);
        $this->assertSame(
            $className,
            $hierarchyMetadata->classMetadata[$className]->name
        );
    }
}
