<?php 

function findCount($d) 
{ 
    return 9 * (pow(10, $d - 1) - pow(9, $d - 1)); 
} 
  

$d = 1; 
echo findCount($d),"\n"; 

$d = 2; 
echo findCount($d),"\n"; 

$d = 4; 
echo findCount($d), "\n"; 
  
?> 