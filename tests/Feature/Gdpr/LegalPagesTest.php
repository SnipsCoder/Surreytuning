<?php

namespace Tests\Feature\Gdpr;

use App\Models\Setting;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_page_renders(): void
    {
        Setting::get()->update(['terms_and_conditions' => 'These are our bespoke trading terms.']);

        $this->get('/terms')
            ->assertOk()
            ->assertSee('Terms')
            ->assertSee('These are our bespoke trading terms.');
    }

    public function test_privacy_page_renders(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy Policy');
    }
}
