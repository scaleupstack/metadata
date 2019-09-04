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

namespace ScaleUpStack\Metadata\Tests\PhpUnit\FeatureAnalyzers;

use ScaleUpStack\Annotations\Annotations;
use ScaleUpStack\Metadata\Factory;
use ScaleUpStack\Metadata\FeatureAnalyzers\TypedProperties;
use ScaleUpStack\Metadata\FeatureAnalyzers\TypedPropertyMetadata;
use ScaleUpStack\Metadata\InvalidArgumentException;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Metadata\PropertyMetadata;
use ScaleUpStack\Metadata\Tests\Resources\FeatureAnalyzers\ForTypedPropertiesTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\FeatureAnalyzers\TypedProperties
 */
final class TypedPropertiesTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        self::setupFeatureAnalyzer(new TypedProperties());
    }

    /**
     * @test
     * @covers ::name()
     * @covers ::extractMetadata()
     */
    public function it_extracts_typed_properties()
    {
        // given a registered TypedProperties FeatureAnalyzer (as setup in setUpBeforeClass()), and a class name
        $className = ForTypedPropertiesTesting::class;

        // when extracting the metadata;
        $hierarchicalMetadata = Factory::getMetadataForClass($className);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $hierarchicalMetadata->classMetadata[$className];

        // then the properties' type metadata is available in the features
        $this->assertEquals(
            [
                'someString' => new TypedPropertyMetadata(
                    'someString',
                    new DataTypeMetadata('string'),
                    new DataTypeMetadata(null)
                ),
                'withoutAnnotation' => new TypedPropertyMetadata(
                    'withoutAnnotation',
                    new DataTypeMetadata(null),
                    new DataTypeMetadata(null)
                ),
                'objectProperty' => new TypedPropertyMetadata(
                    'objectProperty',
                    new DataTypeMetadata(InvalidArgumentException::class . '|string|null'),
                    new DataTypeMetadata(null)
                )
            ],
            $classMetadata->features[TypedProperties::FEATURES_KEY]
        );
    }

    /**
     * @test
     * @covers ::extractMetadata()
     */
    public function it_throws_an_exception_if_more_than_one_var_annotation_is_given()
    {
        // given some ClassMetadata with a var annotation of a property that has more than one @var annotation
        $className = 'SomeClass';
        $classMetadata = new ClassMetadata(
            $className,
            [],
            new Annotations()
        );
        $annotations = new Annotations();
        $annotations->add('var', 'string', Annotations::CONTEXT_PROPERTY);
        $annotations->add('var', 'int', Annotations::CONTEXT_PROPERTY);
        $classMetadata->addPropertyMetadata(
            new PropertyMetadata($className, 'someProperty', $annotations)
        );
        // and a TypedProperties FeatureAnalyzer
        $analyzer = new TypedProperties();

        // when extracting the metadata
        // then an exception is thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only one @var annotation is allowed per property.');

        $analyzer->extractMetadata($classMetadata);
    }
}
