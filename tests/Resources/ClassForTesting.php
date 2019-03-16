<?php

/**
 * This file is part of ScaleUpStack/Metadata
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\Tests\Resources;

use ScaleUpStack\Metadata\ClassMetadata;
use Metadata\ClassMetadata as BaseClassMetadata;

/**
 * @property-read $firstProperty
 * @method string secondProperty()
 * @method string getThirdProperty()
 */
class ClassForTesting
{
    /**
     * @var string
     */
    private $firstProperty = 'first value';

    /**
     * @var int
     */
    private $secondProperty = 42;

    private $thirdProperty = [];

    /**
     * @var \DateTime
     */
    private $globalNamespacedType;

    /**
     * @var ClassMetadata
     */
    private $typeImportedViaUse;

    /**
     * @var ClassForTesting
     */
    private $typeInSameNamespace;

    /**
     * @var BaseClassMetadata
     */
    private $typeRenamedViaUse;

    public function __call($name, $arguments)
    {
        return $this->$name;
    }

    public function __get($name)
    {
        return $this->$name;
    }
}
