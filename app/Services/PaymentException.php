<?php

namespace App\Services;

/** A payment step that failed in a way worth telling the customer (the message is plain language and safe to show). */
class PaymentException extends \RuntimeException
{
}
