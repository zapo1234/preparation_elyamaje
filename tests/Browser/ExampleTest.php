<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {

        // Génère l'étiquette & télécharge l'étiquette
        $this->browse(function (Browser $browser) {
            $browser->visit('https://staging.elyamaje.com/wp-admin')
                    ->type('input[type=text]','mmajeri@elyamaje.com')
                    ->type('input[type=password]','yAzHXGX1380,79')
                    ->press('input[type=submit]')
                    ->waitForText('Tableau de bord')
                    ->visit('https://staging.elyamaje.com/wp-admin/post.php?post=64922&action=edit')
                    ->waitForText('Modifier commande')
                    ->press('Ignore')
                    ->pause(500)
                    ->scrollTo("#lpc_banner-box")
                    ->pause(500)
                    ->click('.lpc__admin__order_banner__header__generation')
                    ->pause(500)
                    ->press('Générer')
                    ->press('Ignore')
                    ->pause(500)
                    ->scrollTo("#lpc_banner-box")
                    ->pause(500)
                    ->click('.lpc_label_action_download') 
                    ->pause(500);
        });
    }

}
