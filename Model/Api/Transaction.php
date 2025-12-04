<?php

namespace Rule\RuleMailer\Model\Api;

use Magento\Framework\Mail\Address;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Rule\ApiWrapper\Api\Api;
use Rule\ApiWrapper\Api\Exception\InvalidResourceException;
use Rule\ApiWrapper\ApiFactory;

/**
 * Class Transaction holds transaction during API calls
 */
class Transaction
{
    /**
     * @var Api
     */
    private $transactionApi;

    /**
     * Transaction constructor.
     * @param $apiKey
     * @throws InvalidResourceException
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct($apiKey)
    {
        $this->transactionApi = ApiFactory::make($apiKey, 'transaction');
    }

    /**
     * Send email message via Rule Transactional API
     *
     * Note: The 'name' field is only included in from/to arrays when not empty,
     * as the Rule API requires name to be a non-empty string when present.
     * This handles cases where Magento's Address::getName() returns null.
     *
     * @param MessageInterface|MailMessageInterface|EmailMessageInterface $message
     *
     * @return void
     * @see https://apidoc.rule.se/#transactions-send-transaction
     */
    public function sendMessage($message)
    {
        $transaction = [];

        if ($message instanceof EmailMessageInterface) {
            /** @var EmailMessage $message */

            $from = $message->getFrom();
            $from1 = array_shift($from);

            $recipients = $message->getTo();
            foreach ($recipients as $recipient) {
                /** @var Address $recipient */
                $recipientName = $recipient->getName();
                $fromName = $from1->getName();
                $fromArray = ['email' => $from1->getEmail()];

                if (!empty($fromName)) {
                    $fromArray['name'] = $fromName;
                }

                $toArray = ['email' => $recipient->getEmail()];
                if (!empty($recipientName)) {
                    $toArray['name'] = $recipientName;
                }

                $transaction = [
                    'transaction_type' => 'email',
                    'transaction_name' => 'some',
                    'subject' => $message->getSubject(),
                    'from' => $fromArray,
                    'to' => $toArray,
                    'content' => [
                        'plain' => quoted_printable_decode($message->getBodyText()),
                        'html' => quoted_printable_decode($message->getBodyText())
                    ]
                ];
            }
        }

        if ($message instanceof MailMessageInterface) {
            // @todo
        } elseif ($message instanceof MessageInterface) {
            /** @var MessageInterface $message */
            // @todo
        }

        $this->transactionApi->send($transaction);
    }
}

