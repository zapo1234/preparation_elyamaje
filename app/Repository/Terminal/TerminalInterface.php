<?php

namespace App\Repository\Terminal;


interface TerminalInterface
{
    public function getTerminal();

    public function insert($data);

    public function update($terminal_id, $data);

    public function delete($terminal_id);
}




