<?php
namespace App\Exceptions;

class FailedException extends BaseException {

    public function __construct($status_code,$keyname,$code,$messages) {
        $this->status_code = $status_code;
        $this->keyname = $keyname;
        $this->code = $code;
        $this->messages = $messages;
    }
}