<?php
namespace App\DTO\Letter;

class MemberDTO
{
    public string $role;
    public string $email;
    public string $decision;

    public function __construct(string $role, string $email, string $decision)
    {
        $this->role = $role;
        $this->email = $email;
        $this->decision = $decision;
    }
}
