<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_user_can_switch_to_french(): void
    {
        $this->from('/login')->get('/language/fr')->assertRedirect('/login');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Connexion')
            ->assertSee('Inscription');
    }
}
