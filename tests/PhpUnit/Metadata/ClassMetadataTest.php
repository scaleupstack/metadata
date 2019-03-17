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
use ScaleUpStack\Metadata\InvalidArgumentException;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Metadata\VirtualMethodMetadata;
use ScaleUpStack\Metadata\Tests\Resources\ClassForTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Metadata\ClassMetadata
 */
final class ClassMetadataTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     * @covers ::setNamespace()
     * @covers ::setUseStatements()
     * @covers ::setVirtualMethods()
     */
    public function it_stores_metadata_for_virtual_methods()
    {
        // given a class name
        $className = ClassForTesting::class;
        // and some use statements
        $useStatements = [
            'ScaleUpStack\Annotations\Annotation\MethodAnnotation',
            'Metadata\ClassMetadata as BaseClassMetadata',
        ];
        // and some Annotations with a PropertyReadAnnotation and a MethodAnnotation
        $annotations = new Annotations();
        $annotations->add('property-read', 'ClassForTesting $someProperty', Annotations::CONTEXT_CLASS);
        $annotations->add('method', 'BaseClassMetadata[] getSomeProperty()', Annotations::CONTEXT_CLASS);
        $annotations->add('method', 'self withSomeProperty(MethodAnnotation $someValue)', Annotations::CONTEXT_CLASS);

        // when creating the ClassMetadata
        $classMetadata = new ClassMetadata($className, $useStatements, $annotations);

        // then the namespace is stored
        $this->assertSame('ScaleUpStack\Metadata\Tests\Resources', $classMetadata->namespace);
        // and the use statements are compiled
        $this->assertSame(
            [
                'MethodAnnotation' => 'ScaleUpStack\Annotations\Annotation\MethodAnnotation',
                'BaseClassMetadata' => 'Metadata\ClassMetadata',
            ],
            $classMetadata->useStatements
        );
        // and the annotations are stored while the virtual methods are available directly
        $this->assertSame($annotations, $classMetadata->annotations);

        $this->assertEquals(
            [
                'getSomeProperty' => new VirtualMethodMetadata(
                    $className,
                    'getSomeProperty',
                    [],
                    new DataTypeMetadata('Metadata\ClassMetadata[]')
                ),
                'withSomeProperty' => new VirtualMethodMetadata(
                    $className,
                    'withSomeProperty',
                    [
                        'someValue' => new DataTypeMetadata('ScaleUpStack\Annotations\Annotation\MethodAnnotation'),
                    ],
                    new DataTypeMetadata('self')
                ),
            ],
            $classMetadata->virtualMethods
        );
    }

    /**
     * @test
     * @covers ::setVirtualMethods()
     */
    public function it_does_not_allow_default_values_in_virtual_methods()
    {
        // given a class name
        $className = ClassForTesting::class;
        // and some Annotations with a MethodAnnotation that has a default value
        $annotations = new Annotations();
        $annotations->add('method', 'getSomeProperty($someParameter = null)', Annotations::CONTEXT_CLASS);

        // when creating the ClassMetadata
        // then an exception is thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currently, default values are not supported in virtual methods.');

        new ClassMetadata($className, [], $annotations);
    }

    /**
     * @test
     * @covers ::serialize()
     * @covers ::unserialize()
     */
    public function it_can_be_serialized_and_unserialized()
    {
        // given a ClassMetadata instance with virtual properties and methods
        $annotations = new Annotations();
        $annotations->add('property-read', 'string $someProperty', Annotations::CONTEXT_CLASS);
        $annotations->add('method', 'int getSomeProperty()', Annotations::CONTEXT_CLASS);
        $metadata = new ClassMetadata(
            ClassMetadata::class,
            [
                Annotations::class,
            ],
            $annotations
        );
        $metadata->features['someFeature'] = new \stdClass();

        // when serializing and unserializing the metadata
        $unserializedMetadata = unserialize(serialize($metadata));

        // then the both instances are equal
        $this->assertEquals($metadata, $unserializedMetadata);
    }

    public function provides_short_and_fully_qualified_data_type_specifications() : array
    {
        return [
            [null, null],
            ['int', 'int'],
            ['bool', 'bool'],
            ['\DateTime', 'DateTime'],
            ['ClassMetadata', 'ScaleUpStack\Metadata\ClassMetadata'],
            ['BaseClassMetadata', 'Metadata\ClassMetadata'],
            ['ClassForTesting', 'ScaleUpStack\Metadata\Tests\Resources\ClassForTesting'],

            ['int[]', 'int[]'],
            ['\DateTime[]', 'DateTime[]'],
            ['ClassMetadata[]', 'ScaleUpStack\Metadata\ClassMetadata[]'],

            ['int[]|\DateTime', 'int[]|DateTime'],
        ];
    }

    /**
     * @test
     * @dataProvider provides_short_and_fully_qualified_data_type_specifications
     * @covers ::fullyQualifiedDataTypeSpecification()
     */
    public function it_transforms_a_data_type_specification_into_a_fully_qualified_specification(
        ?string $shortSpecification,
        ?string $expectedLongSpecification
    )
    {
        // given a ClassMetadata
        $classMetadata = new ClassMetadata(
            ClassForTesting::class,
            [
                'ScaleUpStack\Metadata\ClassMetadata',
                'Metadata\ClassMetadata as BaseClassMetadata',
            ],
            new Annotations()
        );
        // and a data type specification as provided by the test's parameter

        // when transforming the short data type specification
        $longSpecification = $classMetadata->fullyQualifiedDataTypeSpecification($shortSpecification);

        // then the specification was transformed to a fully qualified data type specification
        $this->assertSame($expectedLongSpecification, $longSpecification);
    }
}

