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

use ScaleUpStack\Metadata\Configuration;
use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Configuration
 */
final class ConfigurationTest extends TestCase
{
    /**
     * @test
     * @covers ::registerFeatureAnalyzer()
     * @covers ::featureAnalyzers()
     */
    public function it_registers_features_and_allows_to_retrieve_them()
    {
        // given a feature analyzer and a short name
        $shortName = 'mocked';
        /** @var FeatureAnalyzer $featureAnalyzer */
        $featureAnalyzer = $this->getMockForAbstractClass(FeatureAnalyzer::class);

        // when registering the feature analyzer on the short name in the Configuration
        Configuration::registerFeatureAnalyzer($shortName, $featureAnalyzer);

        // then the feature analyzer is stored in the Configuration
        $this->assertSame(
            [
                $shortName => $featureAnalyzer,
            ],
            Configuration::featureAnalyzers()
        );
    }
}
