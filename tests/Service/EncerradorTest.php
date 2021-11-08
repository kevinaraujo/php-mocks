<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\EmailSender;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
  private $encerrador;
  private $fiat147;
  private $variant;

  /** @var MockObject */
  private $emailSender;

  protected function setUp(): void
  {
    $this->fiat147 = new Leilao(
      'Fiat 147 0km',
      new \DateTimeImmutable('8 days ago')
    );

    $this->variant = new Leilao(
      'Variant 1972 0km',
      new \DateTimeImmutable('10 days ago')
    );

    //$leilaoDao = $this->createMock(LeilaoDao::class);
    $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
      ->setConstructorArgs([new \PDO('sqlite::memory:')])
      ->getMock();

    $leilaoDao->method('recuperarNaoFinalizados')
      ->willReturn([$this->fiat147, $this->variant]);

    $leilaoDao->method('recuperarFinalizados')
    ->willReturn([$this->fiat147, $this->variant]);

    $leilaoDao->expects($this->exactly(2))
      ->method('atualiza')
      ->withConsecutive(
        [$this->fiat147],
        [$this->variant]
      );

      $this->emailSender = $this->createMock(EmailSender::class);
      $this->encerrador = new Encerrador($leilaoDao, $this->emailSender);
  }

  public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
  {
    
    $this->encerrador->encerra();

    $leiloes = [$this->fiat147, $this->variant];
    self::assertCount(2, $leiloes);
    self::assertTrue($leiloes[0]->estaFinalizado());
    self::assertTrue($leiloes[1]->estaFinalizado());
    self::assertEquals('Fiat 147 0km', $leiloes[0]->recuperarDescricao());
    self::assertEquals('Variant 1972 0km', $leiloes[1]->recuperarDescricao());
  }

  public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
  {
    $e = new \DomainException('Error to send email');
    
    $this->emailSender->expects($this->exactly(2))
      ->method('notifyEndOfAuction')
      ->willThrowException($e);

    $this->encerrador->encerra();
  }
}