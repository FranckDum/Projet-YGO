<?php

namespace App\Service;

class NumerosCommandesGeneratorService
{
    private $codeManagerService;

    public function __construct(CodeManagerService $codeManager)
    {
        $this->codeManagerService = $codeManager;
    }

    public function generateNumeros(): string
    {
        $date = new \DateTime();
        $code = $this->codeManagerService->getCode();
        return $date->format('YmdHis') . '-' . $code;
    }
}
