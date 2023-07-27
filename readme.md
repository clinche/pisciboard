# Exam Racing
"No need to say more" 

5 tokens <=> 1200*5 requests/hour <=> 6000req/h <=> 75 refreshes of 80 users every hour

## Working
- token creation
- token refresh
- rate limit detector
- token switching

## ToDo

 - prettier front-end 
 - do a "player weight" to refresh higher players more often 
 - stock the json in SESSION and modify players with higher weight 
 - do a load balancer to launch more than 2 requests/sec thanks to parallelism (maybe 1 thread per weight?)
