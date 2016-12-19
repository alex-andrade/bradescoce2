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

    public function gerarboletoAction(){

        $pedido = mage::getModel('sales/order')->load($_REQUEST['id_pedido']);
        $cliente = mage::getModel('customer/customer')->load($_REQUEST['id_cliente']);

        if($pedido && $cliente){
            $retorno = mage::getModel('bradescoce2/payment')->registrarBoleto($pedido, $cliente);
            var_dump($retorno);
        } else {
            echo "Ocorreu um erro";
        }


    }

    public function falhaAction(){
        var_dump($_REQUEST);
    }

    public function ConfirmarPagamentosAction(){
          mage::getModel('bradescoce2/payment')->confirmaPagamento();
    }
}
