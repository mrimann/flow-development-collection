<?php
namespace TYPO3\Flow\Tests\Functional\Aop\Fixtures;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * An aspect for testing functionality related to abstract classes
 *
 * @Flow\Aspect
 */
class AbstractClassTestingAspect
{
    /**
     * @Flow\Around("method(public TYPO3\Flow\Tests\Functional\Aop\Fixtures\SubClassOfAbstractClass->abstractMethod())")
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
     * @return string
     */
    public function abstractMethodInSubClassAdvice(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);
        return $result . ' adviced';
    }

    /**
     * @Flow\Around("method(public TYPO3\Flow\Tests\Functional\Aop\Fixtures\AbstractClass->concreteMethod())")
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
     * @return string
     */
    public function concreteMethodInAbstractClassAdvice(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint)
    {
        $result = $joinPoint->getAdviceChain()->proceed($joinPoint);
        return $result . ' adviced';
    }
}
