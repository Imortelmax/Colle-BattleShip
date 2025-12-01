<?php 

function isValidCoord($x, $y, $maxX, $maxY){
    return is_int($x) && is_int($y) && $x >= 0 && $y >= 0 && $x < $maxX && $y < $maxY;
}

function colle($x, $y, array $coords = []){
    if ($x == 0 && $y == 0){
        return;
    } else if ($x < 0 || $y < 0){
        echo 'Coordonnées invalides.' . PHP_EOL;
        return;
    } 
    
    $grid = [];

    $totalRows = $y * 2 + 1;
    $totalCols = $x * 4 + 1;

    for ($i = 0; $i < $totalRows; $i++){
        $grid[$i] = array_fill(0, $totalCols, ' ');
    }

    for ($row = 0; $row <= $y; $row++){
        $gridLine = $row * 2;

        for($col = 0; $col <= $x; $col++){
            $charIndex = $col * 4;
            $grid[$gridLine][$charIndex] = '+';

            if($col < $x) {
                $grid[$gridLine][$charIndex + 1] = '-';
                $grid[$gridLine][$charIndex + 2] = '-';
                $grid[$gridLine][$charIndex + 3] = '-';
            }
        }
    }

    for ($row = 0; $row < $y; $row++) {
        $gridLine = $row * 2 + 1;

        for($col = 0; $col <= $x; $col++){
            $charIndex = $col * 4;
            $grid[$gridLine][$charIndex] = '|';
        }

    }

    foreach ($coords as $coord){
        if(count($coord) === 2){
            [$cx, $cy] = $coord;
            if ($cx < 0 || $cy < 0) {
                echo 'Coordonnées invalides: '. "[$cx, $cy]" . PHP_EOL;                
            }
            if ($cx >= 0 && $cx < $x && $cy >= 0 && $cy < $y){
                $lineIndex = $cy * 2 + 1;
                $charIndex = $cx * 4 + 2;
                $grid[$lineIndex][$charIndex] = 'X';
            }
        }
    }

    foreach ($grid as $line){
        echo implode('', $line) . PHP_EOL;
    }
}

class Player {
    public string $name;
    private array $ships = [];
    private array $hits = [];

        public function __construct($name) {
            $this->name = $name;
        }

        public function addShip($x, $y){
            foreach($this->ships as $s){
                if ($s === [$x, $y]){
                    return 'A cross already exists at this location' . PHP_EOL;
                }
            }
            $this->ships[] = [$x, $y];
            return "";
        }
        
        public function hasShipAt($x, $y){
            return in_array([$x, $y], $this->ships);
        }

        public function isHitAt($x, $y){
            return in_array([$x, $y], $this->hits);
        }
        
        public function registerHit($x, $y){
            if(!$this->isHitAt($x, $y)) {
                $this->hits[] = [$x, $y];
            }
        }

        public function hasLost(){
            foreach($this->ships as $s){
                if(!in_array($s, $this->hits)) {
                    return false;
                }
            }
            return true;
        }

        public function getAllCoords(){
            $coords = [];
            foreach($this->ships as $s){
                $coords[] = $s;
            }
            foreach($this->hits as $h){
                if(!in_array($h, $coords)){
                    $coords[] = $h;
                }
            }
            return $coords;
        }
};

function prompt($msg){
    echo $msg;
    return trim(fgets(STDIN));
}

function parseCoord($input){
    if(preg_match('/\[(\d+),\s*(\d+)\]/', $input, $matches)){
        return [(int)$matches[1], (int)$matches[2]];
    }
    return null;
}

function switchPlayer($p1, $p2, $current){
    return $current === $p1 ? $p2 : $p1;
}

$shipsPerPlayer = 2;
$x = 4;
$y = 4;

colle($x, $y);

$p1 = new Player('Player 1');
$p2 = new Player('Player 2');
$current = $p1;
$opponent = $p2;

foreach([$p1, $p2] as $player){
    echo "{$player->name}, place your {$shipsPerPlayer} ships :" . PHP_EOL;
    $count = 0;
    while($count < $shipsPerPlayer){
        $input = prompt("{$player->name} \$> ");
        $coord = parseCoord($input);
        
        if($input == "display") {
            colle($x, $y, $player->getAllCoords());
            continue;
        }

        if($input == 'exit'){
            return;
        }

        if($coord && $coord[0] < $x && $coord[1] < $y){
            $msg = $player->addShip($coord[0], $coord[1]);
            if($msg === "") {
                $count++; 
            } else {
                echo $msg;
            }
        } else {
            echo 'Invalid coordinates.' . PHP_EOL;
        }
    }
}

while(true){
    echo "{$current->name}, launch your attack : " . PHP_EOL;
    $input = prompt("{$current->name} $> ");
    $coord = parseCoord($input);

    if($input == "display") {
        colle($x, $y, $current->getAllCoords());
        continue;
    }

    if($input == 'exit'){
        return;
    }

    if (!$coord || $coord[0] >= $x || $coord[1] >= $y){
        echo 'Invalid coordinates.' . PHP_EOL;
        continue;
    }

    if ($opponent->isHitAt($coord[0], $coord[1])){
        echo 'This location was already attacked. Choose another target.' . PHP_EOL;
        continue;
    }

    if ($opponent->hasShipAt($coord[0], $coord[1])){
        echo "{$current->name}, you touched a boat of {$opponent->name} !" . PHP_EOL;
        $opponent->registerHit($coord[0], $coord[1]);
    } else {
        echo "{$current->name} you didn't touch anything." . PHP_EOL;
        $opponent->registerHit($coord[0], $coord[1]);
    }

    if ($opponent->hasLost()){
        echo "{$current->name} win !!" . PHP_EOL;
        break;
    }

    [$current, $opponent] = [$opponent, $current];
}

?>