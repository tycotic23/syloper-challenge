/**
 * A class representing a service that processes the data for match schedule
 * and generates leaderboard.
 * 
 * NOTE: MAKE SURE TO IMPLEMENT ALL EXISITNG METHODS BELOW WITHOUT CHANGING THE INTERFACE OF THEM, 
 *       AND PLEASE DO NOT RENAME, MOVE OR DELETE THIS FILE.  
 * 
 */

import { Injectable } from '@angular/core'
import { Match } from '../models/match';
import { TeamResults } from '../models/teamResults';

@Injectable({
  providedIn: 'root'
})

export class LeagueService {

  matches: Match[];
  leaderBoard: TeamResults[];
  urlAPI:string="http://localhost:3001/api/v1/";

  constructor () {}

  /**
   * Sets the match schedule.
   * Match schedule will be given in the following form:
   * [
   *      {
   *          matchDate: [TIMESTAMP],
   *          stadium: [STRING],
   *          homeTeam: [STRING],
   *          awayTeam: [STRING],
   *          matchPlayed: [BOOLEAN],
   *          homeTeamScore: [INTEGER],
   *          awayTeamScore: [INTEGER]
   *      },
   *      {
   *          matchDate: [TIMESTAMP],
   *          stadium: [STRING],
   *          homeTeam: [STRING],
   *          awayTeam: [STRING],
   *          matchPlayed: [BOOLEAN],
   *          homeTeamScore: [INTEGER],
   *          awayTeamScore: [INTEGER]
   *      }
   * ]
   *
   * @param {Array} matches List of matches.
   */

  setMatches (matches: any[]) {
    this.matches=matches;
  }

  /**
   * Returns the full list of matches.
   *
   * @returns {Array} List of matches.
   */
  getMatches () {
    return this.matches;
  }

  /**
   * Returns the leaderBoard in a form of a list of JSON objecs.
   *
   * [
   *      {
   *          teamName: [STRING]',
   *          matchesPlayed: [INTEGER],
   *          goalsFor: [INTEGER],
   *          goalsAgainst: [INTEGER],
   *          points: [INTEGER]
   *      },
   * ]
   *
   * @returns {Array} List of teams representing the leaderBoard.
   */
   getLeaderBoard () {

    return this.leaderBoard;
   }

   generateLeaderBoard(matches:Match[]){
    let processMatches:TeamResults[]=[];

    matches.forEach(match=>{
      //obtener puntaje
      let pointsHomeScored=0;
      let pointsAwayScored=0;

      if(match.matchPlayed){

        if(match.homeTeamScore==match.awayTeamScore){
          pointsAwayScored=1;
          pointsHomeScored=1;
        }else if(match.homeTeamScore>match.awayTeamScore){
          pointsHomeScored=3;
        }else{
          pointsAwayScored=3;
        }
      }

      //modificar puntos del homeTeam
      if(!processMatches[match.homeTeam]){
        processMatches[match.homeTeam]={
          teamName:match.homeTeam,
          matchesPlayed:(match.matchPlayed)?1:0,
          goalsFor:match.homeTeamScore,
          goalsAgainst:match.awayTeamScore,
          points:pointsHomeScored
        };
      }else{
        processMatches[match.homeTeam].matchesPlayed+=(match.matchPlayed)?1:0;
        processMatches[match.homeTeam].goalsFor+=match.homeTeamScore;
        processMatches[match.homeTeam].goalsAgainst+=match.awayTeamScore;
        processMatches[match.homeTeam].points+=pointsHomeScored;
      }

      //modificar puntos del awayTeam
      if(!processMatches[match.awayTeam]){
        processMatches[match.awayTeam]={
          teamName:match.awayTeam,
          matchesPlayed:(match.matchPlayed)?1:0,
          goalsFor:match.awayTeamScore,
          goalsAgainst:match.homeTeamScore,
          points:pointsAwayScored
        };
      }else{
        processMatches[match.awayTeam].matchesPlayed+=(match.matchPlayed)?1:0;
        processMatches[match.awayTeam].goalsFor+=match.awayTeamScore;
        processMatches[match.awayTeam].goalsAgainst+=match.homeTeamScore;
        processMatches[match.awayTeam].points+=pointsAwayScored;
      }

    });

    //quedarse solo con las valores sin las claves
    let flatProcesMatches=[];
    for (let key in processMatches){
      flatProcesMatches.push(processMatches[key])
    }
    
    return flatProcesMatches;
   }

