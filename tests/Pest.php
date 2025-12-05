<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Hace que $this->get(), actingAs(), etc. estén disponibles en closures de Pest
uses(TestCase::class)->in('Feature', 'Unit');

// Opcional (si quieres base de datos limpia por test Feature):
uses(RefreshDatabase::class)->in('Feature');
