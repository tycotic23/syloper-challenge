<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////

namespace App\Http\Controllers;

use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\PlayerSkill;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PlayerController extends Controller
{
    public function index()
    {
        return response(PlayerResource::collection(Player::all()),Response::HTTP_OK);
    }

    public function show(int $id)
    {
        $player=Player::find($id);
        if($player){
            return response(new PlayerResource($player),Response::HTTP_OK);
        }
        return response([
            'message'=>'Player not found'
        ], Response::HTTP_NOT_FOUND);
    }

    public function store(StorePlayerRequest $request)
    {
        DB::beginTransaction();
        //crear el jugador
        $player=Player::create([
            'name' =>$request->name,
            'position' =>$request->position,
        ]);

        //preparar el conjunto de skills
        $skills=$request->playerSkills;
        foreach($skills as &$skill){
            $skill['player_id']=$player->id;
        }

        //validar que no haya repeticiones de la misma skill para el mismo player
        $repeats=collect($skills)
        ->groupBy(fn($skill)=>$skill['player_id'].'/'.$skill['skill'])
        ->filter(fn($group)=>count($group)>1);

        if(count($repeats)>0){
            DB::rollBack();
            return response(['message'=>"The skill cannot be repeated"], Response::HTTP_BAD_REQUEST);
        }

        //guardar las skills
        PlayerSkill::insert($skills);
        DB::commit();
        return response(new PlayerResource(Player::find($player->id)),Response::HTTP_CREATED);
    }

    public function update(int $id,UpdatePlayerRequest $request)
    {
        DB::beginTransaction();
        //buscar el jugador
        $player=Player::find($id);
        if(!$player){
            return response(['message'=>"Incorrect id"], Response::HTTP_NOT_FOUND);
        }
        
        //modificar el jugador (si es necesario)
        if(isset($request->name)){
            $player->update(['name'=>$request->name]);
        }
        if(isset($request->position)){
            $player->update(['position'=>$request->position]);
        }

        //modificar skills
        if(isset($request->playerSkills) && count($request->playerSkills)>0){
            

            //borrar skills faltantes

            //obtengo los id nuevos
            $idsActuales=array_map(function ($skill){
                return isset($skill['id'])?$skill['id']:null;
            },$request->playerSkills);
            $idsActuales=array_filter($idsActuales,fn($id)=>!is_null($id));

            //borrar las skills del jugador que no se encuentran en la lista
            $player->skills()->whereNotIn('id',$idsActuales)->delete();

            //crear/modificar las skills de la lista

            //preparar el conjunto de skills
            $skills=$request->playerSkills;
            foreach($skills as &$skill){
                $skill['player_id']=$player->id;
            }

            //validar que no haya repeticiones de la misma skill para el mismo player
            $repeats=collect($skills)
            ->groupBy(fn($skill)=>$skill['player_id'].'/'.$skill['skill'])
            ->filter(fn($group)=>count($group)>1);

            if(count($repeats)>0){
                DB::rollBack();
                return response(['message'=>"The skill cannot be repeated"], Response::HTTP_BAD_REQUEST);
            }
            
            //guardar
            $player->skills()->upsert($skills,['id']);

        }
        DB::commit();
        return response(new PlayerResource(Player::find($id)), Response::HTTP_OK);
        
    }

    public function destroy(int $id)
    {
        $player=Player::find($id);
        if(!$player){
            return response(['message'=>"Id incorrecta"], Response::HTTP_NOT_FOUND);
        }

        $player->delete();
        return response("", Response::HTTP_NO_CONTENT);
    }
}
