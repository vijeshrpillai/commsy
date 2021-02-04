<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class AuthSourceLocal extends AuthSource
{
    public function getType(): string
    {
        return 'local';
    }
}