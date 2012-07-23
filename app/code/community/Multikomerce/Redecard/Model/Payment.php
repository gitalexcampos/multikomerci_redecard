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
 * @package    Multikomerce_Redecard
 * @copyright  Copyright (c) 2011 MagentoNet (www.magento.net.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     MagentoNet <contato@magento.net.br>
 */

class Multikomerce_Redecard_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'Multikomerce_Redecard';
    protected $_formBlockType = 'Multikomerce_Redecard/form';
    protected $_infoBlockType = 'Multikomerce_Redecard/info';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;
    //protected $_canCapture = true;
    
    //protected $_order = null;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $additionaldata = array('Cc_parcelas' => $data->getCcParcelas(), 'cc_cid_enc' => $info->encrypt($data->getCcCid()));
        $info->setCcType($data->getCcType())
            ->setAdditionalData(serialize($additionaldata))
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
	->setCcNumberEnc($info->encrypt($data->getCcNumber())) //criptografa o numero do cartão
        ->setCcCidEnc($info->encrypt($data->getCcCid())) //criptografa o código de segurança do cartão
            ;
        return $this;
    }


 /**
     * Prepare info instance for save
     * Prepara a instancia info para receber os dados do cartão
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        
        $info->setCcNumber(null) //apaga o numero descriptografado
            ->setCcCid(null); //apaga o código de segurança descriptografado
            
        return $this;
    }
    /**
     *  Retorna pedido
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     *  Associa pedido
     *
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $this->_order = $order;
        } elseif (is_numeric($order)) {
            $this->_order = Mage::getModel('sales/order')->load($order);
        } else {
            $this->_order = null;
        }
        return $this;
    }

   public function getOrderPlaceRedirectUrl($orderId = 0)
	{
	   $params = array();
       $params['_secure'] = true;
       
	   if ($orderId != 0 && is_numeric($orderId)) {
	       $params['order_id'] = $orderId;
	   }
       
       
        
        return Mage::getUrl('komerci/Pay/redirect', $params);
    }
        
}
