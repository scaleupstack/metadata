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
use ScaleUpStack\Metadata\FeatureAnalyzers\VirtualMethodMetadata;
use ScaleUpStack\Metadata\FeatureAnalyzers\VirtualMethods;
use ScaleUpStack\Metadata\InvalidArgumentException;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Tests\Resources\FeatureAnalyzers\ClassForVirtualMethodsTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\FeatureAnalyzers\VirtualMethods
 */
final class VirtualMethodsTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        self::setupFeatureAnalyzer(new VirtualMethods());
    }

    /**
     * @test
     * @covers ::extractMetadata()
     * @covers ::name()
     */
    public function it_extracts_virtual_methods_metadata()
    {
        // given a registered VirtualMethods features analyzer (as setup in setUpBeforeClass()), and a class name
        $className = ClassForVirtualMethodsTesting::class;

        // when extracting the metadata;
        $hierarchicalMetadata = Factory::getMetadataForClass($className);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $hierarchicalMetadata->classMetadata[$className];

        // then the virtual methods' metadata is available in the features
        $this->assertEquals(
            [
                'someProperty' => new VirtualMethodMetadata(
                    $className,
                    'someProperty',
                    [],
                    new DataTypeMetadata('string'),
                    false
                ),
                'withSomeProperty' => new VirtualMethodMetadata(
                    $className,
                    'withSomeProperty',
                    [
                        'someProperty' => new DataTypeMetadata('string')
                    ],
                    new DataTypeMetadata('self'),
                    false
                ),
            ],
            $classMetadata->features[VirtualMethods::FEATURES_KEY]
        );
    }

    /**
     * @test
     * @covers ::extractMetadata()
     */
    public function it_throws_an_exception_if_virtual_method_has_a_parameter_with_default_value()
    {
        // given some ClassMetadata with a virtual method annotation that has a default value for a parameter
        $annotations = new Annotations();
        $annotations->add('method', 'someMethod($parameter = "defaultValue")', Annotations::CONTEXT_CLASS);
        $classMetadata = new ClassMetadata('SomeClassName', [], $annotations);
        // and a VirtualMethods feature analyzer
        $analyzer = new VirtualMethods();

        // when extracting the metadata
        // then an exception is thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currently, default values are not supported in virtual methods.');

        $analyzer->extractMetadata($classMetadata);
    }
}
