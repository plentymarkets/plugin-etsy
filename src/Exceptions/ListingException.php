<?php

namespace Etsy\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

/**
 * @author H.Malicha
 * Class ListingException
 */
class ListingException extends Exception
{
    /**
     * @var MessageBag
     */
    protected $messageBag;

    /**
     * ListingException constructor.
     * @param MessageBag $messageBag
     * @param string $message
     * @param int $code
     */
    public function __construct(MessageBag $messageBag, $message = "", $code = 0)
    {
        $this->messageBag = $messageBag;
        parent::__construct($message, $code);
    }

    /**
     * @return MessageBag|null
     */
    public function getMessageBag()
    {
        return $this->messageBag;
    }

    /**
     * @param MessageBag $messageBag
     * @return $this
     */
    public function setMessageBag($messageBag)
    {
        $this->messageBag = $messageBag;

        return $this;
    }
}