  /**
   * Asynchronic function to fetch the data from the server and set the matches.
   */
  async fetchData () {
    try{
      //obtener el codigo de acceso
      let response=await fetch(this.urlAPI+"getAccessToken");
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }
  
      const json = await response.json();
      if (!json.access_token) {
        throw new Error(`Response status: ${response.status}`);
      }

      //traer los partidos
      response=await fetch(this.urlAPI+"getAllMatches",{
        headers:{
          "Authorization": "Bearer "+json.access_token,
        }
      });

      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const matchesBD = await response.json();
      if (!json.access_token) {
        throw new Error(`Response status: ${response.status}`);
      }

      //guardarlos en "matches"
      const matches = matchesBD.matches;
      this.setMatches(matches); 

      //procesar los resultados
      this.leaderBoard=this.generateLeaderBoard(this.matches);

      //ordenar los resultados
      this.leaderBoard=this.order(this.leaderBoard);
      

    }catch(error){
      console.error(error.message);
    }
    
  }

  /* ordering */
  orderSimple(teamResults:Array<TeamResults>){
    teamResults.sort((a,b)=>a.points-b.points);
    return teamResults;
  }

  groupByPoints(teamResults:Array<TeamResults>){
    return teamResults.reduce((acum,team)=>{
      acum[team.points]=acum[team.points]||[];
      acum[team.points].push(team);
      return acum;
    },{});
  }

  order(teamResults:Array<TeamResults>){
    
    //agrupar por puntos
    let teamResultsByPoints=this.groupByPoints(teamResults);
    

    //convertir en array
    let resultsArray=[];
    for (let key in teamResultsByPoints){
      resultsArray.push(teamResultsByPoints[key])
    }

    teamResultsByPoints=resultsArray.map((group:Array<TeamResults>)=>{
      if(group.length==1){
        return group;
      }

      //ordenar los empatados en cada grupo
      //criterio 1

      //obtener los partidos solo de los equipos empatados
      let filterMatches=this.matches.filter(match=>{
        return (group.findIndex(team=>team.teamName==match.awayTeam)!=-1 &&
        group.findIndex(team=>team.teamName==match.homeTeam)!=-1)
      });

      let newOrder=this.generateLeaderBoard(filterMatches);
      this.orderSimple(newOrder);
      

      
      //revisar que no siga habiendo empates
      let empates=[];
      let hayEmpate=0;
      newOrder.forEach((team:TeamResults)=>{
        if(!empates[team.points]){
          empates[team.points]=1;
        }else{
          hayEmpate=1;
        }
      })

      
      //si no hay empate se aplica el criterio 1
      if(!hayEmpate){
        //reordenamos group
        group = newOrder.map(orden => {
          return group.find(n => n.teamName === orden.teamName)
        })

        return group;
      }

      //los demas criterios:
      /* The second tiebreaker is goal difference.
          The third tiebreaker is the number of scored goals.
          The final tiebreaker is alphabetic ascending order by name.
      */
      group.sort((a,b)=>{
        let criterio2=(a.goalsFor-a.goalsFor)-(b.goalsFor-b.goalsFor);
        if(criterio2!=0){
          return criterio2;
        }

        let criterio3=a.goalsFor-b.goalsFor;
        if(criterio3!=0){
          return criterio3;
        }
        
        //en su defecto ordenar alfabeticamente
        return a.teamName.localeCompare(b.teamName);
      })
      return group;
    });

    
    //volver a desagrupar
    let acum=[];
    for (let key in teamResultsByPoints){
      for(let index in teamResultsByPoints[key]){
        acum.push(teamResultsByPoints[key][index])
      }
    }

    //invertir el orden
    return acum.reverse();
  }
}
