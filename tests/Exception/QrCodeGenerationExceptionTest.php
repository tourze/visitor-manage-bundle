<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\VisitorManageBundle\Exception\QrCodeGenerationException;
use Tourze\VisitorManageBundle\Exception\VisitorManageException;

/**
 * @internal
 */
#[CoversClass(QrCodeGenerationException::class)]
class QrCodeGenerationExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $exception = new QrCodeGenerationException();

        $this->assertInstanceOf(VisitorManageException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('二维码生成失败', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testExceptionWithCustomMessage(): void
    {
        $message = '自定义错误消息';
        $code = 500;

        $exception = new QrCodeGenerationException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('之前的异常');
        $exception = new QrCodeGenerationException('二维码生成失败', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
