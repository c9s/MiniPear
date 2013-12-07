<?php
/*
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* php 5.3.3 does not support this */
if( ! defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') )
    define( 'DEBUG_BACKTRACE_PROVIDE_OBJECT' , true );


function get_testcase_object() 
{
    $objs = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
    foreach($objs as $o) {
        if ( isset($o['object']) && $o['object'] instanceof PHPUnit_Framework_TestCase ) {
            return $o['object'];
        }
    }
    return NULL;
}

function ok( $v , $msg = null )
{
    $test = get_testcase_object();
    $test->assertTrue( $v ? true : false , $msg );
    return $v ? true : false;
}

function not_ok( $v , $msg = null )
{
    $test = get_testcase_object();
    $test->assertFalse( $v ? true : false , $msg );
    return $v ? true : false;
}

function is( $expected , $v , $msg = null )
{
    $test = get_testcase_object();
    $test->assertEquals( $expected , $v , $msg );
    return $expected === $v ? true : false;
}


function isa_ok( $expected , $v , $msg = null )
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertInstanceOf( $expected , $v , $msg );
}

function is_class( $expected , $v , $msg = null )
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertInstanceOf( $expected , $v , $msg );
}

function count_ok( $expected, $v, $msg = null ) 
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertCount( $expected , $v , $msg );
}

function select_ok( $selected , $expected , $xml )
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $dom = null;
    if( is_string($xml) ) {
        $dom = new DOMDocument;
        $dom->loadXML($xml);
    } else {
        $dom = $xml;
    }
    $testobj = $stacks[1]['object'];
    $testobj->assertSelectCount( $selected , $expected , $dom );
}

function skip( $msg )
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->markTestSkipped( $msg );
}


function like( $e, $v , $msg = null )
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertRegExp($e,$v,$msg);
}

function is_true($e,$v,$msg = null)
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertTrue($e,$v,$msg);
}

function is_false($e,$v,$msg= null)
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT ); 
    $testobj = $stacks[1]['object'];
    $testobj->assertFalse($e,$v,$msg);
}

function file_equals($e,$v,$msg = null)
{
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
    $testobj = $stacks[1]['object'];
    $testobj->assertFileEquals($e,$v,$msg);
}

function file_ok( $path , $msg = null ) {
    $stacks = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
    $testobj = $stacks[1]['object'];
    $testobj->assertFileExists($path , $msg );
    $testobj->assertTrue( is_file( $path ) , $msg );
}

function class_ok( $val , $msg = null ) 
{
    $test = get_testcase_object();
    $test->assertTrue( class_exists( $val ) , $msg ? $msg : "Class $val exists");
}

function path_ok( $path , $msg = null ) 
{
    $test = get_testcase_object();
    $test->assertFileExists($path , $msg );
}

function dir_ok($path, $msg = null)
{
    $test = get_testcase_object();
    $test->assertFileExists($path , $msg , "Directory $path exists." );
    $test->assertTrue( is_dir($path) , "Path $path is a directory." );
}

function same_ok($e,$v) 
{
    $test = get_testcase_object();
    $test->assertSame($e,$v);
}

function null_ok($e)
{
    $test = get_testcase_object();
    $test->assertNull($e);
}


/**
 * Assert object has an attribute
 *
 */
function object_attribute_ok($o,$attributeName)
{
    $test = get_testcase_object();
    $test->assertNotNull($o, "object " . get_class($o) . " is not empty");
    $test->assertObjectHasAttribute($attributeName, $o);
}

function is_empty($e,$msg = null)
{
    $test = get_testcase_object();
    $test->assertEmpty($e,$msg);
}

function array_key_ok($array,$key,$msg = null)
{
    $test = get_testcase_object();
    $test->assertArrayHasKey($key,$array, $msg);
}


/**
 * Assert html tags
 */
function tag_ok($matcher,$actual, $message = '',$isHtml = true)
{
    $test = get_testcase_object();
    $test->assertTag($matcher,$actual, $message, $isHtml);
}


function dump($e)
{
    var_dump($e);
    ob_flush();
}



