<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Advanced Product Options extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomOptions
 * @author     MageWorx Dev Team
 */

class MageWorx_Adminhtml_Customoptions_ReportsController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction() {       
        $this->_title($this->__('APO'))->_title($this->__('Stock Report'));
        $this->loadLayout()
            ->_setActiveMenu('catalog/customoptions')
            ->_addBreadcrumb($this->__('APO'), $this->__('Stock Report'))
            ->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('mageworx/customoptions_reports_grid')->toHtml()
        ); 
    }
    
    public function massQtyAction() {
        $optionTypeIds = $this->getRequest()->getParam('reports');
        $requestQty = $this->getRequest()->getParam('customoption_qty');
        if (!is_array($optionTypeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Row(s)'));
        } else {
            try {
                list($countUpdated, $countNotUpdated) = $this->_updateItems($optionTypeIds, $requestQty);
                if ($countUpdated > 0) $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', $countUpdated));
                if ($countNotUpdated > 0) $this->_getSession()->addError($this->__('Total of %d record(s) were not updated because they are linked to products via SKU.', $countNotUpdated));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massDisableInventoryAction() {
        $optionTypeIds = $this->getRequest()->getParam('reports');
        if (!is_array($optionTypeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Row(s)'));
        } else {
            list($countUpdated, $countNotUpdated) = $this->_updateItems($optionTypeIds);
            if ($countUpdated > 0) $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', $countUpdated));
            if ($countNotUpdated > 0) $this->_getSession()->addError($this->__('Total of %d record(s) were not updated because they are linked to products via SKU.', $countNotUpdated));
        }
        $this->_redirect('*/*/index');
    }
    
    
    private function _updateItems($optionTypeIds = array(), $requestQty = NULL) {
        $countUpdated = 0;
        $countNotUpdated = 0;
        $data = array();
        $model = Mage::getSingleton('customoptions/stock');
        list($sign, $qty) = $this->_getSignQty($requestQty);
        if (isset($optionTypeIds) && is_array($optionTypeIds)) {
            foreach ($optionTypeIds as $id) {
                $optionValue = $model->load($id);
                if (!$optionValue->getSku()) {                    
                    switch ($sign) {
                        case "+":
                            $qty = $optionValue->getCustomoptionsQty() + (int)$qty;
                            break;
                        case "-":
                            $qty = $optionValue->getCustomoptionsQty() - (int)$qty;
                            break;
                        default:
                            $qty = $qty;
                            break;
                    }
                    $optionValue->setCustomoptionsQty($qty)
                                ->save();
                    $countUpdated++;
                } else {
                    $countNotUpdated++;
                }
            }
        }
        return array($countUpdated, $countNotUpdated);
    }


    private function _getSignQty($qty) {
        if ($qty === NULL) return $qty;
        
        $sign = $qty[0];
        if ($sign == "+" || $sign == "-") {
            return array($sign, substr($qty, 1));
        }
        return array('', $qty);
    }
    
}