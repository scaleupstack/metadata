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

namespace ScaleUpStack\Metadata\Tests\PhpUnit\Generator;

use Metadata\MetadataFactory;
use ScaleUpStack\Annotations\Annotations;
use ScaleUpStack\Metadata\Generator\FileLocator;
use ScaleUpStack\Metadata\Generator\FromFileReader;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\PropertyMetadata;
use ScaleUpStack\Metadata\Tests\Resources\ClassForTesting;
use ScaleUpStack\Metadata\Tests\Resources\FeatureAnalyzerForTesting;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Generator\FromFileReader
 */
final class FromFileReaderTest extends TestCase
{
    /**
     * @var MetadataFactory
     */
    private $factory;

    public function setUp()
    {
        parent::setUp();

        $fileLocator = new FileLocator();
        $this->factory = new MetadataFactory(
            new FromFileReader($fileLocator)
        );
    }

    /**
     * @test
     * @covers \ScaleUpStack\Metadata\Generator\FileLocator::findFileForClass()
     * @covers ::getExtension()
     * @covers ::loadMetadataFromFile()
     * @covers ::extractClassLevelMetadata()
     * @covers ::parseUseStatements()
     */
    public function it_analyzes_class_level_metadata()
    {
        // given a factory as provided via setUp() and a class name
        $className = ClassForTesting::class;

        // when retrieving the metadata
        $hierarchyMetadata = $this->factory->getMetadataForClass($className);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $hierarchyMetadata->classMetadata[$className];

        // then the class name, and the namespace are available
        $this->assertSame(
            $className,
            $classMetadata->name
        );
        $this->assertSame('ScaleUpStack\Metadata\Tests\Resources', $classMetadata->namespace);

        // and the use statements are compiled
        $this->assertSame(
            [
                'ClassMetadata' => 'ScaleUpStack\Metadata\ClassMetadata',
                'BaseClassMetadata' => 'Metadata\ClassMetadata',
            ],
            $classMetadata->useStatements
        );
    }

    /**
     * @test
     * @covers ::extractPropertyLevelMetadata()
     */
    public function it_analyzes_real_properties_metadata()
    {
        // given a factory as provided via setUp() and a class name
        $className = ClassForTesting::class;

        // when retrieving the metadata
        $hierarchyMetadata = $this->factory->getMetadataForClass($className);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $hierarchyMetadata->classMetadata[$className];

        // then the properties' metadata is available
        $firstProperty = new PropertyMetadata($className, 'firstProperty', new Annotations());
        $firstProperty->annotations
            ->add('var', 'string', Annotations::CONTEXT_PROPERTY);

        $secondProperty = new PropertyMetadata($className, 'secondProperty', new Annotations());
        $secondProperty->annotations
            ->add('var', 'int', Annotations::CONTEXT_PROPERTY);

        $thirdProperty = new PropertyMetadata($className, 'thirdProperty', new Annotations());

        $globalNamespacedType = new PropertyMetadata($className, 'globalNamespacedType', new Annotations());
        $globalNamespacedType->annotations
            ->add('var','\DateTime', Annotations::CONTEXT_PROPERTY);

        $typeImportedViaUse = new PropertyMetadata($className, 'typeImportedViaUse', new Annotations());
        $typeImportedViaUse->annotations
            ->add('var', 'ClassMetadata', Annotations::CONTEXT_PROPERTY);

        $typeInSameNamespace = new PropertyMetadata($className, 'typeInSameNamespace', new Annotations());
        $typeInSameNamespace->annotations
            ->add('var', 'ClassForTesting', Annotations::CONTEXT_PROPERTY);

        $typeRenamedViaUse = new PropertyMetadata($className, 'typeRenamedViaUse', new Annotations());
        $typeRenamedViaUse->annotations
            ->add('var', 'BaseClassMetadata', Annotations::CONTEXT_PROPERTY);

        $this->assertEquals(
            [
                'firstProperty' => $firstProperty,
                'secondProperty' => $secondProperty,
                'thirdProperty' => $thirdProperty,
                'globalNamespacedType' => $globalNamespacedType,
                'typeImportedViaUse' => $typeImportedViaUse,
                'typeInSameNamespace' => $typeInSameNamespace,
                'typeRenamedViaUse' => $typeRenamedViaUse,
            ],
            $classMetadata->propertyMetadata
        );
    }

    /**
     * @test
     * @covers ::analyzeRegisteredFeatures()
     */
    public function it_analyzes_features_via_configure_feature_analyzers()
    {
        // given a factory as provided via setUp() and a class name
        $className = ClassForTesting::class;
        // and a FeatureAnalyzer in the Configuration
        $this->setupFeatureAnalyzer(new FeatureAnalyzerForTesting());

        // when retrieving the metadata
        $hierarchyMetadata = $this->factory->getMetadataForClass($className);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $hierarchyMetadata->classMetadata[$className];

        $metadata = $this->factory->getMetadataForClass($className);

        // then the FeatureAnalyzer has added some date to the features property
        $this->assertEquals(
            [
                FeatureAnalyzerForTesting::class => ['some value'],
            ],
            $classMetadata->features
        );
    }
}
