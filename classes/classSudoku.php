<?php
class square
{
    private $_row;
    private $_col;
    private $_quad;
    private $_val;
    private $_not;

    public function __construct($col,$row)
    {
        $this->_col = $col;
        $this->_row = $row;
        $this->_quad = floor($col/3) + (floor($row / 3) * 3);
        $this->_val = null;
        $this->_not = 0;
    }

    private function pad($v,$n)
    {
        while (strlen($v) < $n)
            $v = "0" . $v;
        return $v;
    }

    private function nSet($v)
    {
        $c = 0;
        for($i = 0;$i < 9;$i++)
            $c += intval(($v >> $i) & 1);
        return $c;
    }

    public function reset()
    {
        $this->_val = null;
        $this->_not = 0;
    }

    public function setNot($v)
    {
        $this->_not = $this->_not | (1 << ($v - 1));
    }

    public function __get($n)
    {
        if ($n == "v")
            return $this->_val;
        if ($n == "n")
            return $this->_not;
        if ($n == "c")
            return $this->_col;
        if ($n == "r")
            return $this->_row;
    }

    public function __set($n,$v)
    {
        if ($n == "v")
        {
            $this->_val = $v;
            $this->_not = 511;
        }
        if ($n == "n")
            $this->_not = $v;
    }

    public function cellIdx()
    {
        return ($this->_row * 9) + $this->_col;
    }

    public static function fromCellIdx($idx)
    {
        $row = floor($idx / 9);
        $col = floor($idx % 9);
        return [$col,$row];
    }

    public static function mirror($idx)
    {
        $v  = 80 - $idx;
        return self::fromCellIdx($v);
    }

    public function listCouldBe()
    {
        $l = array();
        for($i = 0;$i < 9;$i++)
        {
            if ( (($this->_not >> $i) & 1) == 0)
                $l[] = $i+1;
        }
        return $l;
    }

    public function couldBe($v)
    {
        $i = $v -1;
        if ( (($this->_not >> $i) & 1) == 0)
            return true;
        return false;
    }

    public function nPossible()
    {
        return 9 - $this->nSet($this->_not);
    }

    public function solveit()
    {
        if ($this->nPossible() == 1)
        {
            for($i=0;$i<9;$i++)
            {
                if ( (($this->_not >> $i) & 1) == 0 )
                {
                    $this->v = $i+1;
                    break;
                }
            }
        }
    }

    public function getQuad()
    {
        return $this->_quad;
    }

    public function toString()
    {
        $ret = "";
        $ret = "[{$this->_col},{$this->_row}] [{$this->_quad}] ";
        if ($this->_val)
            $ret .= $this->_val;
        else
            $ret .= " ";
        $ret .= " ";
        $ret .= $this->pad(base_convert($this->_not,10,2),9);
        return $ret;
    }
}

class group
{
    private $_group;

    public function __construct()
    {
        $this->_group = array();
    }

    public function add($s)
    {
        $this->_group[] = $s;
    }

    public function valid()
    {
        $numbers = array();
        foreach($this->_group as $s)
        {
            $n = $s->v;
            if ($n)
            {
                if (isset($numbers[$n]))
                    return false;
                $numbers[$n] = 1;
            }
        }
        return true;
    }

    public function updateNots()
    {
        $n = 0;

        foreach($this->_group as $s)
        {
            if ($s->v)
                $n = $n  | (1 << ($s->v-1));
        }


        foreach($this->_group as $s)
        {
            if ($s->v)
            {
                $s->n = 511;
            }
            else
            {
                $s->n = $s->n | $n;
            }


            if ($s->n == 511 & ! $s->v)
            {
                throw(new Exception("All nots set but no value for {$s->toString()}"));
            }
        }

    }

    public function nUnsolved()
    {
        $n = 9;
        foreach($this->_group as $s)
        {
            if ($s->v)
                $n--;
        }
        return $n;
    }

