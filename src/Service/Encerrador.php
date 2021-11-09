<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;

class Encerrador
{
    private $dao;
    private $emailSender;

    public function __construct(LeilaoDao $dao, EmailSender $emailSender) 
    {
        $this->dao = $dao;
        $this->emailSender = $emailSender;
    }

    public function encerra()
    {
        $leiloes = $this->dao->recuperarNaoFinalizados();
       
        foreach ($leiloes as $leilao) {
            if ($leilao->temMaisDeUmaSemana()) {
                    
                try {

                    $leilao->finaliza();
                    $this->dao->atualiza($leilao);
                    $this->emailSender->notifyEndOfAuction($leilao);

                } catch (\DomainException $ex) {
                    error_log($ex->getMessage());
                }
            }
        }
    }
}
