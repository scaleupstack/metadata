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
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
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
     */
    public function it_stores_class_level_metadata()
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
    }

    public function provides_wrong_use_statements() : array
    {
        return [
            ['No\As\But\Alias MyAlias'],
            ['HasAlias\ButNoAs\InMiddle somethingwrong MyAlias'],
        ];
    }

    /**
     * @test
     * @dataProvider provides_wrong_use_statements
     * @covers ::setUseStatements()
     * @covers ::throwOnInvalidUseStatement()
     */
    public function it_throws_an_exception_on_wrong_use_statements($wrongUseStatement)
    {
        // given a class name
        $className = ClassForTesting::class;
        // and an invalid use statements
        $useStatements = [$wrongUseStatement];
        // and some Annotations
        $annotations = new Annotations();

        // when creating the ClassMetadata
        // then an exception is thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                "Invalid use statement '%s' in class %s",
                $wrongUseStatement,
                $className
            )
        );

        $classMetadata = new ClassMetadata($className, $useStatements, $annotations);
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

