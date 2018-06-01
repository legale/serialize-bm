<?php
$ver = '0.1';

$iterations = 1000;

$sizes = [10, 100, 1000, 5000];

$party = array(
    'serialize' => array('serialize', 'unserialize'),
    'json_encode' => array('json_encode', 'json_decode'),
    'var_export' => array('var_export', 'eval'),
    'msgpack_pack' => array('msgpack_pack', 'msgpack_unpack'),
);

$res = array_map('array_flip', $party);

// -- welcomme
print "Script version " . $ver . "\n";
print "PHP Version " . phpversion() . "\n\n";


// -- benchmark cycle
foreach ($sizes as $size) {
    $data = array_fill(0, $size, 'Это тестовые данные для проверки. This is test data.');
    print str_repeat('-', 50) . "\n";

    print "Testing " . number_format($size) . " item array over " . number_format($iterations) . " iterations:\n\n";

    //serialize
    $pair = 'serialize';
    $array = $party[$pair];
    $func0 = $array[0];
    $func1 = $array[1];
    $dur0 = bmark($iterations, $func0, $data);
    $dur1 = bmark($iterations, $func1, serialize($data));
    $res[$pair][$func0] = ($dur0 === false) ? false : $res[$pair][$func0] + $dur0;
    $res[$pair][$func1] = ($dur1 === false) ? false : $res[$pair][$func1] + $dur1;

    //json_encode
    $pair = 'json_encode';
    $array = $party[$pair];
    $func0 = $array[0];
    $func1 = $array[1];
    $dur0 = bmark($iterations, $func0, $data);
    $dur1 = bmark($iterations, $func1, json_encode($data), true); //json_decode($data, true)
    $res[$pair][$func0] = ($dur0 === false) ? false : $res[$pair][$func0] + $dur0;
    $res[$pair][$func1] = ($dur1 === false) ? false : $res[$pair][$func1] + $dur1;

    //var_export
    $pair = 'var_export';
    $array = $party[$pair];
    $func0 = $array[0];
    $func1 = $array[1];
    $dur0 = bmark($iterations, $func0, $data, true);
    $dur1 = bmark($iterations, $func1, '$var = ' . var_export($data, true) . ';'); //eval('$var = ...;')
    $res[$pair][$func0] = ($dur0 === false) ? false : $res[$pair][$func0] + $dur0;
    $res[$pair][$func1] = ($dur1 === false) ? false : $res[$pair][$func1] + $dur1;

    //msgpack_pack
    $pair = 'msgpack_pack';
    $array = $party[$pair];
    $func0 = $array[0];
    $func1 = $array[1];
    $dur0 = bmark($iterations, $func0, $data);
    $dur1 = bmark($iterations, $func1, msgpack_pack($data));
    $res[$pair][$func0] = ($dur0 === false) ? false : $res[$pair][$func0] + $dur0;
    $res[$pair][$func1] = ($dur1 === false) ? false : $res[$pair][$func1] + $dur1;


}
// -- results
print "\n" . str_repeat('-', 75) . "\n";
print str_pad('', 35);
print str_pad('average (ms)', 20);
print str_pad('total (ms)', 20) . "\n";
print str_repeat('-', 75) . "\n";

foreach ($party as $pair => $array) {
    foreach ($res[$pair] as $name => $check) {
        if ($check === false) {
            print str_pad(implode('/', $array), 35);
            print str_pad("benchmark failed", 20);
            print str_pad("benchmark failed", 20) . "\n";
            continue(2);//skip two parent cycles
        }
    }

    $total = array_sum($res[$pair]) * 1000;
    print str_pad(implode('/', $array), 35);
    print str_pad($total / count($sizes), 20);
    print str_pad($total, 20) . "\n";
}

print "\n";




function bmark()
{
    $args = func_get_args();
    $len = count($args);

    if ($len < 3) {
        trigger_error("At least 3 args expected. Only $len given.", 256);
        return false;
    }

    $cnt = array_shift($args);
    $fun = array_shift($args);

    $start = microtime(true);
    $i = 0;
    $args = array_map(function($e){return var_export($e, true);},$args);
    $str = "$fun(" . implode(', ', $args) . ");";
    while ($i < $cnt) {
        $i++;
        $res = eval($str);
    }
    $end = microtime(true) - $start;
    return $end;
}

