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

use ScaleUpStack\EasyObjectGenerator\Specification\DataTypeSpecification;
use ScaleUpStack\Metadata\FeatureAnalyzers\TypedPropertyMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\FeatureAnalyzers\TypedPropertyMetadata
 */
final class TypedPropertyMetadataTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::name()
     * @covers ::docBlockDataTypeMetadata()
     * @covers ::phpDataTypeMetadata()
     */
    public function it_can_be_created()
    {
        // given some property name, a doc block DataTypeMetadata, and a PHP DataTypeMetadata
        $propertyName = 'someProperty';
        $docBlockType = new DataTypeMetadata('string');
        $phpType = new DataTypeMetadata(null);

        // when creating the TypedPropertyMetadata
        $metadata = new TypedPropertyMetadata($propertyName, $docBlockType, $phpType);

        // then the parameters can be fetched
        $this->assertSame($propertyName, $metadata->name());
        $this->assertSame($docBlockType, $metadata->docBlockDataTypeMetadata());
        $this->assertSame($phpType, $metadata->phpDataTypeMetadata());
    }
}
