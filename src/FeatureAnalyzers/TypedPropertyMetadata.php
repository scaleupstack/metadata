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

namespace ScaleUpStack\Metadata\FeatureAnalyzers;

use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;

final class TypedPropertyMetadata
{
    private $name;

    private $docBlockDataTypeMetadata;

    private $phpDataTypeMetadata;

    public function __construct(
        string $name,
        DataTypeMetadata $docBlockDataTypeMetadata,
        DataTypeMetadata $phpDataTypeMetadata)
    {
        $this->name = $name;
        $this->docBlockDataTypeMetadata = $docBlockDataTypeMetadata;
        $this->phpDataTypeMetadata = $phpDataTypeMetadata;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function docBlockDataTypeMetadata() : DataTypeMetadata
    {
        return $this->docBlockDataTypeMetadata;
    }

    public function phpDataTypeMetadata() : DataTypeMetadata
    {
        return $this->phpDataTypeMetadata;
    }
}
