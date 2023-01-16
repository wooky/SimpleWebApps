<?php declare(strict_types = 1);
namespace SimpleWebApps\Auth;

use SimpleWebApps\Entity\User;

interface Ownable
{
  function getOwner(): ?User;
}
