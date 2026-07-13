<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MoodleFlowLogger
{
    public function begin(string $flow, array $context = []): string
    {
        $traceId = (string) Str::uuid();

        $this->write('info', $traceId, $flow, 'flow.started', $context);

        return $traceId;
    }

    public function step(string $traceId, string $flow, string $step, array $context = []): void
    {
        $this->write('info', $traceId, $flow, $step, $context);
    }

    public function warning(string $traceId, string $flow, string $step, array $context = []): void
    {
        $this->write('warning', $traceId, $flow, $step, $context);
    }

    public function failure(string $traceId, string $flow, string $step, Throwable $exception, array $context = []): void
    {
        $this->write('error', $traceId, $flow, $step, [
            ...$context,
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
        ]);
    }

    public function success(string $traceId, string $flow, array $context = []): void
    {
        $this->write('info', $traceId, $flow, 'flow.completed', $context);
    }

    private function write(string $level, string $traceId, string $flow, string $step, array $context): void
    {
        Log::channel('moodle')->log($level, '[MoodleFlow] '.$step, [
            'trace_id' => $traceId,
            'flow' => $flow,
            'step' => $step,
            ...$this->sanitize($context),
        ]);
    }

    private function sanitize(array $context): array
    {
        $sensitiveKeys = [
            'password',
            'token',
            'secret',
            'verification_code',
            'verification_code_hash',
            'lookup_value',
        ];

        foreach ($context as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $context[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $context[$key] = $this->sanitize($value);
            }
        }

        return $context;
    }
}
