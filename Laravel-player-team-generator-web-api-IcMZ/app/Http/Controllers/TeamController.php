<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessTeamRequest;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    public function processTeam(ProcessTeamRequest $request){
       
        $team=$request->all();

        //validar que no haya repeticiones de la misma habilidad para la misma posicion
        $repeats=collect($team)
        ->groupBy(fn($search)=>$search['position'].'/'.$search['mainSkill'])
        ->filter(fn($group)=>count($group)>1);

        if(count($repeats)>0){
            return response(['message'=>"The position/skill set cannot be repeated"], Response::HTTP_BAD_REQUEST);
        }

        $results=collect();
        foreach($team as $search){
            $registerLeft=$search['numberOfPlayers'];
            //tomar todos los registros que se pueda con los criterios seleccionados
            $registersMainSkill=Player::where('position',$search['position'])
            ->select('players.id','name','position')
            ->join('player_skills','players.id','=','player_skills.player_id')
            //para que no se repitan los jugadores ya tomados
            ->whereNotIn('players.id',$results->pluck('id'))
            ->where('skill',$search['mainSkill'])
            ->orderBy('player_skills.value','desc')
            ->groupBy('players.id')
            ->limit($registerLeft);

            //medir cuantos faltan
            $registerLeft-=count($registersMainSkill->get());
            
            //obtener (si es necesario) jugadores de la misma posicion pero con otra skill
            $registersSecondarySkill=Player::where('position',$search['position'])
            ->select('players.id','name','position')
            ->join('player_skills','players.id','=','player_skills.player_id')
            //para que no se repitan los jugadores ya tomados
            ->whereNotIn('players.id',$results->pluck('id'))
            ->whereNotIn('players.id',$registersMainSkill->pluck('id'))
            ->orderBy('player_skills.value','desc')
            ->groupBy('players.id')
            ->limit($registerLeft)
            ->get();

            //juntar los resultados manteniendo su orden original
            $partialResults=$registersMainSkill->get()->merge($registersSecondarySkill);

            //corroborar que tengamos la cantidad indicada
            if(count($partialResults)!=$search['numberOfPlayers']){
                return response([
                    'message'=>"Insufficient number of players for position: ".$search['position']
                ],Response::HTTP_NOT_FOUND);
            }

            //añadir a los resultados
            $results=$results->merge($partialResults);      
            
        }
        return response($results,Response::HTTP_OK);
    }
}
