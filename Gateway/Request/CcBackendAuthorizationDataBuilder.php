<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2019 Adyen BV (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Gateway\Request;

use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Observer\AdyenCcDataAssignObserver;
use Magento\Backend\Model\Session\Quote;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CcBackendAuthorizationDataBuilder implements BuilderInterface
{
    /**
     * @var StateData
     */
    private $stateData;
    /**
     * @var Quote
     */
    private $quote;

    public function __construct(
        StateData $stateData,
        Quote $quote
    ) {
        $this->stateData = $stateData;
        $this->quote = $quote;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $requestBody = $this->stateData->getStateData($this->quote->getQuoteId());

        // if installments is set add it into the request
        $installments = $payment->getAdditionalInformation(AdyenCcDataAssignObserver::NUMBER_OF_INSTALLMENTS) ?: 0;
        if ($installments > 0) {
            $requestBody['installments']['value'] = $installments;
        }

        return [
            'body' => $requestBody
        ];
    }
}
