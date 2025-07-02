<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run()
    {
        Country::insert([
            ['name' => 'Algérie'],
            ['name' => 'Angola'],
            ['name' => 'Bénin'],
            ['name' => 'Botswana'],
            ['name' => 'Burkina Faso'],
            ['name' => 'Burundi'],
            ['name' => 'Cabo Verde'],
            ['name' => 'Cameroun'],
            ['name' => 'République centrafricaine'],
            ['name' => 'Tchad'],
            ['name' => 'Comores'],
            ['name' => 'République du Congo'],
            ['name' => 'République démocratique du Congo'],
            ['name' => 'Côte d\'Ivoire'],
            ['name' => 'Djibouti'],
            ['name' => 'Égypte'],
            ['name' => 'Guinée équatoriale'],
            ['name' => 'Érythrée'],
            ['name' => 'Eswatini'],
            ['name' => 'Éthiopie'],
            ['name' => 'Gabon'],
            ['name' => 'Gambie'],
            ['name' => 'Ghana'],
            ['name' => 'Guinée'],
            ['name' => 'Guinée-Bissau'],
            ['name' => 'Kenya'],
            ['name' => 'Lesotho'],
            ['name' => 'Libéria'],
            ['name' => 'Libye'],
            ['name' => 'Madagascar'],
            ['name' => 'Malawi'],
            ['name' => 'Mali'],
            ['name' => 'Mauritanie'],
            ['name' => 'Maurice'],
            ['name' => 'Maroc'],
            ['name' => 'Mozambique'],
            ['name' => 'Namibie'],
            ['name' => 'Niger'],
            ['name' => 'Nigéria'],
            ['name' => 'Rwanda'],
            ['name' => 'Sao Tomé-et-Principe'],
            ['name' => 'Sénégal'],
            ['name' => 'Seychelles'],
            ['name' => 'Sierra Leone'],
            ['name' => 'Somalie'],
            ['name' => 'Afrique du Sud'],
            ['name' => 'Soudan'],
            ['name' => 'Soudan du Sud'],
            ['name' => 'Tanzanie'],
            ['name' => 'Togo'],
            ['name' => 'Tunisie'],
            ['name' => 'Ouganda'],
            ['name' => 'Zambie'],
            ['name' => 'Zimbabwe'],
        ]);
    }
}
