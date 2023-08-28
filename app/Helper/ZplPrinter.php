<?php

namespace App\Helper;

class ZplPrinter
{

    /**
     * socket
     *
     * @var mixed
     */
    protected $socket;

    /**
     * constructor
     *
     * @param  mixed $host
     * @param  mixed $port
     * @return void
     */
    public function __construct(string $host, int $port = 9100)
    {
        $this->connect($host, $port);
    }

    /**
     * destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * create an instance to manipulate printer
     *
     * @param  mixed $host
     * @param  mixed $port
     * @return self
     */
    public static function printer(string $host, int $port = 9100): self
    {
        return new static($host, $port);
    }

    /**
     * connect to the printer
     *
     * @param  mixed $host
     * @param  mixed $port
     * @return void
     */
    protected function connect(string $host, int $port): void
    {
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket || !@socket_connect($this->socket, $host, $port)) {
            $error = $this->getLastError();
            report($error);
        }
    }

    /**
     * disconnect to the printer
     *
     * @return void
     */
    protected function disconnect(): void
    {
        @socket_close($this->socket);
    }

    /**
     * send ZPL data to printer.
     *
     * @param  mixed $zpl
     * @return void
     */
    public function send(string $zpl): void
    {
        if (!@socket_write($this->socket, $zpl)) {
            $error = $this->getLastError();
            report($error);
        }
    }

    /**
     * getLastError
     *
     * @return array
     */
    protected function getLastError(): array
    {
        $code = socket_last_error($this->socket);
        $message = socket_strerror($code);
        return compact('code', 'message');
    }
}