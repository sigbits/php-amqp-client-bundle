<?php

declare(strict_types=1);

namespace Sigbits\AmqpBundle\Connection;

use Sigbits\Amqp\Client\Connection;
use Sigbits\Amqp\Protocol\Sasl\SaslClient;
use Sigbits\Amqp\Transport\TlsOptions;

final readonly class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @param array{
     *     verify_peer: bool,
     *     verify_peer_name: bool,
     *     peer_name: string|null,
     *     cafile: string|null,
     *     local_cert: string|null
     * }|null $tls
     * @param array{
     *     mechanism: 'auto'|'anonymous'|'plain',
     *     username: string,
     *     password: string,
     *     authorization_id: string
     * } $sasl
     */
    public function __construct(
        private string $uri,
        private string $containerId,
        private float $timeoutSeconds,
        private ?array $tls,
        private array $sasl,
    ) {
    }

    public function connect(): Connection
    {
        return Connection::connect(
            uri: $this->uri,
            saslClient: $this->createSaslClient(),
            containerId: $this->containerId,
            timeoutSeconds: $this->timeoutSeconds,
            tls: $this->createTlsOptions(),
        );
    }

    private function createSaslClient(): ?SaslClient
    {
        return match ($this->sasl['mechanism']) {
            'auto' => null,
            'anonymous' => SaslClient::anonymous(),
            'plain' => SaslClient::plain(
                username: $this->sasl['username'],
                password: $this->sasl['password'],
                authorizationId: $this->sasl['authorization_id'],
            ),
        };
    }

    private function createTlsOptions(): ?TlsOptions
    {
        if ($this->tls === null) {
            return null;
        }

        return new TlsOptions(
            verifyPeer: $this->tls['verify_peer'],
            verifyPeerName: $this->tls['verify_peer_name'],
            peerName: $this->tls['peer_name'],
            cafile: $this->tls['cafile'],
            localCert: $this->tls['local_cert'],
        );
    }
}
