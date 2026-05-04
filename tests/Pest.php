<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Konfigurasi Pest PHP untuk Sistem Penggajian PT IPM.
| Menggunakan TestCase Laravel untuk Feature test dan
| TestCase dasar untuk Unit test.
|
*/

uses(Tests\TestCase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Property');
uses(Tests\TestCase::class)->in('Unit/Models');
uses(Tests\TestCase::class)->in('Unit/Traits');
uses(Tests\TestCase::class)->in('Unit/Services');
uses(Tests\TestCase::class)->in('Unit/Middleware');
uses(Tests\TestCase::class)->in('Unit/Policies');
uses(Tests\TestCase::class)->in('Unit/Observers');
