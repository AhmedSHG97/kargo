<?php

namespace App\Services;

// require_once('vendor/iyzico/iyzipay-php/IyzipayBootstrap.php');

class IyzicoPayment
{

    public function __construct()
    {
        $this->options = new \Iyzipay\Options();
        $this->options->setApiKey(config("iyzico.api_key"));
        $this->options->setSecretKey(config("iyzico.secret_key"));
        $this->options->setBaseUrl(config("iyzico.uri"));
    }

    public function create(array $paymentRequest)
    {
        // $this->makeSubmerchant();
        $this->request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();;
        $this->request->setLocale(\Iyzipay\Model\Locale::EN);
        $this->request->setConversationId("123456789");
        $this->request->setPrice($paymentRequest['price']);
        $this->request->setPaidPrice($paymentRequest['paid_price']);
        $this->request->setCurrency(\Iyzipay\Model\Currency::TL);
        $this->request->setEnabledInstallments(array(2, 3, 6, 9));
        $this->request->setBasketId($paymentRequest["order_id"]);
        $this->request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $this->request->setCallbackUrl(url("api/payment/callback"));


        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId("BY" . $paymentRequest['buyer_id']);
        $buyer->setName($paymentRequest['buyer_first_name']);
        $buyer->setSurname($paymentRequest['buyer_last_name']);
        $buyer->setGsmNumber($paymentRequest['buyer_phone']);
        $buyer->setEmail($paymentRequest['buyer_email']);
        $buyer->setIdentityNumber($paymentRequest['buyer_identity_number']);
        $buyer->setLastLoginDate("2015-10-05 12:43:35");
        $buyer->setRegistrationDate("2013-04-21 15:12:09");
        $buyer->setRegistrationAddress($paymentRequest['buyer_address']);
        $buyer->setIp($paymentRequest['buyer_ip']);
        $buyer->setCity($paymentRequest['buyer_city']);
        $buyer->setCountry($paymentRequest['buyer_country']);
        // $buyer->setZipCode("34732");
        $this->request->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($paymentRequest['shipping_contact_name']);
        $shippingAddress->setCity($paymentRequest['shipping_city']);
        $shippingAddress->setCountry($paymentRequest['shipping_country']);
        $shippingAddress->setAddress($paymentRequest['shipping_address']);
        $shippingAddress->setZipCode("34742");
        $this->request->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($paymentRequest['billing_contact_name']);
        $billingAddress->setCity($paymentRequest['billing_city']);
        $billingAddress->setCountry($paymentRequest['billing_country']);
        $billingAddress->setAddress($paymentRequest['billing_address']);
        $billingAddress->setZipCode("34742");
        $this->request->setBillingAddress($billingAddress);

        $basketItems = array();
        foreach ($paymentRequest['basket_items'] as $item) {
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId("BI" . $item['id']);
            $basketItem->setName($item['name']);
            $basketItem->setCategory1($item['category']);
            $basketItem->setSubMerchantKey("nE0JHvoX+tc1YorogsNX79NI1jE=");
            $basketItem->setSubMerchantPrice(1);
            // $basketItem->setCategory2("Accessories");
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $basketItem->setPrice($item['price']);
            array_push($basketItems, $basketItem);
        }
        $this->request->setBasketItems($basketItems);

        $payment = \Iyzipay\Model\CheckoutFormInitialize::create($this->request, $this->options);
        return $payment;
    }

    public function makeSubmerchant()
    {
        $request = new \Iyzipay\Request\CreateSubMerchantRequest();
        $request->setLocale(\Iyzipay\Model\Locale::EN);
        $request->setConversationId("123456789");
        $request->setSubMerchantExternalId("AS20120");
        $request->setSubMerchantType(\Iyzipay\Model\SubMerchantType::LIMITED_OR_JOINT_STOCK_COMPANY);
        $request->setAddress("19 Mayıs Mahallesi Balçık Tarlası Sokak Dış Kapı No:1 Kat:8 Daire:10
        Şişli/İstanbul");
        $request->setTaxOffice("Tax office");
        $request->setTaxNumber("4444615");
        $request->setLegalCompanyTitle("Bennebos market");
        $request->setEmail("email@submerchantemail.com");
        $request->setGsmNumber("+905074044615");
        $request->setName("bennebos market");
        $request->setIban("TR950020500009763963500001");
        $request->setCurrency(\Iyzipay\Model\Currency::TL);

        $subMerchant = \Iyzipay\Model\SubMerchant::create($request, $this->options);
        $submerchantKey = $subMerchant->getSubMerchantKey();
        dd($subMerchant);
    }
    public function callback($conversationId, $token)
    {
        $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($conversationId);
        $request->setToken($token);

        $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $this->options);
        return $checkoutForm;
    }

    public function approveTransaction($payment_id){
        $request = new \Iyzipay\Request\CreateApprovalRequest();
        $request->setLocale(\Iyzipay\Model\Locale::EN);
        $request->setConversationId("123456789");
        $request->setPaymentTransactionId($payment_id);

        $approval = \Iyzipay\Model\Approval::create($request, $this->options);
    }
}
