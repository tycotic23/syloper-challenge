/* 

        {
*          matchDate: [TIMESTAMP],
*          stadium: [STRING],
*          homeTeam: [STRING],
*          awayTeam: [STRING],
*          matchPlayed: [BOOLEAN],
*          homeTeamScore: [INTEGER],
*          awayTeamScore: [INTEGER]
*       }

*/


export interface Match{
    matchDate: Date;
    stadium:string;
    homeTeam:string;
    awayTeam:string;
    matchPlayed:boolean;
    homeTeamScore:number;
    awayTeamScore:number;
}