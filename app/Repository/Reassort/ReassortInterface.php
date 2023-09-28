<?php

namespace App\Repository\Reassort;

interface ReassortInterface
{
    public function getReassortByUser($user_id);
    public function findByIdentifiantReassort($identifiant, $cle = null);
    public function deleteByIdentifiantReassort($identifiant);
    public function update_in_hist_reassort($identifiant, $colonnes_values);
}




