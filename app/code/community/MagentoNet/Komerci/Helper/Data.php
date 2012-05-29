<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   payment
 * @package    MagentoNet_Komerci
 * @copyright  Copyright (c) 2011 MagentoNet (www.magento.net.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     MagentoNet <contato@magento.net.br>
 */

class MagentoNet_Komerci_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const PARCEL_MAX_VALUE = 5;

    /**
     * Escapa entidades HTML.
     * Função criada para compatibilidade com versões mais antigas do Magento.
     *
     * @param   mixed $data
     * @param   array $allowedTags
     * @return  string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        $core_helper = Mage::helper('core');
        if (method_exists($core_helper, "escapeHtml")) {
            return $core_helper->escapeHtml($data, $allowedTags);
        } elseif (method_exists($core_helper, "htmlEscape")) {
            return $core_helper->htmlEscape($data, $allowedTags);
        } else {
            return $data;
        }
        
    }
    
   
    
}
