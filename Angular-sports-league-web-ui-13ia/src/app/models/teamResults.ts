/*  {
*          teamName: [STRING]',
*          matchesPlayed: [INTEGER],
*          goalsFor: [INTEGER],
*          goalsAgainst: [INTEGER],
*          points: [INTEGER]
*      }
* 
* 
* */


export interface TeamResults{
    teamName:string;
    matchesPlayed:number;
    goalsFor:number;
    goalsAgainst:number;
    points:number;
}