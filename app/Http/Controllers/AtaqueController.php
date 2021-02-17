<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;

class AtaqueController extends Controller
{

    static function getAtaque($ataqueJSOn)
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

}
