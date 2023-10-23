<?php
require "./includes/classCollection.php";

class trip
{
    function __construct()
    {
        echo "Trip Constructed";
    }

	public function what($name)
    {
        echo $this->{$name} . "\n";
    }
}


$testdata = [
	["a" => 1, "b" => "zero", "c" => null],
	["a" => 2, "b" => "one", "c" => null],
	["a" => 3, "b" => "two", "c" => null],
];

$col = new CCollection($testdata);

//Test first
$rec = $col->first();
echo "Testing first - record follows\n";
var_dump($rec);

//Testing the rest
echo "Testing remainder - records follows\n";
while($rec = $col->next())
{
	var_dump($rec);
}

//Testingt reset
echo "Testing full list after reset starting with next - records follow\n";
$col->reset();
while ($rec = $col->next())
{
	var_dump($rec);
}

echo "Testing full list after reset and sort ascending starting with next - records follow\n";
$col->sort("b", "asc");
$col->reset();
while ($rec = $col->next()) {
    var_dump($rec);
}

echo "Testing full list after reset and sort descending starting with next - records follow\n";
$col->sort("b", "desc");
$col->reset();
while ($rec = $col->next())
{
	var_dump($rec);
}

echo "Testing object get - record follow\n";
$o = $col->oget(0);
var_dump($o);

echo "Testing getting object variable columen b\n";
echo "Object column b = {$o->b}\n";

echo "Testing updating a non reference get\n";
$rec= $col->get(0);
$rec["b"] = "fred";
var_dump($rec);
$rec = $col->get(0);
var_dump($rec);

echo "Testing updating a referenced get\n";
$rec = &$col->get(0);
$rec["b"] = "fred";
var_dump($rec);
$rec = $col->get(0);
var_dump($rec);


echo "Testing catsing an object to a row\n";
$o = $col->oget(0,"trip");
echo "Dumping trip object\n";
var_dump($o);

echo "Trying what\n";
echo $o->what("b") . "\n";

echo "List all again\n";
$col->reset();
while ($rec = $col->next()) {
    var_dump($rec);
}

echo "Trying find of a = 2\n";
$rec = $col->find("a",2);
var_dump($o);

echo "Test current\n";
echo $col->current();
echo "\n";
?>