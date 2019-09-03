<?php declare(strict_types = 1);

/**
 * This file is part of ScaleUpStack/Metadata.
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\Tests\Resources;

use ScaleUpStack\Metadata\Configuration;
use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;
use ScaleUpStack\Reflection\Reflection;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private static $oldConfiguration = null;

    protected static function setupFeatureAnalyzer(FeatureAnalyzer $featureAnalyzer)
    {
        self::$oldConfiguration = Reflection::getStaticPropertyValue(Configuration::class, 'configuration');
        Reflection::setStaticPropertyValue(Configuration::class, 'configuration', []);

        Configuration::registerFeatureAnalyzer($featureAnalyzer);
    }

    public static function tearDownAfterClass()
    {
        if (! is_null(self::$oldConfiguration)) {
            Reflection::setStaticPropertyValue(Configuration::class, 'configuration', self::$oldConfiguration);
        }
    }
}
