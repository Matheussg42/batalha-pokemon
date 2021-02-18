<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AtaqueController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Http;

class PokemonController extends Controller
{
    private array $round = [];

    /**
     * Encontrar pokemon
     *
     * @param \App\Models\Pokemon $pokemon
     * @throws \Exception
     */
    public function getPokemon(string $pokemon)
    {
        $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$pokemon}");
        $response = $response->getBody();
        $response = json_decode($response);
        $PrimeiraGeracao = 151;
        $stats= [];

        if ($response->id > $PrimeiraGeracao) {
            throw new \Exception('Por favor, pesquise pokemons da primeira geração.', -404);
        }

        $type = $this->getTipoInfo($response->types[0]->type);

        $ataques = $this->getAtaque($response->moves);

        $stats = $this->getStats($response->stats);

        $pokemon=[
            'nome'=> $response->name,
            'peso'=> $response->weight,
            'altura'=> $response->height,
            'imagem'=> $response->sprites->back_default,
            'tipo'=> $type,
            'stats'=> $stats,
            'ataques'=> $ataques,
            'status' => 1,
            'inicia' => 0
        ];

        return $pokemon;
    }

    public function getNextRound(array $equipe1, array $equipe2)
    {
        foreach ($equipe1 as $pokemon){
            if($pokemon['stats']['hp'] > 0){
                array_push($this->round, $pokemon);
                break;
            }
        }

        foreach ($equipe2 as $pokemon){
            if($pokemon['stats']['hp'] > 0){
                array_push($this->round, $pokemon);
                break;
            }
        }

        $this->aplicarBonus();

        return $this->round;
    }

    private function aplicarBonus(): void
    {
        if(in_array($this->round[1]['tipo']['nome'], $this->round[0]['tipo']['infringeCriticoEm'])){
            $this->round[0]['ataques'][0]['dano']*=1.5;
            $this->round[0]['ataques'][1]['dano']*=1.5;
        }

        if(in_array($this->round[0]['tipo']['nome'], $this->round[1]['tipo']['infringeCriticoEm'])){
            $this->round[1]['ataques'][0]['dano']*=1.5;
            $this->round[1]['ataques'][1]['dano']*=1.5;
        }
    }

    private function getTipoInfo($type)
    {
        $info=['nome'=>$type->name];
        $response = Http::get($type->url);
        $response = $response->getBody();
        $response = json_decode($response);

//        foreach ()
        $info['sofreCriticoDe'] = array_map(
            function($array) { return $array->name; },
            $response->damage_relations->double_damage_from
        );

        $info['infringeCriticoEm'] = array_map(
            function($array) { return $array->name; },
            $response->damage_relations->double_damage_to
        );

        return $info;
    }

    private function getAtaque($ataqueJSOn)
    {
        $move1 = Http::get($ataqueJSOn[0]->move->url);
        $move1 = $move1->getBody();
        $move1 = json_decode($move1);

        $move2 = Http::get($ataqueJSOn[1]->move->url);
        $move2 = $move2->getBody();
        $move2 = json_decode($move2);

        return [
            ['nome'=>$move1->name,'dano' => $move1->pp],
            ['nome'=>$move2->name,'dano' => $move2->pp]
        ];
    }

    private function getStats($statsJSOn): array
    {
        $stats=[];
        foreach($statsJSOn as $stat){
            if($stat->stat->name == 'hp' || $stat->stat->name == 'speed'){
                $stats+= [$stat->stat->name => $stat->base_stat];
            }
        }
        return $stats;
    }
}
