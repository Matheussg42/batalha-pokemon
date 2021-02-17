<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;
use App\Http\Controllers\AtaqueController;
use Illuminate\Support\Facades\Http;

class PokemonController extends Controller
{

    /**
     * Encontrar pokemon
     *
     * @param \App\Models\Pokemon $pokemon
     * @throws \Exception
     */
    public function getPokemon($pokemon)
    {
        $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$pokemon}");
        $response = $response->getBody();
        $response = json_decode($response);
        $PrimeiraGeracao = 151;
        $stats= [];

        if ($response->id > $PrimeiraGeracao) {
            throw new \Exception('Por favor, pesquise pokemons da primeira geração.', -404);
        }

        $type = $response->types[0]->type->name;

        $ataque = new AtaqueController();
        $ataques = $ataque->getAtaque($response->moves);

        echo"<pre>";print_r($response->stats);die();
        $newStats = array_filter(json_decode($response->stats), function($stat) {
            return $stat->stat->name == 'hp' || $stat->stat->name == 'speed';
        }, ARRAY_FILTER_USE_KEY);



        foreach($response->stats as $stat){
            $stats+= [$stat->stat->name => $stat->base_stat];
        }

        $pokemon=[
            'nome'=> $response->name,
            'peso'=> $response->weight,
            'altura'=> $response->height,
            'imagem'=> $response->sprites->back_default,
            'tipo'=> $type,
            'stats'=> $stats,
            'ataques'=> $ataques
        ];

        return $pokemon;
    }
}
