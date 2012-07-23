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
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
class Multikomerce_Redecard_Model_komerci extends Mage_Core_Model_Abstract
{
       
    		/**
        * A model to serialize attributes
        * @var Varien_Object
        */
        protected $_serializer = null ;

        /**
        * Initialization
        */
        protected function _construct ()
        {
            $this -> _serializer = new Varien_Object ();
            parent :: _construct ();
        }
        
         //coloca o valor sempre com duas casas decimais
          public function tratavalor($valor)
         {
             return round($valor, 2);
         }
         
         public function calculajuros($valor){
             return ($valor/100)+1;
         }
         
         
         /*============================================================================
         * 
         * Solicita autorização do pagamento no webservice da redecard
         * 
         * @total - valor total da compra
         * @transacao - tipo de transação
         * @parcelas - numero de parcelas
         * @filiacao - numero de filiação no komerci
         * @numpedido - numero do pedido no magento
         * @nrcartao - numero do cartão
         * @cvc2 - código de segurança do cartão
         * @mes - mês de validade do cartão
         * @ano - ano de validade do cartão
         * @portador - nome no cartão
         * 
         ===========================================================================*/
        public function setTransacao($total, $transacao, $parcelas, $filiacao, $numpedido, $nrcartao, $cvc2, $mes, $ano, $portador)
        {
            
               $parameters = new stdClass();
                $parameters->Total = $total; 
                $parameters->Transacao = $transacao;
                $parameters->Parcelas = $parcelas;
                $parameters->Filiacao = $filiacao;
                $parameters->NumPedido = $numpedido;
                $parameters->Nrcartao = $nrcartao;
                $parameters->CVC2 = $cvc2;
                $parameters->Mes = $mes;
                $parameters->Ano = $ano;
                $parameters->Portador = $portador;
                $parameters->IATA = NULL;
                $parameters->Distribuidor = NULL;
                $parameters->Concentrador = NULL;
                $parameters->TaxaEmbarque = NULL;
                $parameters->Entrada = NULL;
                $parameters->Pax1 = NULL;
                $parameters->Pax2 = NULL;
                $parameters->Pax3 = NULL;
                $parameters->Pax4 = NULL;
                $parameters->Numdoc1 = NULL;
                $parameters->Numdoc2 = NULL;
                $parameters->Numdoc3 = NULL;
                $parameters->Numdoc4 = NULL;
                $parameters->ConfTxn = NULL;
                $parameters->Add_Data = NULL; 
            



                /**
                 * Aqui enviamos a requisição
                 */
                try {
                        $komerci = new SoapClient('https://ecommerce.redecard.com.br/pos_virtual/wskomerci/cap.asmx?WSDL',
                                array(
                                        'trace'                 => 1,
                                        'exceptions'                    => 1,
                                        'style'                 => SOAP_DOCUMENT,
                                        'use'                   => SOAP_LITERAL,
                                        'soap_version'                  => SOAP_1_1,
                                        'encoding'              => 'UTF-8'
                                )
                        );

                        /**
                        * A variável $EncryptRequestResult abaixo conterá o conteúdo criptografado se tudo ocorrer bem
                        */
                  $GetAuthorizedResponse = $komerci -> GetAuthorized($parameters);
                  $resposta = $GetAuthorizedResponse -> GetAuthorizedResult; //Exibindo o conteúdo criptografado
                  $xml = simplexml_load_string($resposta -> any);  
                  $autorizacao['codret'] 	= $xml->CODRET;   
                  $autorizacao['msgret'] 	= $xml->MSGRET;   	
                  $autorizacao['numsqn'] 	= $xml->NUMSQN;   	
                  $autorizacao['numcv'] 	= $xml->NUMCV;		
                  $autorizacao['numautor'] 	= $xml->NUMAUTOR;	
                  $autorizacao['numpedido'] 	= $xml->NUMPEDIDO;	
                  $autorizacao['data'] 		= $xml->DATA;		
                  
                  //retorna os dados da aprovação no vetor @autorizacao
                  //retorna os dados em caso de sucesso faz a captura
                  if(isset($autorizacao['codret']) && $autorizacao['codret'] == 0){
                      //INICIO DA CAPTURA
                      
                              $soap_request  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
                              $soap_request .= "<soap:Envelope xmlns:xsi=\"https://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
                              $soap_request .= "  <soap:Body>\n";
                                    $soap_request .= "<ConfirmTxn xmlns=\"http://ecommerce.redecard.com.br\">\n";
                                            $soap_request .= "<Data>".$autorizacao['data']."</Data>\n";
                                            $soap_request .= "<NumSqn>".$autorizacao['numsqn']."</NumSqn>\n";
                                            $soap_request .= "<NumCV>".$autorizacao['numcv']."</NumCV>\n";
                                            $soap_request .= "<NumAutor>".$autorizacao['numautor']."</NumAutor>\n";
                                            $soap_request .= "<Parcelas>".$parameters->Parcelas."</Parcelas>\n";
                                            $soap_request .= "<TransOrig>".$parameters->Transacao."</TransOrig>\n";
                                            $soap_request .= "<Total>".$parameters->Total."</Total>\n";
                                            $soap_request .= "<Filiacao>".$parameters->Filiacao."</Filiacao>\n";
                                            $soap_request .= "<Distribuidor></Distribuidor>\n";
                                            $soap_request .= "<NumPedido>".$autorizacao['numpedido']."</NumPedido>\n";
                                            $soap_request .= "<Pax1></Pax1>\n";
                                            $soap_request .= "<Pax2></Pax2>\n";
                                            $soap_request .= "<Pax3></Pax3>\n";
                                            $soap_request .= "<Pax4></Pax4>\n";
                                            $soap_request .= "<Numdoc1></Numdoc1>\n";
                                            $soap_request .= "<Numdoc2></Numdoc2>\n";
                                            $soap_request .= "<Numdoc3></Numdoc3>\n";
                                            $soap_request .= "<Numdoc4></Numdoc4>\n";
                                    $soap_request .= "</ConfirmTxn>\n";
                              $soap_request .= "  </soap:Body>\n";
                              $soap_request .= "</soap:Envelope>";

                            //echo $soap_request;
                              $header = array(
                                "POST /pos_virtual/wskomerci/cap.asmx HTTP/1.1",
                                "Host: ecommerce.redecard.com.br",
                                "Content-type: text/xml; charset=\"utf-8\"",
                                "Content-length: ".strlen($soap_request),
                                "SOAPAction: \"http://ecommerce.redecard.com.br/ConfirmTxn\"",
                              );

                              $soap_do = curl_init();
                              curl_setopt($soap_do, CURLOPT_URL,            "https://ecommerce.redecard.com.br/pos_virtual/wskomerci/cap.asmx?WSDL" );
                              curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
                              curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
                              curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
                              curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
                              curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
                              curl_setopt($soap_do, CURLOPT_POST,           true );
                              curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
                              curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);

                             $resultado = curl_exec($soap_do);
                             
                             
                             $resultado2 =  htmlentities($resultado, ENT_QUOTES, "UTF-8");
                                $resultado2 = explode('CODRET&gt;',$resultado2);
                                $codret = explode('&lt;/',$resultado2[1]);

                                $resultado3 =  htmlentities($resultado, ENT_QUOTES, "UTF-8");
                                $resultado3 = explode('MSGRET&gt;',$resultado3);
                                $msgret = explode('&lt;/',$resultado3[1]);



                              $autorizacao['codret'] 	= $codret[0];   
                              $autorizacao['msgret'] 	= $msgret[0];  

                              if($resultado === false) {
                                $err = 'Curl error: ' . curl_error($soap_do);
                                curl_close($soap_do);
                                $autorizacao['msgret'] = $err;
                              } else {
                                $autorizacao = $autorizacao;
                                curl_close($soap_do);
                              }
                        //FIM DA CAPTURA
                  } 
                  
                  
                  return $autorizacao;
                 

                } catch( SoapFault $fault ){

                               return false;

                }
                
        }
        
        
        
        
        
        
         /*============================================================================
         * 
         * Solicita autorização do pagamento no webservice da redecard com o serviço AVS
         * 
         ===========================================================================*/
        public function getAutorizedAVS()
        {
            
        }
        
        
        
        
        
  
    
}
?>