<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\Aspect;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * An aspect which cares for CSRF protection.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @aspect
 */
class CsrfProtectionAspect {

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \F3\FLOW3\Security\Context
	 */
	protected $securityContext;

	/**
	 * @var \F3\FLOW3\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ObjectManagerInterface $objectManager A reference to the object manager
	 * @return void
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects the reflection service
	 *
	 * @param \F3\FLOW3\Reflection\ReflectionService $reflectionService The reflection service
	 * @return void
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Injects the security context
	 *
	 * @param \F3\FLOW3\Security\Context $securityContext The security context
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectSecurityContext(\F3\FLOW3\Security\Context $securityContext) {
		$this->securityContext = $securityContext;
	}

	/**
	 * Injects the policy service
	 *
	 * @param \F3\FLOW3\Security\Policy\PolicyService $policyService The policy service
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectPolicyService(\F3\FLOW3\Security\Policy\PolicyService $policyService) {
		$this->policyService = $policyService;
	}

	/**
	 * Adds a CSRF token as argument in the URI builder
	 *
	 * @before method(F3\FLOW3\MVC\Web\Routing\UriBuilder->build()) && setting(FLOW3.security.enable)
	 * @param \F3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function addCsrfTokenToUri(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$uriBuilder = $joinPoint->getProxy();
		$arguments = $uriBuilder->getArguments();
		$packageKey = (isset($arguments['@package']) ? $arguments['@package'] : '');
		$subpackageKey = (isset($arguments['@subpackage']) ? $arguments['@subpackage'] : '');
		$controllerName = (isset($arguments['@controller']) ? $arguments['@controller'] : 'Standard');
		$actionName = (isset($arguments['@action']) ? $arguments['@action'] : 'index') . 'Action';

		$possibleObjectName = 'F3\@package\@subpackage\Controller\@controllerController';
		$possibleObjectName = str_replace('@package', $packageKey, $possibleObjectName);
		$possibleObjectName = str_replace('@subpackage', $subpackageKey, $possibleObjectName);
		$possibleObjectName = str_replace('@controller', $controllerName, $possibleObjectName);
		$possibleObjectName = str_replace('\\\\', '\\', $possibleObjectName);
		$lowercaseObjectName = strtolower($possibleObjectName);

		$className = $this->objectManager->getClassNameByObjectName($this->objectManager->getCaseSensitiveObjectName($lowercaseObjectName));

		if ($this->policyService->hasPolicyEntryForMethod($className, $actionName)
			&& !$this->reflectionService->isMethodTaggedWith($className, $actionName, 'skipCsrfProtection')) {
			$arguments['FLOW3-CSRF-TOKEN'] = $this->securityContext->getCsrfProtectionToken();
			$uriBuilder->setArguments($arguments);
		}
	}
}

?>