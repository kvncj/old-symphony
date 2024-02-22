<?php

namespace App\Controller;

use App\Model\Exception\Enum\ErrorLevel;
use App\Model\Exception\Enum\FlashLevel;
use App\Model\Exception\FlashException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ExtendedController extends AbstractController
{
    private string|array $data = [];
    private array $errors = [];

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log(string $message, string $level = LogLevel::DEBUG)
    {
        $this->logger->log($level, $message);
    }

    protected function error(int $caseNo, string $error, ErrorLevel $level = ErrorLevel::DANGER)
    {
        $this->errors[] = [
            'case' => $caseNo,
            'level' => $level,
            'message' => $error
        ];
    }

    protected function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    protected function getErrors(): array
    {
        return $this->errors;
    }

    protected function response(?array $data = null)
    {
        if (null !== $data)
            $this->data = $data;

        if ($this->hasErrors())
            return $this->json([
                'status' => 'error',
                'errors' => $this->errors
            ]);
        else {
            return $this->json([
                'status' => 'success',
                'data' => $this->data
            ]);
        }
    }

    protected function handleException(\Exception $e, ?string $message = null): int
    {
        $caseNo = rand();
        if ($e instanceof FlashException) {
            $this->addFlash($e->getLevel(), $e->getMessage());
        } else {
            $this->log("Case $caseNo: $message");
            $this->error($caseNo, $message ?? $e->getMessage());
            if ($message != null)
                $this->addFlash(FlashLevel::DANGER->value, "$caseNo: $message");
        }
        return $caseNo;
    }

    protected function validateCSRFToken(Request $request, string $token): void
    {
        $key = $request->get('_route');
        if (!$this->isCsrfTokenValid($key, $token))
            throw FlashException::danger('Invalid form token.');
    }
}
