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

class Multikomerce_Redecard_PayController extends Mage_Core_Controller_Front_Action
{
    
    /**
     * Pega o método principal
     */
    public function getKomerci()
    {
        return Mage::getSingleton('Multikomerce_Redecard/payment');
    }
    
    

    /**
     * Retorna o Checkout
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Retorna ID da store, através do ID do pedido
     * 
     */
    function getOrderStoreId($orderId) {
        return Mage::getModel('sales/order')->load($orderId)->getStoreId();
    }




  //busca os dados da compra
  public function getOrder()
	    {
	        if ($this->_order == null) {
						$this->_order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
	        }
	        return $this->_order;
	    }

    /**
     * Redireciona o cliente ao PagSeguro na finalização do pedido
     *
     */
    public function redirectAction()
    {


        $komerci = $this->getKomerci(); //pega todos os metodos do modelo payment.php
        $session = $this->getCheckout(); //pega os dados do checkout

        //$orderId = $this->getRequest()->getParam('order_id');
        //$order = $this->_order = Mage::getModel('sales/order')->load(11);

        $order = $this->getOrder(); //pega os dados da compra
	$payment = $order->getPayment(); //pega os dados do pagamento

        if (empty($orderId)) {
            $orderId = $session->getLastOrderId();
            //$session->clear(); //Limpa o carrinho
        }

        //pega id da loja
        $storeId = $this->getOrderStoreId($orderId);
                
        
        //chama model do webservice do komerci
        $webservice = Mage::getModel('Multikomerce_Redecard/Komerci');
        /*===========================================================================================
         * Código para efetuar a transação pelo webservice komerci
         * 
         * 
         ============================================================================================*/
        
        
        //pega dados necessarios para pagamento
        $additionaldata = unserialize($payment->getData('additional_data'));
        $parcelas               = $additionaldata["Cc_parcelas"];
        $juros                  = $komerci->getConfigData('parcelamento_juros', $storeId);
        $parcelasMaximo         = $komerci->getConfigData('num_max_parc', $storeId);
        $parcelasSemJuros       = $komerci->getConfigData('parcelamento_semjuros', $storeId);
        $parcelasValorMinimo    = $komerci->getConfigData('valor_minimo', $storeId);
        $descontoAVista         = $komerci->getConfigData('desconto_avista', $storeId);
        $descontoAVistaValor    = $komerci->getConfigData('valor_desconto_avista', $storeId);
        $valortotal             = $order->getGrandTotal(); //pega valor total do pedido no carrinho
        
        //$total = $webservice->calculajuros($juros);
        
        
        //tipo de transação 04 é para pagamento a vista
        if($parcelas == 0){
            $transacao = '04';
        } else {
            $transacao = $komerci->getConfigData('tipo_parcelamento', $storeId);$order->getId(); //verifica se é emissor ou loja
        }
        $filiacao  = $komerci->getConfigData('filiacao', $storeId);
        $numpedido = $orderId;
        $nrcartao  = $payment->decrypt($payment->getCcNumberEnc());
        $cvc2      = $payment->decrypt($additionaldata['cc_cid_enc']);
        $mes       = $payment->getCcExpMonth();
        $ano       = $payment->getCcExpYear();
        $ano       = substr($ano,-2);
        $portador  = $payment->getCcOwner();
        
        //verifica o pagamento (à vista, à vista com desconto, 2x sem juros, 3x sem juros...)
        if($parcelas > 1){
                if($parcelas <= $parcelasSemJuros){
                   $valor = $valortotal;
                } else {
                    //faz o calculo dos juros
                    $total_com_juros = $valortotal;
                    for ($i=1; $i < $parcelas +1; $i++){
			$total_com_juros *= 1 + ($juros / 100);
		     }
			$valor = $total_com_juros;
                    } 
                
        } else {
            if($descontoAVista){
                $valor = $valortotal * ((($descontoAVistaValor / 100) - 1) * -1);
            } else {
                $valor = $valortotal;
            }
        }
        
        
        $total = $valor;
       
         $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();

		if(isset($totals["encargo"])){
			$encargo = $totals["encargo"]->getValue();
		}else{
			$encargo = 0;
		}
		if($encargo > 0){
			$total = $total - $encargo;
		}
                
        
        $total =  number_format($total, 2);
        
     $total = str_replace(',', '', $total); //tira virgula no milhar
     /*echo $total ."<br>";
         echo   "Transacao ".  $transacao ."<br>";
            echo  "Parcelas " . $parcelas ."<br>";
              echo "Filiacao " . $filiacao."<br>";
              echo "Numero do pedido ". $numpedido ."<br>";
            echo  "Numero do cartão " . $nrcartao ."<br>";
             echo "Numero do código de segurança " . $cvc2."<br>";
             echo "Mês " . $mes."<br>";
            echo  "Ano " . $ano."<br>";
             echo "Portador" . $portador."<br>"; */


        $autorizacao = $webservice->setTransacao($total, $transacao, $parcelas, $filiacao, $numpedido, $nrcartao, $cvc2, $mes, $ano, $portador);
        //coloca a resposta no banco de dados
        $additionaldata['autorizacao'] = array(
						'codigo' => (string)$autorizacao['codret'],
						'mensagem' => (string)$autorizacao['msgret']
				);
        $payment->setAdditionalData(serialize($additionaldata));
        $payment->save();
        
        //Mostra mensagem de sucesso
        if($autorizacao['codret'] == 0)  
        {
            //em caso de sucesso no pagamento redireciona para a pagina personalizada de sucesso
            
             //em caso de sucesso faz a captura automatica
                                                if($order->canInvoice()) {
                                                        /**
                                                         * Create invoice
                                                         * The invoice will be in 'Pending' state
                                                         */
                                                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());

                                                        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);

                                                        /**
                                                         * Pay invoice
                                                         * i.e. the invoice state is now changed to 'Paid'
                                                         */
                                                        //$invoice->capture()->save();
                                                }                
                                                           
