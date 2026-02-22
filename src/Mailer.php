<?php

declare(strict_types=1);

final class Mailer
{
    public function sendLeadNotification(array $lead, string $tenantKey): bool
    {
        $to = env('LEAD_NOTIFY_TO', env('SMTP_FROM', ''));
        $from = env('SMTP_FROM', 'no-reply@actatechnology.dk');

        if ($to === '') {
            return false;
        }

        $subject = sprintf('[%s] New lead from %s', $tenantKey, (string) ($lead['name'] ?? 'Unknown'));
        $body = $this->buildLeadBody($lead, $tenantKey);

        return $this->send($to, $subject, $body, $from);
    }

    public function send(string $to, string $subject, string $body, string $from): bool
    {
        $host = env('SMTP_HOST', '');
        $port = (int) env('SMTP_PORT', '587');
        $username = env('SMTP_USERNAME', '');
        $password = env('SMTP_PASSWORD', '');

        if ($host !== '' && $username !== '' && $password !== '') {
            return $this->sendViaSmtp($host, $port, $username, $password, $from, $to, $subject, $body);
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from,
            'Reply-To: ' . $from,
        ];

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private function sendViaSmtp(string $host, int $port, string $username, string $password, string $from, string $to, string $subject, string $body): bool
    {
        $timeout = 15;
        $transportHost = $port === 465 ? 'ssl://' . $host : $host;
        $socket = @fsockopen($transportHost, $port, $errno, $errstr, $timeout);
        if ($socket === false) {
            return false;
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO actatechnology.dk', [250]);

            if ($port !== 465) {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($socket);
                    return false;
                }
                $this->command($socket, 'EHLO actatechnology.dk', [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);

            $this->command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = "Subject: {$subject}\r\n" .
                "To: {$to}\r\n" .
                "From: {$from}\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n\r\n" .
                $body . "\r\n.";

            $this->command($socket, $message, [250]);
            $this->command($socket, 'QUIT', [221]);
            fclose($socket);
            return true;
        } catch (Throwable $exception) {
            fclose($socket);
            return false;
        }
    }

    private function command($socket, string $command, array $expectedCodes): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4) {
                continue;
            }

            $code = (int) substr($line, 0, 3);
            $separator = substr($line, 3, 1);
            if ($separator === ' ') {
                if (!in_array($code, $expectedCodes, true)) {
                    throw new RuntimeException('SMTP unexpected response: ' . trim($response));
                }
                return $response;
            }
        }

        throw new RuntimeException('SMTP no response');
    }

    private function buildLeadBody(array $lead, string $tenantKey): string
    {
        $lines = [
            'New lead received',
            'Tenant: ' . $tenantKey,
            'Name: ' . (string) ($lead['name'] ?? ''),
            'Email: ' . (string) ($lead['email'] ?? ''),
            'Company: ' . (string) ($lead['company'] ?? ''),
            'Phone: ' . (string) ($lead['phone'] ?? ''),
            'Service: ' . (string) ($lead['service_key'] ?? ''),
            'Locale: ' . (string) ($lead['locale'] ?? ''),
            'Source host: ' . (string) ($lead['source_host'] ?? ''),
            'Consent: ' . (!empty($lead['consent']) ? 'yes' : 'no'),
            '',
            'Message:',
            (string) ($lead['message'] ?? ''),
            '',
            'Created at: ' . date('c'),
        ];

        return implode("\n", $lines);
    }
}
