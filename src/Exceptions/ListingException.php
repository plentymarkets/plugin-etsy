<?php

namespace Etsy\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Throwable;

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
     * @param string $message
     * @param MessageBag $messageBag
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(MessageBag $messageBag, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->messageBag = $messageBag;
        parent::__construct($message, $code, $previous);
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