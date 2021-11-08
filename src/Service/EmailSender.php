<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EmailSender 
{
    public function notifyEndOfAuction(Leilao $leilao) 
    {
        $success = mail(
            'user@email.com',
            'Auction finished.',
            sprintf('The Auction %s was finished.', $leilao->recuperarDescricao())
        );

        if (!$success) {
            throw new \Exception('Error to send email');
        }
    }
}