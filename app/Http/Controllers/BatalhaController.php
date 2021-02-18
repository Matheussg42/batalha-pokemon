<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PokemonController;
use Illuminate\Http\Request;

class BatalhaController extends Controller
{
    private array $equipe1;
    private array $equipe2;
    private array $round;

    public function batalha(Request $request)
    {
        $this->equipe1 = $this->formarEquipe($request->all()['equipe1']);
        $this->equipe2 = $this->formarEquipe($request->all()['equipe2']);

        $this->prepararbatalha();

        $this->iniciaBatalha();

    }

    public function formarEquipe($pokemons):array
    {
        $equipeFormada=[];
        $pokemonController = new PokemonController();

        foreach ($pokemons as $pokemon){
            try {
                array_push($equipeFormada, $pokemonController->getPokemon($pokemon));
            } catch (\Exception $e) {
                throw new \DomainException($e);
            }
        }

        return $equipeFormada;
    }

    private function prepararbatalha()
    {
        $pokemonController = new PokemonController();

        $this->round = $pokemonController->getNextRound($this->equipe1, $this->equipe2);

    }

    private function iniciaBatalha(): void
    {
        $this->defineQuemInicia();

        echo "Vamos começar a batalha pokémon! <br>";
        echo "{$this->round[0]['nome']} x {$this->round[1]['nome']} <br>";
        echo "{$this->round[array_key_first($this->round)]['nome']} irá começar!<br><br>";

        while ($this->round[0]['stats']['hp'] > 0 && $this->round[1]['stats']['hp'] > 0):

            foreach ($this->round as $key => &$pokemon){
                $adversario = $key == 1 ? 0 : 1;
                $valor = mt_rand(0, 50);
                $porcentagem = $this->round[$adversario]['stats']['speed']/10;

                echo "{$pokemon['nome']} se prepara para atacar {$this->round[$adversario]['nome']} e... <br>";

                if($valor > $porcentagem){
                    $ataqueRand = array_rand($pokemon['ataques'], 1);
                    echo "Efetivo! {$pokemon['nome']} usa {$pokemon['ataques'][$ataqueRand]['nome']} em {$this->round[$adversario]['nome']} que sofre {$pokemon['ataques'][$ataqueRand]['dano']} de dano. <br>";

                    $this->round[$adversario]['stats']['hp'] -= $pokemon['ataques'][$ataqueRand]['dano'];
                    $this->round[$adversario]['stats']['hp'] = $this->round[$adversario]['stats']['hp'] <= 0 ? 0 : $this->round[$adversario]['stats']['hp'];

                    echo "{$this->round[$adversario]['nome']} possui {$this->round[$adversario]['stats']['hp']} de vida. <br><br>";
                }else{
                    echo "{$this->round[$adversario]['nome']} esquiva! <br><br>";
                }

                if($this->round[$adversario]['stats']['hp'] == 0){
                    echo "{$this->round[$adversario]['nome']} desmaiou! <br>";
                    break;
                }
            }
        endwhile;

//        $valor=mt_rand(0, 10);
//        $porcentagem = 2;
//
//        echo"<pre>";var_dump($valor);
//        echo"<pre>";var_dump($valor<=$porcentagem);die();
    }

    private function defineQuemInicia()
    {

        if($this->round[0]['stats']['speed'] > $this->round[1]['stats']['speed']){
            $this->round[0]['inicia'] = 1;
        } elseif ($this->round[0]['stats']['speed'] == $this->round[1]['stats']['speed']){
            $this->round[0]['inicia'] = 1;
        } else {
            $this->round[1]['inicia'] = 1;
        }

        uasort ( $this->round , function ($a, $b) {
            return $a['inicia'] < $b['inicia'];
        });
    }

}