    public function onePlaceOnly()
    {
        //Looking for ony one zero in the vertical of all nots
        for($i=0;$i<9;$i++)
        {
            $cnt = 0;
            $shit = null;
            foreach($this->_group as $s)
            {
                 if ((($s->n >> $i) & 1) == 0 )
                 {
                     $cnt++;
                     $shit = $s;
                 }
            }

            if ($cnt == 1)
            {
                $shit->v = $i + 1;
                return true;
            }
        }
    }

    public function twoPlaceOnly()
    {
        //Looking for two zeros in the vertical of all nots
        $list1 = array();
        for($i=0;$i<9;$i++)
        {
            $cnt = 0;
            $shits = array();
            foreach($this->_group as $s)
            {
                 if ((($s->n >> $i) & 1) == 0 )
                 {
                     $cnt++;
                     $shits[] = $s;
                 }
            }

            if ($cnt == 2)
            {
                $list1[] = [$i,$shits[0],$shits[1]];
            }
        }

        //Are there any two $lists1 that are the same
        if (count($list1) >= 2)
        {
            for ($j = 0; $j < count($list1); $j++)
            {
                for ($k = $j+1; $k < count($list1); $k++)
                {
                    if ( ($list1[$j] [1])->cellIdx()  == ($list1[$k] [1])->cellIdx()  &&  ($list1[$j] [2])->cellIdx()  == ($list1[$k] [2])->cellIdx() )
                    {
                        //we have two colums that are the same fro numbers
                        $n1 = $list1[$j] [0];
                        $n2 = $list1[$k] [0];
                        $n = 511 & ~(1 << $n1);
                        $n = $n & ~(1 << $n2);
                        ($list1[$j] [1])->n = $n;
                        ($list1[$j] [2])->n = $n;

                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function oddNot()
    {
        $nots = array();
        $odds = array();
        $identicle = array();
        $strike = false;

        foreach($this->_group as $s)
        {
            if (! $s->v )
            {
                $nots[] = $s;
                $odds[] = $s;
            }
        }

        for ($a = 0; $a < (count($nots)-1); $a++)
        {
            for ($b = $a+1 ; $b < count($nots); $b++)
            {
                if ($nots[$a]->n == $nots[$b]->n)
                {
                    if (! isset($identicle[$a]))
                        $identicle[$a] = array();
                    $identicle[$a] [] = $b;
                    if (! isset($identicle[$b]))
                        $identicle[$b] = array();
                    $identicle[$b] [] = $a;
                }
            }
        }

        if (count($identicle) == 0)
            return [];

        //Any identical nots that have same possible bit counts as count of identicles
        foreach ($identicle as $idx => $i)
        {
            //$i is an array of other identicals.
            if (count($i) == $nots[$idx]->nPossible() -1 )
            {
                //we remove them from the odds
                unset($odds[$idx]);
                foreach($i as $j)
                    unset($odds[$j]);

                foreach($odds as $o)
                {
                    foreach($nots[$idx]->listCouldBe() as $v)
                        $o->setNot($v);
                }

                return false;
            }
        }

        return false;
    }

    public function toString()
    {
        $str = "";
        foreach($this->_group as $s)
        {
            $str .= $s->toString() . "\n";
        }
        return $str;
    }
}

class puzzle
{
    private $_all;
    private $_rows;
    private $_cols;
    private $_quads;
    private $_stack;

    public function __construct()
    {
        $this->purge();
    }

    public function purge()
    {
        $this->_all = array();
        for($col = 0;$col < 9;$col++)
            $this->_all[$col] = array();
        $this->_cols = array();
        $this->_rows = array();
        $this->_quads = array();
        $this->_stack = array();

        //Create groups
        for ($col = 0; $col < 9;$col++)
            $this->_cols[$col] = new group();
        for ($row = 0; $row < 9;$row++)
            $this->_rows[$row] = new group();
        for ($quad = 0; $quad < 9;$quad++)
            $this->_quads[$quad] = new group();

        for ($col = 0; $col < 9;$col++)
        {
            for ($row = 0; $row < 9;$row++)
            {
                $s = new square($col,$row);
                $this->_all[$col] [$row] = $s;
                $this->_cols[$col]->add($s);
                $this->_rows[$row]->add($s);
                $this->_quads[$s->getQuad()]->add($s);
            }
        }
    }

    public function push()
    {
        $this->_stack[] = $this->export();
    }

    public function pop()
    {
        if (count($this->_stack) > 0)
        {
            $this->load(array_pop($this->_stack));
        }
    }

    public function stack_count()
    {
        return count($this->_stack);
    }

    public function setSquare($col,$row,$v)
    {
        ($this->_all[$col] [$row])->v = $v;
    }

    public function load($l)
    {
        if (gettype($l) == "array")
        {
            $i = 0;
            for($col=0; $col < 9; $col++)
            {
                for($row=0;$row < 9;$row++)
                {
                    ($this->_all[$col] [$row])->v = $l[$i];
                    ($this->_all[$col] [$row])->n = 0;
                    $i++;
                }
            }
        }

        if (gettype($l) == "string")
        {
            if (strlen($l) != 81)
                throw (new Exception("Invalid string length for load of string"));

            $i = 0;
            for($row=0; $row < 9; $row++)
            {
                for($col=0;$col < 9;$col++)
                {
                    if (substr($l,$i,1) != " ")
                    {
                        ($this->_all[$col] [$row])->v = intval(substr($l,$i,1));
                        ($this->_all[$col] [$row])->n = 0;
                    }
                    else
                    {
                        ($this->_all[$col] [$row])->v = null;
                        ($this->_all[$col] [$row])->n = 0;
                    }
                    $i++;
                }
            }

        }

        $this->updateNots();

    }

    public function export()
    {
        $str = "";

        for ($row = 0; $row < 9; $row++)
        {
            for ($col = 0; $col < 9; $col++)
            {
                $v = ($this->_all[$col] [$row])->v;
                if ($v)
                    $str .= ($this->_all[$col] [$row])->v;
                else
                    $str .= " ";
            }
        }
        return $str;
    }

    public function count()
    {
        $cnt = 0;
        for ($row = 0; $row < 9; $row++)
        {
            for ($col = 0; $col < 9; $col++)
            {
                if (($this->_all[$col] [$row])->v )
                    $cnt++;
            }
        }
        return $cnt;
    }

    public function updateNots()
    {
        foreach($this->_cols as $g)
            $g->updateNots();
        foreach($this->_rows as $g)
            $g->updateNots();
        foreach($this->_quads as $g)
            $g->updateNots();

        $this->allOddsForNum(3);
        $this->allOddsForNum(4);
        $this->allOddsForNum(5);
        $this->allOddsForNum(6);

        $this->alltwoPlaceOnly();
    }

    private function oddsForGroup($group,$n)
    {
        $rslt = false;

        foreach($group as $g)
        {
            if ($n == $g->nUnsolved())
            {
                if ($g->oddNot() )
                    $rslt = true;
             }
        }
        return $rslt;
    }

    private function allOddsForNum($n)
    {
        if ($this->oddsForGroup($this->_cols,$n) )
            return true;
        if ($this->oddsForGroup($this->_rows,$n) )
            return true;
        if ($this->oddsForGroup($this->_quads,$n) )
            return true;
        return false;
    }

    private function onePlaceOnlyForGroup($group)
    {
        foreach($group as $g)
        {
            if ($g->onePlaceOnly())
                return true;
        }
        return false;
    }

    private function allonePlaceOnly()
    {
        if ($this->onePlaceOnlyForGroup($this->_cols) )
            return true;
        if ($this->onePlaceOnlyForGroup($this->_rows) )
            return true;
        if ($this->onePlaceOnlyForGroup($this->_quads) )
            return true;
        return false;
    }

    private function twoPlaceOnlyForGroup($group)
    {
        foreach($group as $g)
        {
            if ($g->twoPlaceOnly())
                return true;
        }
        return false;
    }

    private function alltwoPlaceOnly()
    {
        if ($this->twoPlaceOnlyForGroup($this->_cols) )
            return true;
        if ($this->twoPlaceOnlyForGroup($this->_rows) )
            return true;
        if ($this->twoPlaceOnlyForGroup($this->_quads) )
            return true;
        return false;
    }

    public function valid()
    {
        //Checks that each group doesnt have two numbers the same
        foreach($this->_cols as $g)
        {
            if (!$g->valid())
                return false;
        }
        foreach($this->_rows as $g)
        {
            if (!$g->valid())
                return false;
        }
        foreach($this->_quads as $g)
        {
            if (!$g->valid())
                return false;
        }
        return true;
    }

    public function solve_one()
    {
        $rslt = false;

        for ($row = 0; $row < 9; $row++)
        {
            for ($col = 0; $col < 9; $col++)
            {
                if ( ($this->_all[$col] [$row])->nPossible() == 1)
                {
                    ($this->_all[$col] [$row])->solveit();
                    $this->updateNots();
                    return true;
                }
            }
        }

        $rslt = $this->allonePlaceOnly();
        if ($rslt)
        {
            $this->updateNots();
            return true;
        }


        return false;

    }

    public function solve()
    {
        $cnt = 0;
        $rslt = $this->solve_one();
        while ($rslt && $cnt < 200)
        {
            $rslt = $this->solve_one();
            $cnt++;
        }
    }

    public function isSolved()
    {
        if ($this->valid() && strpos($this->export()," ") === false )
            return true;
    }

    private function addRandomPair()
    {
        $done = false;
        while (!$done)
        {
            $cells = array();
            $idx = rand(0,80);
            $cIdx = square::fromCellIdx($idx);
            $cells[0] = $this->_all[$cIdx[0]] [$cIdx[1]];
            if ($idx != 40)
            {
                $cIdx = square::mirror($idx);
                $cells[1] = $this->_all[$cIdx[0]] [$cIdx[1]];
            }

            $nCells = count($cells);
            $old = array();
            $i = 0;
            $cellSet = false;

            foreach ($cells as $cell)
            {

                if ( ! $cell->v )
                {
                    //Check what valid numbers this cell can have

                    //echo "Found empty cell [{$cell->c},{$cell->r}]\n";

                    $cb = $cell->listCouldBe();

                    //echo " couldbes are: ";
                    //foreach($cb as $z)
                        //echo "{$z},";
                    //echo "\n";

                    $cbidx = rand(0,count($cb)-1);

                    $old[$i] = $cell->n;
                    $cell->v = $cb[$cbidx];
                    $i++;
                    $cellSet = true;

                    //echo " set cell to {$cell->v}\n";

                    if ($i >= $nCells)
                        $done = true;
                }

            }

            if (!$done && $cellSet)
            {
                //We need to reverse out
                //echo " reverse out as no suitable numbers\n";
                $i = 0;
                foreach ($cells as $cell)
                {
                    $cell->v = null;
                    $cell->n = $old[$i];
                    $i++;
                }

            }

        }

    }

    public function createRandom()
    {
        $solved = false;
        $this->purge();

        while (!$solved)
        {
            $this->addRandomPair();

            $this->push();
            try {
                $this->updateNots();
                $this->solve();
            }
            catch (Exception $e){
                break;
            }
            $solved = $this->isSolved();
            $this->pop();

            if ($this->count() > 50)
                break;
        }
        return $solved;
    }


    public function grid()
    {
        $str = "";

        for ($row = 0; $row < 9; $row++)
        {
            for ($col = 0; $col < 9; $col++)
            {
                $v = ($this->_all[$col] [$row])->v;
                if ($v)
                    $str .= ($this->_all[$col] [$row])->v;
                else
                    $str .= "-";
            }
            $str .= "\n";
        }

        return $str;

    }

    public function dump()
    {
        $str = $this->grid();

        $str .= "Dump of columns\n";
        foreach($this->_cols as $idx => $g)
        {
            $str .= "Column {$idx}\n";
            $str .= $g->toString();
        }

        $str .= "Dump of rows\n";
        foreach($this->_rows as $idx => $g)
        {
            $str .= "Row {$idx}\n";
            $str .= $g->toString();
        }

        $str .= "Dump of quads\n";
        foreach($this->_quads as $idx => $g)
        {
            $str .= "Quad {$idx}\n";
            $str .= $g->toString();
        }

        return $str;
    }

}
?>