                                                 //$this->_redirect('komerci/pay/success/codigo/'.$autorizacao['codret'].'/motivo/'.$autorizacao['msgret'].'/orderid/'.$orderId);
                                                
                                                //envia email de confirmação da compra
                                                $order->sendNewOrderEmail();
					
                                                
                                                $this->getCheckout()->setLastOrderId($orderId); 
                                                 $this->getCheckout()->setLastQuoteId($session->getLastQuoteId()); 
                                                 $this->getCheckout()->setLastSuccessQuoteId($session->getLastSuccessQuoteId());
                                                 $this->_redirect('checkout/onepage/success', array('_secure'=>true));
        } else {
            //caso tenha acontecido algum erro, redireciona apra página de erro de erro personalizada
            $this->_redirect('komerci/pay/failure/codigo/'.$autorizacao['codret'].'/motivo/'.$autorizacao['msgret'].'/orderid/'.$orderId);
        }
           
    }

   

    /**
     * Exibe informações de conclusão do pagamento
     * 
     */
    public function successAction()
    {
        $orderId = $this->getRequest()->getParam('orderid');
        $codigo = $this->getRequest()->getParam('codigo');
        $motivo = $this->getRequest()->getParam('motivo');
        
       
        
        $this->loadLayout();
        
        $texto = "<br><h1>Obrigado pela preferência!</h1><br>";
        $texto .= "<h2>".$motivo."</h2><br>";
        $texto .= "Número do pedido: ".$orderId."<br>";
        $texto .= "Em alguns instantes você receberá um e-mail com a confirmação da compra.<br>";
        $texto .= "É possivel verificar informações referentes a sua compra no nosso painel de <a href='/customer/account/'>controle</a>.";
        
        
        
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Text');
        $block->setText($texto);
        $this->getLayout()->getBlock('content')->append($block);

        //Now showing it with rendering of layout
         $this->renderLayout();
            
    }
    
      public function failureAction()
    {
        $orderId = $this->getRequest()->getParam('orderid');
        $codigo = $this->getRequest()->getParam('codigo');
        $motivo = $this->getRequest()->getParam('motivo');
        
        Mage::getModel('sales/order')->load($orderId);
        
        $this->loadLayout();
        
        $texto = "<h1>Ocorreu um problema com a confirmação de pagamento.</h1><br>";
        $texto .= "Motivo: ".$motivo."<br>";
        $texto .= "Para maiores informações entre em contato.<br>";
        
        
        
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Text');
        $block->setText($texto);
        $this->getLayout()->getBlock('content')->append($block);

        //Now showing it with rendering of layout
         $this->renderLayout();
        
            /* $storeId = $this->getOrderStoreId($orderId);
            $this->loadLayout();
            $this->renderLayout(); */
            
    }

      public function estornoAction()
      {
          //verifica se usuario está logado no admini do magento
          
          //efetua o estorno
          
      }
  
    

}
