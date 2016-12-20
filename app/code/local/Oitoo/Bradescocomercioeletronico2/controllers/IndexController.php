<?php
class Oitoo_Bradescocomercioeletronico2_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

	  $this->loadLayout();
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Titlename"),
                "title" => $this->__("Titlename")
		   ));

      $this->renderLayout();

    }

    public function registrarBoletoAction(){

        $_order = mage::getModel('sales/order')->load($_REQUEST['id_pedido']);
        $_customer = mage::getModel('customer/customer')->load($_REQUEST['id_cliente']);

        if($_order && $_customer){
            $url_acesso = mage::helper('bradescoce2')->getUrlAcesso($_order, $_customer);
            if($url_acesso){
                var_dump($url_acesso);
            } else {
                echo "Não foi possível emitir o boleto. Por favor entre em contato!";
            }
        } else {
            echo "Não foi possível emitir o boleto. Por favor entre em contato!";
        }


    }



    public function ConfirmarPagamentosAction(){
          var_dump(mage::getModel('bradescoce2/payment')->setAutenticacao(0,123,54654));
    }
}
