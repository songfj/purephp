<?php

class pure_error extends Exception{
    public function __construct($message, $code, $previous) {
        parent::__construct('Pure Error: '.$message, $code, $previous);
    }
}