<?php
class Oitoo_Bradescocomercioeletronico2_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getUrlAcesso($_order, $_customer){
        $payment = $_order->getPayment();
        $url_acesso = $payment->getAdditionalInformation('url_acesso');
        if($url_acesso == NULL || $url_acesso == '' || !isset($url_acesso)){
            //boleto ainda não foi gerado
            $registro = Mage::getModel('bradescoce2/payment')->registrarBoleto($_order, $_customer);
            if($registro){
                return $payment->getAdditionalInformation('url_acesso');
            } else {
                return false;
            }
        } else {
            return $url_acesso;
        }
    }
}
	 
