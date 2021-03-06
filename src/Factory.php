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

namespace ScaleUpStack\Metadata;

use Metadata\ClassHierarchyMetadata;
use Metadata\MetadataFactory;
use ScaleUpStack\Metadata\Generator\FileLocator;
use ScaleUpStack\Metadata\Generator\FromFileReader;

final class Factory
{
    private static $metadataFactory;

    private static function metadataFactory() : MetadataFactory
    {
        if (is_null(self::$metadataFactory)) {
            self::$metadataFactory = new MetadataFactory(
                new FromFileReader(
                    new FileLocator()
                )
            );
        }

        return self::$metadataFactory;
    }

    public static function getMetadataForClass(string $className) : ClassHierarchyMetadata
    {
        return self::metadataFactory()->getMetadataForClass($className);
    }
}
