<?php

class InvestigationLinesController{
    private $service;

    public function __construct(InvestigationLinesService $service){
        $this->service = $service;
    }

    
}