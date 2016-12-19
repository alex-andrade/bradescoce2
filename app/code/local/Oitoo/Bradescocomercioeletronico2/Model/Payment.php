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
 * @package    Alex Braga_bradescoce
 * @copyright  Copyright (c) 2016 Kaisan (www.kaisan.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Alex Braga <>
 */

class Oitoo_Bradescocomercioeletronico2_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'bradescoce2';
    protected $_formBlockType = 'bradescoce2/form';
    protected $_infoBlockType = 'bradescoce2/info';
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;



    public function registrarBoleto($pedido, $cliente){


        $configmodulo = Mage::getSingleton('bradescoce2/payment');

        $ambienteproducao = $configmodulo->getConfigData('ambiente', Mage::app()->getStore()->getId());
        if($ambienteproducao){
            $url = 'https://meiosdepagamentobradesco.com.br/api';
        } else {
            $url = 'https://homolog.meiosdepagamentobradesco.com.br/api';
        }

        $mercahntid = Mage::getStoreConfig(
            'payment/bradescoce2/merchantid',
            Mage::app()->getStore()
        );

        $chave = Mage::getStoreConfig(
            'payment/bradescoce2/assinatura',
            Mage::app()->getStore()
        );

        $cedente = Mage::getStoreConfig(
            'payment/bradescoce2/cedente',
            Mage::app()->getStore()
        );

        $valor = $pedido->getGrandTotal();
        $valor = str_replace(",", ".",$valor);
        $valor = str_replace("," , "", number_format($valor, 2, ",", "."));
        $valor = str_replace("." , "", $valor);

        $endereco =  $pedido->getBillingAddress();
        $rua = $endereco->getStreet1();
        $numero = $endereco->getStreet2();
        $complemento = $endereco->getStreet3();
        $bairro = $endereco->getStreet4();

        if($complemento == NULL || $complemento == ''){
            $enderecoCompleto = $rua . ', ' . $numero . ', ' . $bairro;
        } else {
            $enderecoCompleto = $rua . ', ' . $numero . ', ' . $complemento . ', ' . $bairro;
        }

        $cep = $endereco->getPostcode();
        $cep = str_replace('-', '', $cep);

        $cidade = $endereco->getCity();
        $cidade = str_replace(")", "", $cidade);
        $cidade = str_replace("(", "", $cidade);

        $estado = $endereco->getRegionCode();

        $nome = $endereco->getFirstname() . ' ' . $endereco->getLastname();
        $cgccpf = $cliente->getTaxvat();
        //it verify if cpf is empty
        if($cgccpf == '' || $cgccpf == NULL){
            $cgccpf = $pedido->getBillingAddress()->getVatId();
        }

        $storeId = $pedido->getStore();
        $instrucao1 = $configmodulo->getConfigData('instrucao1', $storeId);
        $instrucao2 = $configmodulo->getConfigData('instrucao2', $storeId);
        $instrucao3 = $configmodulo->getConfigData('instrucao3', $storeId);
        $instrucao4 = $configmodulo->getConfigData('instrucao4', $storeId);
        $instrucao5 = $configmodulo->getConfigData('instrucao5', $storeId);
        $instrucao6 = $configmodulo->getConfigData('instrucao6', $storeId);
        $instrucao7 = $configmodulo->getConfigData('instrucao7', $storeId);
        $instrucao8 = $configmodulo->getConfigData('instrucao8', $storeId);
        $instrucao9 = $configmodulo->getConfigData('instrucao9', $storeId);
        $instrucao10 = $configmodulo->getConfigData('instrucao10', $storeId);
        $instrucao11 = $configmodulo->getConfigData('instrucao11', $storeId);
        $instrucao12 = $configmodulo->getConfigData('instrucao12', $storeId);

        $carteira = $configmodulo->getConfigData('carteira', $storeId);
        if($carteira == '') {
            $carteira = 25;
        }


        $BradescoDiasdeVencimento = $configmodulo->getConfigData('diasvencimento', $storeId);
        $vencimento =  "86400" * $BradescoDiasdeVencimento + mktime(0,0,0,date('m'),date('d'),date('Y'));
        $vencimento = date ("Y-m-d", $vencimento);

        $dataatual  = date("Y-m-d", strtotime(now()));


        //monta o array
        $merchantId = $mercahntid;
        $chaveSeguranca = $chave;
        $data_service_pedido = array(
            "numero"    =>  $pedido->getIncrementId(),
            "valor"     =>  $valor,
            "descricao" =>  $_POST["pedido-descricao"]);
        $data_service_comprador_endereco = array(
            "cep"           =>  $cep,
            "logradouro"    =>  $rua,
            "numero"        =>  $numero,
            "complemento"   =>  $complemento,
            "bairro"        =>  $bairro,
            "cidade"        =>  $cidade,
            "uf"            =>  $estado);
        $data_service_comprador = array(
            "nome"          =>  $nome,
            "documento"     =>  $cgccpf,
            "endereco"      =>  $data_service_comprador_endereco,
            "ip"            =>  $_SERVER["REMOTE_ADDR"],
            "user_agent"    =>  $_SERVER["HTTP_USER_AGENT"]);
        $data_service_boleto_registro = null;
        $data_service_boleto_instrucoes = array(
            "instrucao_linha_1"     => $instrucao1,
            "instrucao_linha_2"     => $instrucao2,
            "instrucao_linha_3"     => $instrucao3,
            "instrucao_linha_4"     => $instrucao4,
            "instrucao_linha_5"     => $instrucao5,
            "instrucao_linha_6"     => $instrucao6,
            "instrucao_linha_7"     => $instrucao7,
            "instrucao_linha_8"     => $instrucao8,
            "instrucao_linha_9"     => $instrucao9,
            "instrucao_linha_10"    => $instrucao10,
            "instrucao_linha_11"    => $instrucao11,
            "instrucao_linha_12"    => $instrucao12);
        $data_service_boleto = array(
            "beneficiario"          => $cedente,
            "carteira"              => $carteira,
            "nosso_numero"          => $_POST["boleto-nossoNumero"],
            "data_emissao"          => $dataatual,
            "data_vencimento"       => $vencimento,
            "valor_titulo"          => $valor,
            "url_logotipo"          => $_POST["boleto-urlLogotipo"],
            "mensagem_cabecalho"    => $_POST["boleto-mensagemCabecalho"],
            "tipo_renderizacao"     => 0, //html
            "instrucoes"            => $data_service_boleto_instrucoes,
            "registro"              => $data_service_boleto_registro);
        $data_service_request = array(
            "merchant_id"       => $merchantId,
            "meio_pagamento"    => "300",
            "pedido"            => $data_service_pedido,
            "comprador"         => $data_service_comprador,
            "boleto"            => $data_service_boleto,
            "token_request_confirmacao_pagamento" => $_POST["token-request-confirmacao-pagamento"]);


        return $data_post = json_encode($data_service_request);
        $url = $url . "/transacao";


        //Configuracao do cabecalho da requisicao
        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Accept-Charset: UTF-8";
        $headers[] = "Accept-Encoding:  application/json";
        $headers[] = "Content-Type: application/json; charset=UTF-8";
        $AuthorizationHeader = $merchantId.":".$chaveSeguranca;
        $AuthorizationHeaderBase64 = base64_encode($AuthorizationHeader);
        $headers[] = "Authorization: Basic ".$AuthorizationHeaderBase64;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }





   public function getOrderPlaceRedirectUrl($orderId = 0)
	{
	   $params = array();
       $params['_secure'] = true;

	   if ($orderId != 0 && is_numeric($orderId)) {
	       $params['order_id'] = $orderId;
	   }


        return Mage::getUrl('bradescoce2/checkout/success', $params);
    }

}
