<?php

namespace Jet\Router\Test;

use Jet\Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public static $fixturesDir;

    public static function setUpBeforeClass()
    {
        self::$fixturesDir = __DIR__."/../Fixtures/";
        require_once(self::$fixturesDir.'TestFixtures.php');
    }

    public function testConstructor()
    {
        $test = array('test' => 'test');
        $router = new Router($test);

        $this->assertEquals($test, $router->routes, "Constructor don't add routes");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorException()
    {
        $router = new Router('string');
    }

    public function testAddRoutes()
    {
        $router = new Router();
        $test = array('test' => 'test');
        $router->addRoutes($test);

        $this->assertEquals($test, $router->routes, "addRoutes don't add routes");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRoutesException()
    {
        $router = new Router();
        $router->addRoutes('string');
    }

    public function testLauchReturnValue()
    {
        $router = new Router();
        $router->addRoutes(array('/' => function(){
            return true;
        }));

        $this->assertTrue($router->launch(), "Router can't launch default URI");
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLaunchException()
    {
        $router = new Router();
        $router->launch();
        $router->launch('/test');
    }

    public function testDefault()
    {
        $router = new Router(array(
            '/' => function(){
                return 'default';
            }
        ));

        $this->assertEquals('default', $router->launch(), 'Default route not working');
    }

    public function testError()
    {
        $router = new Router(array(
            'error' => function(){
                return 'error';
            }
        ));

        $this->assertEquals('error', $router->launch(), 'Default route not working');
    }

    public function testPattern()
    {
        $urlContent = array(
            'any'   => 'this.is-a/Test',
            'slug'  => 'this-is-a-test',
            'alpha' => 'abcd',
            'num'   => '1234'
        );

        $router = new Router(array(
            'any/[test]:any' => function($test){
                return $test;
            },
            'slug/[test]:slug' => function($test){
                return $test;
            },
            'alpha/[test]:alpha' => function($test){
                return $test;
            },
            'num/[test]:num' => function($test){
                return $test;
            },
            'error' => function($url){
                return false;
            }
        ));

        $this->assertEquals($urlContent['any'], $router->launch('any/'.$urlContent['any']), 'pattern :any not working');
        $this->assertEquals($urlContent['slug'], $router->launch('slug/'.$urlContent['slug']), 'pattern :slug not working');
        $this->assertEquals($urlContent['alpha'], $router->launch('alpha/'.$urlContent['alpha']), 'pattern :alpha not working');
        $this->assertEquals($urlContent['num'], $router->launch('num/'.$urlContent['num']), 'pattern :num not working');
    }

    public function testCalledObject()
    {
        $router = new Router();
        $router->addRoutes(array(
            '/' => '\Tests\Fixtures\TestFixtures:testDefault',
            'error' => '\Tests\Fixtures\TestFixtures:testError',
            'test/[arg]:any' => '\Tests\Fixtures\TestFixtures:testWithArgument'
        ));
        $value = sha1(time());

        $this->assertEquals('default', $router->launch('/'), "Router can't call object default method");
        $this->assertEquals('error', $router->launch($value), "Router can't call object error method");
        $this->assertEquals($value, $router->launch('test/'.$value), "Router can't call object method with arguments");
    }

    public function testRouteArray()
    {
        $router = new Router();
        $value = sha1(time());
        $router->addRoutes(array(
            '/' => array(
                '\Tests\Fixtures\TestFixtures:testDefault',
                function(){
                    return true;
                }
            ),
            'test/[arg]:any' => array(
                '\Tests\Fixtures\TestFixtures:testWithArgument',
                function($arg) use ($value) {
                    return ($arg === $value);
                }
            )
        ));

        $this->assertEquals('default', $router->launch('/'), "Router can't call object default method");
        $this->assertEquals('error', $router->launch($value), "Router can't call object error method");
        $this->assertEquals($value, $router->launch('test/'.$value), "Router can't call object method with arguments");
    }
}