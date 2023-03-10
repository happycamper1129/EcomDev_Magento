<?php
/**
 * PHP Unit test suite for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   EcomDev
 * @package    EcomDev_PHPUnit
 * @copyright  Copyright (c) 2013 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Constraint for testing event observer definition
 *  in the configuration
 *
 */
class EcomDev_PHPUnit_Constraint_Config_EventObserver
    extends EcomDev_PHPUnit_Constraint_AbstractConfig
{
    const XML_PATH_EVENTS = '%s/events';

    const TYPE_DEFINED = 'defined';

    const OBSERVER_TYPE_DISABLED = 'disabled';
    const OBSERVER_TYPE_SINGLETON = 'singleton';
    const OBSERVER_TYPE_MODEL = 'model';

    /**
     * Event area (frontend, adminhtml, global, cron)
     *
     * @var string
     */
    protected $_area = null;

    /**
     * Observer name for additional restriction
     *
     * @var string|null
     */
    protected $_observerName = null;

    /**
     * Observer class alias
     *
     * @var string
     */
    protected $_observerClassAlias = null;

    /**
     * Observer method
     *
     * @var string
     */
    protected $_observerMethod = null;

    /**
     * Name of the event that should be observed
     *
     * @var string
     */
    protected $_eventName = null;

    /**
     * Constraint for testing observer
     * event definitions in configuration
     *
     * @param string $area
     * @param string $eventName
     * @param string $observerClassAlias
     * @param string $observerMethod
     * @param string $type
     * @param string|null $observerName
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($area, $eventName, $observerClassAlias, $observerMethod, $type = self::TYPE_DEFINED, $observerName = null)
    {
        if (empty($area) || !is_string($area)) {
            throw EcomDev_PHPUnit_Helper::createInvalidArgumentException(1, 'string', $area);
        }

        if (empty($eventName) || !is_string($eventName)) {
            throw EcomDev_PHPUnit_Helper::createInvalidArgumentException(2, 'string', $eventName);
        }

        if (empty($observerClassAlias) || !is_string($observerClassAlias)) {
            throw EcomDev_PHPUnit_Helper::createInvalidArgumentException(3, 'string', $observerClassAlias);
        }

        if (empty($observerMethod) || !is_string($observerMethod)) {
            throw EcomDev_PHPUnit_Helper::createInvalidArgumentException(4, 'string', $observerMethod);
        }

        if ($observerName !== null && !is_string($observerName)) {
            throw EcomDev_PHPUnit_Helper::createInvalidArgumentException(6, 'string', $observerName);
        }

        $this->_area = $area;
        $this->_eventName = $eventName;

        $this->_observerClassAlias = $observerClassAlias;
        $this->_observerMethod = $observerMethod;
        $this->_observerName = $observerName;

        $expectedValue = $this->_observerClassAlias . '::' . $this->_observerMethod;
        $nodePath = sprintf(self::XML_PATH_EVENTS, $this->_area, $this->_eventName);

        parent::__construct($nodePath, $type, $expectedValue);
    }

    /**
     * Evaluates that observer is defined and enabled
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateDefined($other)
    {
        $this->setActualValue(false);

        if (!isset($other->{$this->_eventName}->observers)) {
            return false;
        }

        $observers = $other->{$this->_eventName}->observers;
        foreach ($observers->children() as $observer) {
            if ((string)$observer->type === self::OBSERVER_TYPE_DISABLED ||
                ($this->_observerName !== null && $this->_observerName !== $observer->getName())) {
                continue;
            }

            $classAlias = (isset($observer->class) ? (string) $observer->class : $observer->getClassName());
            $method = (string)$observer->method;

            if ($classAlias === $this->_observerClassAlias && $method == $this->_observerMethod) {
                $this->setActualValue($classAlias . '::' . $method);
                return true;
            }
        }

        return false;
    }

    /**
     * Text representation of event observer definition evaluation
     *
     * @return string
     */
    protected function textDefined()
    {
        $text = 'is defined';
        if ($this->_observerName !== null) {
            $text .= sprintf(' as %s observer name', $this->_observerName);
        }

        return $text;
    }


    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see \PHPUnit\Framework\Constraint\Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other)
    {
        return sprintf(
            '%s "%s" event observer %s.',
            $this->_area,
            $this->_expectedValue,
            $this->toString()
        );
    }
}
